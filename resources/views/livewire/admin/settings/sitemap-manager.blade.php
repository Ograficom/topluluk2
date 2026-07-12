<div class="mx-auto flex w-full flex-col gap-y-8">
    <div class="rounded-xl bg-white p-4 shadow dark:bg-gray-900 sm:overflow-hidden sm:p-6">
        <div class="flex flex-col gap-4">
            <div class="mb-4 flex gap-2 rounded-lg bg-gray-100 p-4 text-sm dark:bg-gray-700"
                role="alert">
                <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <div>
                    <span
                        class="font-medium">
                        {{ __('Please note that the process of creating a site map can take several minutes or even more, depending on the amount of content on your site, so after clicking the button update the site map, wait until the process is complete without leaving the page, otherwise, the process will fail!') }}
                    </span>
                </div>
            </div>
            @if (file_exists(public_path('sitemaps.xml')))
                <h2 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ __('Sitemaps:') }}</h2>
                <ul class="max-w-md list-inside space-y-4 text-gray-500 dark:text-gray-400">
                    <li class="flex flex-col items-start gap-x-2 sm:flex-row sm:items-center">
                        <span class="mr-2">{{ __('Index:') }}</span>
                        <a
                            target="_blank"
                            class="text-primary-400 hover:text-primary-600 dark:hover:text-primary-300 flex items-center"
                            href="{{ env('APP_URL') . '/sitemaps.xml' }}">
                            <span>{{ env('APP_URL') . '/sitemaps.xml' }}</span>
                        </a>
                    </li>
                    <li class="flex flex-col items-start gap-x-2 sm:flex-row sm:items-center">
                        <span class="mr-2">{{ __('Base:') }}</span>
                        <a
                            target="_blank"
                            class="text-primary-400 hover:text-primary-600 dark:hover:text-primary-300 flex items-center"
                            href="{{ env('APP_URL') . '/base_sitemap.xml' }}">
                            <span>{{ env('APP_URL') . '/base_sitemap.xml' }}</span>
                        </a>
                    </li>
                    <li class="flex flex-col items-start gap-x-2 sm:flex-row sm:items-center">
                        <span class="mr-2">{{ __('Stories:') }}</span>
                        <a
                            target="_blank"
                            class="text-primary-400 hover:text-primary-600 dark:hover:text-primary-300 flex items-center"
                            href="{{ env('APP_URL') . '/stories_sitemap.xml' }}">
                            <span>{{ env('APP_URL') . '/stories_sitemap.xml' }}</span>
                        </a>
                    </li>
                    <li class="flex flex-col items-start gap-x-2 sm:flex-row sm:items-center">
                        <span class="mr-2">{{ __('Communities:') }}</span>
                        <a
                            target="_blank"
                            class="text-primary-400 hover:text-primary-600 dark:hover:text-primary-300 flex items-center"
                            href="{{ env('APP_URL') . '/communities_sitemap.xml' }}">
                            <span>{{ env('APP_URL') . '/communities_sitemap.xml' }}</span>
                        </a>
                    </li>
                    <li class="flex flex-col items-start gap-x-2 sm:flex-row sm:items-center">
                        <span class="mr-2">{{ __('Tags:') }}</span>
                        <a
                            target="_blank"
                            class="text-primary-400 hover:text-primary-600 dark:hover:text-primary-300 flex items-center"
                            href="{{ env('APP_URL') . '/tags_sitemap.xml' }}">
                            <span>{{ env('APP_URL') . '/tags_sitemap.xml' }}</span>
                        </a>
                    </li>
                    <li class="flex flex-col items-start gap-x-2 sm:flex-row sm:items-center">
                        <span class="mr-2">{{ __('Users:') }}</span>
                        <a
                            target="_blank"
                            class="text-primary-400 hover:text-primary-600 dark:hover:text-primary-300 flex items-center"
                            href="{{ env('APP_URL') . '/users_sitemap.xml' }}">
                            <span>{{ env('APP_URL') . '/users_sitemap.xml' }}</span>
                        </a>
                    </li>
                    <li class="flex flex-col items-start gap-x-2 sm:flex-row sm:items-center">
                        <span class="mr-2">{{ __('Pages:') }}</span>
                        <a
                            target="_blank"
                            class="text-primary-400 hover:text-primary-600 dark:hover:text-primary-300 flex items-center"
                            href="{{ env('APP_URL') . '/pages_sitemap.xml' }}">
                            <span>{{ env('APP_URL') . '/pages_sitemap.xml' }}</span>
                        </a>
                    </li>
                </ul>
                <div class="my-5 text-sm font-semibold sm:text-xl">{{ __('Last sitemap update') }}
                    ({{ \Carbon\Carbon::parse(strtotime($sitemapUpdatedDate))->format('j F, Y - H:i') }})
                </div>
            @else
                <h2 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white sm:text-lg">{{ __('Sitemaps:') }}
                </h2>
                <div>
                    {{ __('There is no sitemap yet, you can generate a new sitemap, click on the update sitemap button and wait until it is generated.') }}
                </div>
            @endif
        </div>
    </div>
    <div class="flex w-full justify-end sm:w-auto">
        <x-filament::button wire:click="update" spinner="update"
            loading-delay="short">
            {{ __('Update sitemap') }}
        </x-filament::button>
    </div>
</div>
