<?php

namespace App\Services\GoogleDrive;

use App\Models\GoogleDriveConnection;
use Google\Http\MediaFileUpload;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class GoogleDriveStorage
{
    public function __construct(private readonly GoogleDriveClient $client) {}

    public function ensureRootFolder(): string
    {
        $connection = GoogleDriveConnection::current();

        if ($connection?->root_folder_id) {
            return $connection->root_folder_id;
        }

        $folder = new DriveFile([
            'name' => config('google-drive.root_folder_name', 'OGRAFI_STORAGE'),
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        $created = $this->client->service()->files->create($folder, ['fields' => 'id,name']);
        $connection ??= new GoogleDriveConnection();
        $connection->root_folder_id = $created->id;
        $connection->save();

        return (string) $created->id;
    }

    public function createFolder(string $name, ?string $parentId = null): string
    {
        $data = ['name' => $name, 'mimeType' => 'application/vnd.google-apps.folder'];

        if ($parentId) {
            $data['parents'] = [$parentId];
        }

        $created = $this->client->service()->files->create(new DriveFile($data), ['fields' => 'id,name']);

        return (string) $created->id;
    }

    /**
     * Upload files in resumable chunks to keep PHP memory usage bounded.
     */
    public function upload(UploadedFile|string $file, string $name, ?string $folderId = null): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! $path || ! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('Yüklenecek dosya okunamadı.');
        }

        $fileSize = filesize($path);

        if ($fileSize === false || $fileSize === 0) {
            throw new RuntimeException('Boş veya boyutu okunamayan dosya yüklenemez.');
        }

        $folderId ??= $this->ensureRootFolder();
        $mimeType = $file instanceof UploadedFile
            ? ($file->getMimeType() ?: 'application/octet-stream')
            : (mime_content_type($path) ?: 'application/octet-stream');

        $client = $this->client->client();
        $service = new Drive($client);
        $metadata = new DriveFile(['name' => $name, 'parents' => [$folderId]]);
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Dosya açılırken hata oluştu.');
        }

        $client->setDefer(true);

        try {
            $request = $service->files->create($metadata);
            $upload = new MediaFileUpload(
                $client,
                $request,
                $mimeType,
                null,
                true,
                5 * 1024 * 1024,
            );
            $upload->setFileSize($fileSize);

            $result = false;

            while (! $result && ! feof($handle)) {
                $chunk = fread($handle, 5 * 1024 * 1024);

                if ($chunk === false || $chunk === '') {
                    throw new RuntimeException('Dosya okunurken hata oluştu.');
                }

                $result = $upload->nextChunk($chunk);
            }

            if (! $result) {
                throw new RuntimeException('Google Drive yüklemesi tamamlanamadı.');
            }
        } finally {
            $client->setDefer(false);
            fclose($handle);
        }

        return [
            'id' => (string) $result->id,
            'name' => (string) $result->name,
            'mime_type' => (string) $result->mimeType,
            'size' => (int) ($result->size ?? $fileSize),
            'web_view_link' => $result->webViewLink,
        ];
    }


    /**
     * Stream a Drive file to disk without loading the complete backup into memory.
     */
    public function downloadToPath(string $fileId, string $destination): void
    {
        $response = $this->client->service()->files->get($fileId, ['alt' => 'media']);
        $body = $response->getBody();
        $directory = dirname($destination);

        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('İndirme klasörü oluşturulamadı.');
        }

        $handle = fopen($destination, 'wb');

        if ($handle === false) {
            throw new RuntimeException('İndirme dosyası oluşturulamadı.');
        }

        try {
            while (! $body->eof()) {
                $chunk = $body->read(1024 * 1024);

                if ($chunk === '') {
                    continue;
                }

                if (fwrite($handle, $chunk) === false) {
                    throw new RuntimeException('İndirilen dosya diske yazılamadı.');
                }
            }
        } finally {
            fclose($handle);
        }

        @chmod($destination, 0600);
    }

    /**
     * List backup files owned by the configured application folder.
     */
    public function listFiles(string $folderId, string $namePrefix): array
    {
        $files = [];
        $pageToken = null;

        do {
            $params = [
                'q' => sprintf(
                    "'%s' in parents and trashed = false and name contains '%s'",
                    addcslashes($folderId, "\\\\'"),
                    addcslashes($namePrefix, "\\\\'")
                ),
                'pageSize' => 100,
                'orderBy' => 'createdTime desc',
                'fields' => 'nextPageToken,files(id,name,mimeType,size,createdTime)',
            ];

            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $result = $this->client->service()->files->listFiles($params);

            foreach ($result->getFiles() as $file) {
                $files[] = [
                    'id' => (string) $file->getId(),
                    'name' => (string) $file->getName(),
                    'created_time' => $file->getCreatedTime(),
                ];
            }

            $pageToken = $result->getNextPageToken();
        } while ($pageToken);

        return $files;
    }

    public function delete(string $fileId): void
    {
        $this->client->service()->files->delete($fileId);
    }

    public function metadata(string $fileId): array
    {
        $file = $this->client->service()->files->get($fileId, [
            'fields' => 'id,name,mimeType,size,webViewLink,modifiedTime',
        ]);

        return [
            'id' => (string) $file->id,
            'name' => (string) $file->name,
            'mime_type' => (string) $file->mimeType,
            'size' => (int) ($file->size ?? 0),
            'web_view_link' => $file->webViewLink,
            'modified_time' => $file->modifiedTime,
        ];
    }
}
