<div class="flex flex-col gap-2 text-sm">
    <div class="flex items-center gap-2">
        <span>{{ __('Secret URL:') }}</span>
        <a class="hover:underline" href="{{ env('APP_URL') . '/' . config('alma.maintenance.secret') }}">
            {{ env('APP_URL') . '/' . config('alma.maintenance.secret') }}
        </a>
    </div>

    <span class="text-xs text-gray-500 dark:text-gray-400">
        {{ __('Copy the link and click save. Follow the link in your browser and you will be able to view the application normally as if it was not in maintenance mode.') }}
    </span>
</div>
