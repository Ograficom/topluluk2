<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return view('pages.show', ['page' => $page]);
    }
}
