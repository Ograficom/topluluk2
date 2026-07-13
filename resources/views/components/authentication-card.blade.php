<div class="flex min-h-screen items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">
        <div class="mb-4 flex justify-center">
            {{ $logo }}
        </div>

        <div class="overflow-hidden rounded-2xl border border-border bg-card px-6 py-6 text-card-foreground shadow-sm">
            {{ $slot }}
        </div>
    </div>
</div>

