<?php

namespace App\Http\Controllers;

use App\Models\KycDocument;
use App\Models\Traits\LogsActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $documents = $user->kycDocument()
            ->latest()
            ->get();

        return view('kyc.index', compact('user', 'documents'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:passport,national_id,driving_license',
            'document_number' => 'required|string|max:100',
            'document_front' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'document_back' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'selfie' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $user = Auth::user();

        $frontPath = $request->file('document_front')->store('kyc/' . $user->id, getCurrentDisk());

        $backPath = null;
        if ($request->hasFile('document_back')) {
            $backPath = $request->file('document_back')->store('kyc/' . $user->id, getCurrentDisk());
        }

        $selfiePath = null;
        if ($request->hasFile('selfie')) {
            $selfiePath = $request->file('selfie')->store('kyc/' . $user->id, getCurrentDisk());
        }

        KycDocument::create([
            'user_id' => $user->id,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'document_front_path' => $frontPath,
            'document_back_path' => $backPath,
            'selfie_path' => $selfiePath,
            'status' => 'pending',
        ]);

        $user->update(['kyc_status' => 'pending']);

        LogsActivity::logActivity('kyc_submitted', __(':user submitted KYC documents', ['user' => $user->username]));

        return redirect()->route('kyc.index')
            ->with('success', __('Your KYC documents have been submitted for review.'));
    }

    public function resubmit()
    {
        $user = Auth::user();

        if ($user->kyc_status !== 'rejected') {
            return redirect()->route('kyc.index');
        }

        $user->update(['kyc_status' => 'unverified']);

        return redirect()->route('kyc.index')
            ->with('success', __('You can now resubmit your documents.'));
    }

    public function admin()
    {
        $pendingDocuments = KycDocument::with('user')
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        $recentDocuments = KycDocument::with(['user', 'verifiedBy'])
            ->where('status', '!=', 'pending')
            ->latest()
            ->limit(10)
            ->get();

        return view('kyc.admin', compact('pendingDocuments', 'recentDocuments'));
    }

    public function approve(KycDocument $kycDocument)
    {
        $kycDocument->update([
            'status' => 'approved',
            'verified_at' => now(),
            'verified_by' => Auth::id(),
            'admin_notes' => null,
        ]);

        $kycDocument->user->update(['kyc_status' => 'verified']);

        LogsActivity::logActivity('kyc_verified', __('KYC approved for :user', ['user' => $kycDocument->user->username]));

        return redirect()->route('kyc.admin')
            ->with('success', __('KYC document approved successfully.'));
    }

    public function reject(Request $request, KycDocument $kycDocument)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $kycDocument->update([
            'status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
        ]);

        $kycDocument->user->update(['kyc_status' => 'rejected']);

        LogsActivity::logActivity('kyc_rejected', __('KYC rejected for :user - :reason', [
            'user' => $kycDocument->user->username,
            'reason' => $request->admin_notes,
        ]));

        return redirect()->route('kyc.admin')
            ->with('success', __('KYC document rejected.'));
    }
}
