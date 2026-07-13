<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactSubmissionController extends Controller
{
    public function create(Request $request): View
    {
        return view('contact.create', [
            'pageTitle' => 'Bize Ulasin',
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'consent' => ['accepted'],
        ]);

        ContactSubmission::query()->create([
            'user_id' => $request->user()?->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'consent_accepted' => true,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'status' => 'new',
        ]);

        return redirect()
            ->route('contact.create')
            ->with('contact_status', 'Mesajiniz basariyla gonderildi.');
    }
}
