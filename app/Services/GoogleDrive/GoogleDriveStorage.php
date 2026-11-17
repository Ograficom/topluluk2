<?php
namespace App\Services\GoogleDrive;
use App\Models\GoogleDriveConnection;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use RuntimeException;
class GoogleDriveStorage
{
    public function __construct(private readonly GoogleDriveClient $client) {}
    public function ensureRootFolder(): string
    {
        $connection = GoogleDriveConnection::current();
        if ($connection?->root_folder_id) return $connection->root_folder_id;
        $folder = new DriveFile(['name'=>config('google-drive.root_folder_name','OGRAFI_STORAGE'),'mimeType'=>'application/vnd.google-apps.folder']);
        $created = $this->client->service()->files->create($folder,['fields'=>'id,name']);
        $connection ??= new GoogleDriveConnection();
        $connection->root_folder_id = $created->id;
        $connection->save();
        return (string) $created->id;
    }
    public function createFolder(string $name, ?string $parentId = null): string
    {
        $data = ['name'=>$name,'mimeType'=>'application/vnd.google-apps.folder'];
        if ($parentId) $data['parents'] = [$parentId];
        $created = $this->client->service()->files->create(new DriveFile($data),['fields'=>'id,name']);
        return (string) $created->id;
    }
    public function upload(UploadedFile|string $file, string $name, ?string $folderId = null): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        if (! $path || ! is_file($path) || ! is_readable($path)) throw new RuntimeException('Yüklenecek dosya okunamadı.');
        $folderId ??= $this->ensureRootFolder();
        $stream = fopen($path,'rb');
        try {
            $created = $this->client->service()->files->create(new DriveFile(['name'=>$name,'parents'=>[$folderId]]),[
                'data'=>stream_get_contents($stream),
                'mimeType'=>$file instanceof UploadedFile ? ($file->getMimeType() ?: 'application/octet-stream') : (mime_content_type($path) ?: 'application/octet-stream'),
                'uploadType'=>'multipart','fields'=>'id,name,mimeType,size,webViewLink'
            ]);
        } finally { if (is_resource($stream)) fclose($stream); }
        return ['id'=>(string)$created->id,'name'=>(string)$created->name,'mime_type'=>(string)$created->mimeType,'size'=>(int)($created->size ?? 0),'web_view_link'=>$created->webViewLink];
    }
    public function delete(string $fileId): void { $this->client->service()->files->delete($fileId); }
    public function metadata(string $fileId): array
    {
        $f=$this->client->service()->files->get($fileId,['fields'=>'id,name,mimeType,size,webViewLink,modifiedTime']);
        return ['id'=>(string)$f->id,'name'=>(string)$f->name,'mime_type'=>(string)$f->mimeType,'size'=>(int)($f->size ?? 0),'web_view_link'=>$f->webViewLink,'modified_time'=>$f->modifiedTime];
    }
}