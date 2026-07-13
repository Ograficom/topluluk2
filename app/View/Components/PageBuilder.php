<?php

namespace App\View\Components;

use App\Models\PageBuilder as PageBuilderModel;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class PageBuilder extends Component
{
    public ?PageBuilderModel $page = null;
    public ?string $key = null;

    public function __construct(?string $key = null)
    {
        $this->key = $key ?: $this->resolveKey();

        if ($this->key) {
            $this->page = PageBuilderModel::query()
                ->where('key', $this->key)
                ->where('is_active', true)
                ->first();
        }
    }

    public function render(): \Illuminate\Contracts\View\View|\Closure|string
    {
        return view('components.page-builder');
    }

    private function resolveKey(): ?string
    {
        $route = request()->route();
        $routeName = $route?->getName();

        if ($routeName && !Str::startsWith($routeName, 'filament.')) {
            return $routeName;
        }

        $path = trim((string) request()->path(), '/');

        return $path === '' ? 'home' : $path;
    }
}
