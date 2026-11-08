<?php

namespace App\Http\Controllers;

use App\Models\RssItem;
use App\Models\RssSource;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RssReaderController extends Controller
{
    public function index(Request $request): View
    {
        $sourceId = $request->integer('source');
        $sources = RssSource::query()
            ->where('is_active', true)
            ->withCount('items')
            ->orderBy('name')
            ->get();

        $items = RssItem::query()
            ->with('source')
            ->whereHas('source', fn ($query) => $query->where('is_active', true))
            ->when($sourceId > 0, fn ($query) => $query->where('rss_source_id', $sourceId))
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(20)
            ->withQueryString();

        return view('rss.index', [
            'items' => $items,
            'sources' => $sources,
            'selectedSource' => $sourceId,
            'lastUpdatedAt' => RssSource::query()->where('is_active', true)->max('last_fetched_at'),
        ]);
    }
}
