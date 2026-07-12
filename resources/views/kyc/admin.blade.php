@extends('kyc.layout')
@section('title', __('KYC Administration'))

@section('content')
<div class="bg-white rounded-alma shadow-sm border border-gray-200 p-6 mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('KYC Verification Management') }}</h1>
    <p class="text-gray-500 text-sm mt-1">{{ __('Review and manage user identity verification documents.') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-alma shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('Pending Reviews') }} ({{ $pendingDocuments->total() }})</h2>
            </div>

            @if($pendingDocuments->isEmpty())
                <div class="p-12 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>{{ __('No pending KYC reviews.') }}</p>
                </div>
            @else
                @foreach($pendingDocuments as $document)
                    <div class="p-6 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $document->user->getAvatar() }}" alt="" class="w-10 h-10 rounded-full border border-gray-200" onerror="this.src='https://api.dicebear.com/7.x/avataaars/svg?seed=default'">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $document->user->name ?? $document->user->username }}</p>
                                    <p class="text-xs text-gray-400">@{{ $document->user->username }} · {{ $document->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $document->statusBadgeClass() }}">
                                {{ $document->statusLabel() }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 mb-1">{{ __('Document Type') }}</p>
                                <p class="text-sm text-gray-900">{{ __(\App\Models\KycDocument::$documentTypes[$document->document_type] ?? $document->document_type) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 mb-1">{{ __('Document Number') }}</p>
                                <p class="text-sm text-gray-900">{{ $document->document_number }}</p>
                            </div>
                        </div>

                        <div class="space-y-3 mb-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 mb-1.5">{{ __('Front Side') }}</p>
                                <a href="{{ $document->getDocumentFrontUrl() }}" target="_blank" class="block">
                                    <img src="{{ $document->getDocumentFrontUrl() }}" alt="Document front" class="max-h-48 rounded-lg border border-gray-200 object-cover" loading="lazy">
                                </a>
                            </div>
                            @if($document->getDocumentBackUrl())
                                <div>
                                    <p class="text-xs font-medium text-gray-500 mb-1.5">{{ __('Back Side') }}</p>
                                    <a href="{{ $document->getDocumentBackUrl() }}" target="_blank" class="block">
                                        <img src="{{ $document->getDocumentBackUrl() }}" alt="Document back" class="max-h-48 rounded-lg border border-gray-200 object-cover" loading="lazy">
                                    </a>
                                </div>
                            @endif
                            @if($document->getSelfieUrl())
                                <div>
                                    <p class="text-xs font-medium text-gray-500 mb-1.5">{{ __('Selfie with Document') }}</p>
                                    <a href="{{ $document->getSelfieUrl() }}" target="_blank" class="block">
                                        <img src="{{ $document->getSelfieUrl() }}" alt="Selfie" class="max-h-48 rounded-lg border border-gray-200 object-cover" loading="lazy">
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="flex space-x-3">
                            <form method="POST" action="{{ route('kyc.approve', $document) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition">
                                    {{ __('Approve') }}
                                </button>
                            </form>
                            <button onclick="showRejectModal({{ $document->id }})" class="flex-1 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700 transition">
                                {{ __('Reject') }}
                            </button>

                            <div id="reject_modal_{{ $document->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                                <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Reject KYC Document') }}</h3>
                                    <form method="POST" action="{{ route('kyc.reject', $document) }}">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Reason for rejection') }}</label>
                                            <textarea name="admin_notes" rows="3" required maxlength="500"
                                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition resize-none"
                                                placeholder="{{ __('Explain why the document was rejected...') }}"></textarea>
                                        </div>
                                        <div class="flex justify-end space-x-3">
                                            <button type="button" onclick="hideRejectModal({{ $document->id }})" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition">{{ __('Cancel') }}</button>
                                            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700 transition">{{ __('Reject') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $pendingDocuments->links() }}
                </div>
            @endif
        </div>
    </div>

    <div>
        <div class="bg-white rounded-alma shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">{{ __('Recently Reviewed') }}</h2>
            </div>
            @if($recentDocuments->isEmpty())
                <div class="p-6 text-center text-gray-400 text-sm">
                    {{ __('No recent reviews.') }}
                </div>
            @else
                @foreach($recentDocuments as $document)
                    <div class="px-6 py-3 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $document->user->name ?? $document->user->username }}</p>
                                <p class="text-xs text-gray-400">{{ $document->verified_at?->diffForHumans() }}</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $document->statusBadgeClass() }}">
                                {{ $document->statusLabel() }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showRejectModal(id) {
        document.getElementById('reject_modal_' + id).classList.remove('hidden');
    }

    function hideRejectModal(id) {
        document.getElementById('reject_modal_' + id).classList.add('hidden');
    }
</script>
@endpush
