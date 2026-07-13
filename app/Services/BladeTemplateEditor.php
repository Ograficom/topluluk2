<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class BladeTemplateEditor
{
    public function templates(): array
    {
        return (array) config('blade-editor.templates', []);
    }

    public function getContent(string $template): string
    {
        $path = $this->resolvePath($template);

        return File::get($path);
    }

    public function save(string $template, string $content): string
    {
        $path = $this->resolvePath($template);
        $backupPath = $this->backup($template, $path);

        File::put($path, $content);

        return $backupPath;
    }

    public function lastBackups(string $template, int $limit = 5): array
    {
        $dir = $this->backupDir($template);
        if (!Storage::disk('local')->exists($dir)) {
            return [];
        }

        $files = Storage::disk('local')->files($dir);
        rsort($files);

        return array_slice($files, 0, $limit);
    }

    private function resolvePath(string $template): string
    {
        $templates = $this->templates();
        if (!array_key_exists($template, $templates)) {
            throw new RuntimeException('Bu dosyayi duzenleme yetkisi yok.');
        }

        $base = realpath(resource_path('views'));
        $full = realpath(resource_path('views/' . $template)) ?: resource_path('views/' . $template);

        if (!$base || !Str::startsWith($full, $base)) {
            throw new RuntimeException('Gecersiz dosya yolu.');
        }

        if (!File::exists($full)) {
            throw new RuntimeException('Dosya bulunamadi.');
        }

        return $full;
    }

    private function backup(string $template, string $path): string
    {
        $disk = Storage::disk('local');
        $dir = $this->backupDir($template);
        $disk->makeDirectory($dir);

        $stamp = now()->format('Ymd_His');
        $base = pathinfo($template, PATHINFO_FILENAME);
        $ext = pathinfo($template, PATHINFO_EXTENSION);
        $fileName = $base . '_' . $stamp . '.' . $ext;
        $backupPath = $dir . '/' . $fileName;

        $disk->put($backupPath, File::get($path));
        $this->trimBackups($dir);

        return $backupPath;
    }

    private function backupDir(string $template): string
    {
        $root = (string) config('blade-editor.backup_dir', 'blade-backups');
        $slug = Str::slug(str_replace(['/', '.blade.php'], ['-', ''], $template));

        return trim($root, '/') . '/' . $slug;
    }

    private function trimBackups(string $dir): void
    {
        $max = (int) config('blade-editor.max_backups', 0);
        if ($max <= 0) {
            return;
        }

        $disk = Storage::disk('local');
        $files = $disk->files($dir);
        rsort($files);

        $extra = array_slice($files, $max);
        foreach ($extra as $file) {
            $disk->delete($file);
        }
    }
}
