<?php

namespace App\Services\GoogleDrive;

use Google\Http\MediaFileUpload;
use Google\Service\Drive;\nuse Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class GoogleDriveStorage
{
    public function __construct(private readonly GoogleDriveClient $client) {}

    public function ensureRootFolder(): string
    {
        $connection = \App\Models\GoogleDriveConnection::current();

        if ($connection?->root_folder_id) {
            return $connection->root_folder_id;
        }

        $folder = new DriveFile([
            'name' => config('google-drive.root_folder_name', 'OGRAFI_STORAGE'),
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        $created = $this->client->service()->files->create($folder, ['fields' => 'id,name']);
        $connection ??= new \App\Models\GoogleDriveConnection();
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
     * Uploads large files in resumable chunks instead of loading the whole file into PHP memory.
     */
    public function upload(UploadedFile|string $file, string $name, ?string $folderId = null): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! $path || ! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('Yüklenecek dosya okunamadı.');
        }

        $folderId ??= $this->ensureRootFolder();
        $mimeType = $file instanceof UploadedFile
            ? ($file->getMimeType() ?: 'application/octet-stream')
            : (mime_content_type($path) ?: 'application/octet-stream');

        $client = $this->client->client();
        $service = new Drive($client);
        $metadata = new DriveFile(['name' => $name, 'parents' => [$folderId]]);
        $client->setDefer(true);
        $handle = fopen($path, 'rb');

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
            $upload->setFileSize(filesize($path));

            $result = false;

            while (! $result && ! feof($handle)) {
                $chunk = fread($handle, 5 * 1024 * 1024);

                if ($chunk === false) {
                    throw new RuntimeException('Dosya okunurken hata oluştu.');
                }

                $result = $upload->nextChunk($chunk);
            }

            if (! $result) {
                throw new RuntimeException('Google Drive yüklemesi tamamlanamadı.');
            }
        } finally {
            $client->setDefer(false);

            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        return [
            'id' => (string) $result->id,
            'name' => (string) $result->name,
            'mime_type' => (string) $result->mimeType,
            'size' => (int) ($result->size ?? filesize($path)),
            'web_view_link' => $result->webViewLink,
        ];
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
