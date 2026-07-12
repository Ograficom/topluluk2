@extends('messages.layout')
@section('title', __('New Message'))

@section('content')
<div class="mb-4">
    <a href="{{ route('messages.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('Back to Inbox') }}
    </a>
</div>

<div class="bg-white rounded-alma shadow-sm border border-gray-200 p-6">
    <h1 class="text-xl font-bold text-gray-900 mb-6">{{ __('New Message') }}</h1>

    <form method="POST" action="{{ route('messages.store') }}">
        @csrf
        <div class="space-y-5">
            <div>
                <label for="recipient_search" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Recipient') }}</label>
                <input type="text" id="recipient_search" placeholder="{{ __('Search by username...') }}"
                    value="{{ $recipient ? ($recipient->name ?? $recipient->username) : '' }}"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition">
                <input type="hidden" name="recipient_id" id="recipient_id" value="{{ $recipient->id ?? '' }}">
                <div id="search_results" class="mt-2 space-y-1"></div>
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Subject') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span></label>
                <input type="text" name="subject" id="subject" maxlength="255"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition">
            </div>

            <div>
                <label for="body" class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('Message') }}</label>
                <textarea name="body" id="body" rows="6" required maxlength="5000"
                    placeholder="{{ __('Write your message...') }}"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition resize-none"></textarea>
                <p class="text-xs text-gray-400 mt-1"><span id="char_count">0</span>/5000</p>
            </div>

            <div class="flex justify-end space-x-3 pt-2">
                <a href="{{ route('messages.index') }}" class="px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 transition">{{ __('Cancel') }}</a>
                <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition">
                    {{ __('Send Message') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const searchInput = document.getElementById('recipient_search');
    const recipientId = document.getElementById('recipient_id');
    const resultsDiv = document.getElementById('search_results');
    const charCount = document.getElementById('char_count');
    const bodyTextarea = document.getElementById('body');

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            resultsDiv.innerHTML = '';
            if (!recipientId.value) return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('messages.create') }}?search=${encodeURIComponent(query)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const userResults = doc.querySelectorAll('[data-user-result]');
                resultsDiv.innerHTML = '';
                userResults.forEach(el => {
                    const clone = el.cloneNode(true);
                    clone.addEventListener('click', function() {
                        searchInput.value = this.querySelector('[data-user-name]').textContent.trim();
                        recipientId.value = this.dataset.userId;
                        resultsDiv.innerHTML = '';
                    });
                    resultsDiv.appendChild(clone);
                });
            });
        }, 300);
    });

    bodyTextarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });

    document.addEventListener('DOMContentLoaded', function() {
        @if($recipient)
            resultsDiv.innerHTML = '';
        @endif
    });
</script>
@endpush
