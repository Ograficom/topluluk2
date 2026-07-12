<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <x-filament::icon icon="heroicon-m-exclamation-circle"
                class="h-10 w-10" color="gray" />
            <div class="flex-1">
                <h2
                    class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    {{ __('Cron Job Not Working') }}
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('It seems Cron Job works incorrectly. Please double-check and ensure that your Cron Job is properly configured. Cron Job is required by multiple things to be run (Emails, Badges, Notifications, Sitemap, etc...)') }}
                </p>
            </div>

            <x-filament::button
                color="warning"
                href="cp/cron-job"
                icon="heroicon-m-arrow-left-on-rectangle"
                icon-alias="panels::widgets.account.logout-button"
                labeled-from="sm"
                tag="a">
                {{ __('Setup') }}
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
