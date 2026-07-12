@if (!empty($cronJobLastExecution))
    <div class="my-5 text-sm font-semibold sm:text-xl">
        {{ __('Last cronjob execution') }}
        ({{ \Carbon\Carbon::parse(strtotime($cronJobLastExecution))->format('j F, Y - H:i:s') }})
    </div>
@endif
