@props(['name' => 'info'])

<svg {{ $attributes->merge(['class' => 'icon', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'aria-hidden' => 'true']) }} stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    @switch($name)
        @case('help')
            <circle cx="12" cy="12" r="9"/>
            <circle cx="12" cy="12" r="3.2"/>
            <path d="M12 3v5.2M12 15.8V21M3 12h5.2M15.8 12H21"/>
            @break
        @case('eye')
            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/>
            <circle cx="12" cy="12" r="2.5"/>
            @break
        @case('calendar')
            <rect x="3.5" y="5" width="17" height="15" rx="2"/>
            <path d="M8 3.5V7M16 3.5V7M3.5 10h17M8 14.5l1.6 1.6L13 13"/>
            @break
        @case('shield')
            <path d="M12 3.5l7 2.5v5.2c0 4.2-2.8 7.2-7 8.8-4.2-1.6-7-4.6-7-8.8V6z"/>
            <path d="M9 12.2l2 2 4-4.2"/>
            @break
        @case('student')
            <path d="M3 9.5L12 5l9 4.5-9 4.5L3 9.5z"/>
            <path d="M7 11.5v4.2c0 .8 2.2 2.3 5 2.3s5-1.5 5-2.3v-4.2"/>
            @break
        @case('staff')
            <rect x="3.5" y="7" width="17" height="12" rx="2"/>
            <path d="M8 7V5.8A1.8 1.8 0 0 1 9.8 4h4.4A1.8 1.8 0 0 1 16 5.8V7M12 11.5v2"/>
            @break
        @case('person')
            <circle cx="12" cy="8" r="3"/>
            <path d="M5.5 19.5c1.2-3 3.4-4.5 6.5-4.5s5.3 1.5 6.5 4.5"/>
            @break
        @case('arrow')
            <path d="M5 12h12M13 7l5 5-5 5"/>
            @break
        @case('logout')
            <path d="M10 7V5.5A1.5 1.5 0 0 1 11.5 4h7A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-7A1.5 1.5 0 0 1 10 18.5V17"/>
            <path d="M4 12h10M11 8.5L14.5 12 11 15.5"/>
            @break
        @case('clipboard')
            <rect x="6" y="4.5" width="12" height="16" rx="2"/>
            <path d="M9 4.5h6v2.2H9zM9 11h6M9 15h4"/>
            @break
        @case('lock')
            <rect x="5" y="10" width="14" height="10" rx="2"/>
            <path d="M8 10V8a4 4 0 0 1 8 0v2"/>
            @break
        @case('home')
            <path d="M4 10.5L12 4l8 6.5"/>
            <path d="M7 10.2V19h10v-8.8"/>
            @break
        @case('camera')
            <path d="M4 8.5h3l1.2-2h7.6L17 8.5h3v9H4z"/>
            <circle cx="12" cy="13" r="2.4"/>
            @break
        @case('chart')
            <path d="M4 19V5M4 19h16"/>
            <path d="M8 15v-4M12 15V8M16 15v-6"/>
            @break
        @case('users')
            <circle cx="9" cy="9" r="2.4"/>
            <circle cx="16" cy="10" r="2"/>
            <path d="M4.8 18c.7-2.2 2.3-3.3 4.2-3.3s3.5 1.1 4.2 3.3M14 14.8c1.3-.3 2.5.1 3.4 1.2.6.7 1 1.4 1.2 2"/>
            @break
        @case('route')
            <circle cx="6" cy="7" r="2"/>
            <circle cx="17" cy="17" r="2"/>
            <path d="M8 8c4 0 3 8 7 8"/>
            @break
        @case('document')
            <path d="M7 3.5h7l4 4V20.5H7z"/>
            <path d="M14 3.5V8h4M9.5 12h5M9.5 16h5"/>
            @break
        @case('scale')
            <path d="M12 4v15M8 19h8M12 6l-5 6h4M12 6l5 6h-4"/>
            @break
        @default
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 11v5M12 8h.01"/>
    @endswitch
</svg>
