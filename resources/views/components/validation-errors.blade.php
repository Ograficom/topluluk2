@props(['class' => ''])

@if($errors->any())
    <div {{ $attributes->merge(['class' => 'repost-validation-errors ' . $class]) }} role="alert">
        <p class="font-semibold">İşlem tamamlanamadı. Lütfen aşağıdakileri kontrol et:</p>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
