@php
    $defaultClasses = 'w-12 h-12 max-w-full object-contain';
@endphp

<img
    src="{{ asset('umb.png') }}"
    alt="{{ config('app.name') }} logo"
    {{ $attributes->merge(['class' => trim($attributes->get('class').' '.$defaultClasses)]) }}
/>
