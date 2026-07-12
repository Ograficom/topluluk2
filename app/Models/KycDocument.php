<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class KycDocument extends Model
{
    protected $table = 'kyc_documents';

    protected $guarded = [];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public static array $documentTypes = [
        'passport' => 'Passport',
        'national_id' => 'National ID Card',
        'driving_license' => 'Driving License',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getDocumentFrontUrl(): string
    {
        return Storage::disk(getCurrentDisk())->url($this->document_front_path);
    }

    public function getDocumentBackUrl(): ?string
    {
        return $this->document_back_path
            ? Storage::disk(getCurrentDisk())->url($this->document_back_path)
            : null;
    }

    public function getSelfieUrl(): ?string
    {
        return $this->selfie_path
            ? Storage::disk(getCurrentDisk())->url($this->selfie_path)
            : null;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-yellow-100 text-yellow-800',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'approved' => __('Approved'),
            'rejected' => __('Rejected'),
            default => __('Pending Review'),
        };
    }
}
