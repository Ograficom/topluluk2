<x-guest-layout>
    <div class="pt-4 bg-gray-100">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <div>
                <x-authentication-card-logo />
            </div>

            <div class="w-full sm:max-w-2xl mt-6 p-6 shadow-md overflow-hidden sm:rounded-lg prose">
                @isset($title)
                    <h1 class="mb-4 text-2xl font-semibold text-slate-900">{{ $title }}</h1>
                @endisset
                {!! $policy !!}
            </div>
        </div>
    </div>
</x-guest-layout>

