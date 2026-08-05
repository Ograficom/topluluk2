<div>
    @once
        @push('head')
            @filamentStyles
        @endpush
    @endonce

    <form wire:submit="apply" class="alma-archive-picker">
        {{ $this->form }}

        <button type="submit" class="site-primary-btn inline-flex items-center justify-center gap-2 text-sm" style="height: 42px; padding: 0 20px; border-radius: 9999px; background: #2563eb; color: #ffffff; font-weight: 500; border: 1px solid #2563eb; margin-top: 12px;">
            <iconify-icon icon="lucide:search" style="font-size: 16px;"></iconify-icon>
            <span>Bu Aralikta Ara</span>
        </button>
    </form>

    @once
        @push('scripts')
            @filamentScripts
        @endpush
    @endonce
</div>
