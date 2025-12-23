<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="{{ asset('umb.png') }}" type="image/png">
<link rel="apple-touch-icon" href="{{ asset('umb.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<script>
    (() => {
        const storageKey = 'e-legalisir-theme';
        const root = document.documentElement;

        try {
            const storedTheme = window.localStorage.getItem(storageKey);
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
        } catch (error) {
            console.error('Theme init error', error);
        }
    })();
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
