<?php

namespace App\Http\Controllers;

use App\Services\BladeTemplateEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class BladePreviewController extends Controller
{
    public function show(Request $request, BladeTemplateEditor $editor)
    {
        $template = (string) $request->query('template', '');
        $template = str_replace('..', '', $template);

        try {
            $path = $this->resolvePath($editor, $template);
            $viewName = $this->toViewName($template);

            if (view()->exists($viewName)) {
                $content = view($viewName)->render();
            } else {
                $content = view()->file($path)->render();
            }

            $content = $this->extractBody($content);

            return response()->view('filament.pages.blade-template-preview', [
                'content' => $content,
                'template' => $template,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            return response()->view('filament.pages.blade-template-preview', [
                'content' => '',
                'template' => $template,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolvePath(BladeTemplateEditor $editor, string $template): string
    {
        $templates = $editor->templates();
        if (!array_key_exists($template, $templates)) {
            abort(404);
        }

        return resource_path('views/' . $template);
    }

    private function toViewName(string $template): string
    {
        $name = Str::replaceLast('.blade.php', '', $template);

        return str_replace('/', '.', $name);
    }

    private function extractBody(string $html): string
    {
        if (!str_contains($html, '<body')) {
            return $html;
        }

        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $matches)) {
            return $matches[1] ?? $html;
        }

        return $html;
    }
}
