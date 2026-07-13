@props([
    'left' => null,
    'right' => null,
])

<div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
    <aside class="lg:col-span-3 space-y-6">
        {{ $left }}
    </aside>

    <main class="lg:col-span-6 space-y-6">
        {{ $slot }}
    </main>

    <aside class="lg:col-span-3 space-y-6">
        {{ $right }}
    </aside>
</div>
