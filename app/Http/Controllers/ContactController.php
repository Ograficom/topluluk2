<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Rules\Recaptcha;
use App\Services\SeoGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function show()
    {
        $seo = new SeoGeneratorService();

        return Inertia::render('Contact')
            ->title(__('Contact us'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('Contact us'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:5|max:150',
            'email' => 'required|email|max:150',
            'subject' => 'required|max:150',
            'message' => 'required|min:5|max:500',
            'recaptcha' => $request->recaptcha == '' ? ['required', new Recaptcha($request->recaptcha)] : ['sometimes'],
        ]);

        Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        toast_success(__('Your message has been sent'));

        return back();
    }
}
