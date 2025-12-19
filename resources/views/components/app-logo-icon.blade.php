<img
    src="{{ asset('umb.png') }}"
    alt="{{ config('app.name') }} logo"
    {{ $attributes->merge(['class' => trim(($attributes->get('class') ?? '').' w-full h-full object-contain')]) }}
/>
