<?php

namespace App\Http\Controllers;

use App\Models\CookiePolicy;
use Illuminate\Contracts\View\View;

class CookiePolicyController extends Controller
{
    public function show(): View
    {
        $policy = CookiePolicy::enabled()->latest('updated_at')->firstOrFail();

        return view('policy', [
            'policy' => $policy->content,
            'title' => $policy->title,
        ]);
    }
}
