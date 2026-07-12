@extends('kyc.layout')
@section('title', __('KYC Verification'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-alma shadow-sm border border-gray-200 p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('KYC Verification') }}</h1>
        <p class="text-gray-500 text-sm">{{ __('Verify your identity to unlock all platform features.') }}</p>
    </div>

    @php
        $kycStatus = $user->kyc_status ?? 'unverified';
    @endphp

    @if($kycStatus === 'unverified')
        <div class="bg-white rounded-alma shadow-sm border border-gray-200 p-6">
            <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-gray-100">
                <div class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Submit Your Documents') }}</h2>
                    <p class="text-sm text-gray-500">{{ __('Please provide the required documents for identity verification.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('kyc.submit') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <label for="document_type" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Document Type') }}</label>
                    <select name="document_type" id="document_type" required
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition bg-white">
                        <option value="">{{ __('Select document type...') }}</option>
                        @foreach(\App\Models\KycDocument::$documentTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('document_type') == $value ? 'selected' : '' }}>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="document_number" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Document Number') }}</label>
                    <input type="text" name="document_number" id="document_number" required maxlength="100" value="{{ old('document_number') }}"
                        placeholder="e.g. AB1234567"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition">
                </div>

                <div>
                    <label for="document_front" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Document Front Side') }}</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-primary-400 transition cursor-pointer" onclick="document.getElementById('document_front').click()">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div class="text-sm text-gray-500">
                                <span class="text-primary-600 font-medium">{{ __('Upload a file') }}</span>
                                {{ __('or drag and drop') }}
                            </div>
                            <p class="text-xs text-gray-400">{{ __('JPG, JPEG, PNG up to 5MB') }}</p>
                            <p id="front_file_name" class="text-xs text-primary-600 font-medium hidden"></p>
                        </div>
                    </div>
                    <input type="file" name="document_front" id="document_front" accept="image/*" required class="hidden" onchange="updateFileName(this, 'front_file_name')">
                </div>

                <div>
                    <label for="document_back" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Document Back Side') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span></label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-primary-400 transition cursor-pointer" onclick="document.getElementById('document_back').click()">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div class="text-sm text-gray-500">
                                <span class="text-primary-600 font-medium">{{ __('Upload a file') }}</span>
                                {{ __('or drag and drop') }}
                            </div>
                            <p class="text-xs text-gray-400">{{ __('JPG, JPEG, PNG up to 5MB') }}</p>
                            <p id="back_file_name" class="text-xs text-primary-600 font-medium hidden"></p>
                        </div>
                    </div>
                    <input type="file" name="document_back" id="document_back" accept="image/*" class="hidden" onchange="updateFileName(this, 'back_file_name')">
                </div>

                <div>
                    <label for="selfie" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Selfie with Document') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span></label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-primary-400 transition cursor-pointer" onclick="document.getElementById('selfie').click()">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <div class="text-sm text-gray-500">
                                <span class="text-primary-600 font-medium">{{ __('Upload a file') }}</span>
                                {{ __('or drag and drop') }}
                            </div>
                            <p class="text-xs text-gray-400">{{ __('JPG, JPEG, PNG up to 5MB') }}</p>
                            <p id="selfie_file_name" class="text-xs text-primary-600 font-medium hidden"></p>
                        </div>
                    </div>
                    <input type="file" name="selfie" id="selfie" accept="image/*" class="hidden" onchange="updateFileName(this, 'selfie_file_name')">
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition">
                        {{ __('Submit for Verification') }}
                    </button>
                </div>
            </form>
        </div>
    @elseif($kycStatus === 'pending')
        <div class="bg-yellow-50 border border-yellow-200 rounded-alma p-6 text-center">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-yellow-800 mb-2">{{ __('Verification In Progress') }}</h2>
            <p class="text-sm text-yellow-600">{{ __('Your documents are being reviewed. This usually takes 1-3 business days.') }}</p>
        </div>

        @if($documents->isNotEmpty())
            <div class="bg-white rounded-alma shadow-sm border border-gray-200 p-6 mt-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('Submitted Documents') }}</h3>
                @foreach($documents as $doc)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ __(\App\Models\KycDocument::$documentTypes[$doc->document_type] ?? $doc->document_type) }}</p>
                            <p class="text-xs text-gray-400">{{ $doc->document_number }} · {{ $doc->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $doc->statusBadgeClass() }}">
                            {{ $doc->statusLabel() }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    @elseif($kycStatus === 'verified')
        <div class="bg-green-50 border border-green-200 rounded-alma p-6 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-green-800 mb-2">{{ __('Identity Verified') }}</h2>
            <p class="text-sm text-green-600">{{ __('Your identity has been successfully verified. You have full access to all platform features.') }}</p>
        </div>
    @elseif($kycStatus === 'rejected')
        <div class="bg-red-50 border border-red-200 rounded-alma p-6">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-red-800 mb-2">{{ __('Verification Rejected') }}</h2>
                <p class="text-sm text-red-600 mb-4">{{ __('Your verification was not approved. Please review the reason below and resubmit.') }}</p>
            </div>

            @php $lastRejected = $documents->where('status', 'rejected')->first(); @endphp
            @if($lastRejected && $lastRejected->admin_notes)
                <div class="bg-white rounded-xl border border-red-200 p-4 mb-4">
                    <p class="text-sm font-medium text-red-800 mb-1">{{ __('Reason:') }}</p>
                    <p class="text-sm text-red-600">{{ $lastRejected->admin_notes }}</p>
                </div>
            @endif

            <div class="text-center">
                <a href="{{ route('kyc.resubmit') }}" class="inline-flex items-center px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition">
                    {{ __('Resubmit Documents') }}
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function updateFileName(input, displayId) {
        const display = document.getElementById(displayId);
        if (input.files && input.files[0]) {
            display.textContent = input.files[0].name;
            display.classList.remove('hidden');
        } else {
            display.textContent = '';
            display.classList.add('hidden');
        }
    }
</script>
@endpush
