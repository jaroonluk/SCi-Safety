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
        @case('phone')
            <path d="M8 3.8h3.2l1 3.2-1.8 1.1a11.2 11.2 0 0 0 5.5 5.5l1.1-1.8 3.2 1V16a2.2 2.2 0 0 1-2.2 2.2A14.2 14.2 0 0 1 3.8 6.2 2.2 2.2 0 0 1 6 4z"/>
            @break
        @case('building')
            <path d="M5 20V6.5l7-3 7 3V20"/>
            <path d="M9 20v-5h6v5M9 9.5h.01M12 9.5h.01M15 9.5h.01M9 13h.01M12 13h.01M15 13h.01"/>
            @break
        @case('pin')
            <path d="M12 21s6-5.1 6-10a6 6 0 1 0-12 0c0 4.9 6 10 6 10z"/>
            <circle cx="12" cy="11" r="2.1"/>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M12 7.5V12l3 2"/>
            @break
        @case('flag')
            <path d="M6 4v16M6 5h10l-2.2 3.5L16 12H6"/>
            @break
        @case('check')
            <circle cx="12" cy="12" r="9"/>
            <path d="M8 12.2l2.4 2.4L16.2 9"/>
            @break
        @case('bag')
            <path d="M7 8.5h10l.8 11H6.2L7 8.5z"/>
            <path d="M9.2 8.5V7a2.8 2.8 0 0 1 5.6 0v1.5"/>
            @break
        @case('alert')
            <path d="M12 4.2 20.2 19H3.8L12 4.2z"/>
            <path d="M12 10v4M12 16.5h.01"/>
            @break
        @case('layers')
            <path d="M12 4.5 3.5 9 12 13.5 20.5 9 12 4.5z"/>
            <path d="M3.5 13.2 12 17.7l8.5-4.5M3.5 16.8 12 21.3l8.5-4.5"/>
            @break
        @case('message')
            <path d="M5 5.5h14a1.5 1.5 0 0 1 1.5 1.5v8A1.5 1.5 0 0 1 19 16.5H10l-4 3v-3H5A1.5 1.5 0 0 1 3.5 15V7A1.5 1.5 0 0 1 5 5.5z"/>
            @break
        @case('id')
            <rect x="3.5" y="6" width="17" height="12" rx="2"/>
            <circle cx="9" cy="12" r="2"/>
            <path d="M13.5 10.5h4M13.5 13.5h3"/>
            @break
        @case('stairs')
            <path d="M4 19h4v-3.5h4V12h4V8.5h4V5"/>
            @break
        @case('door')
            <path d="M6 20V5.5A1.5 1.5 0 0 1 7.5 4H16a1.5 1.5 0 0 1 1.5 1.5V20"/>
            <path d="M10.5 12.2h.01"/>
            @break
        @case('spark')
            <path d="M12 3.5v3.2M12 17.3v3.2M3.5 12h3.2M17.3 12h3.2"/>
            <path d="M6.8 6.8l2.2 2.2M15 15l2.2 2.2M17.2 6.8 15 9M9 15l-2.2 2.2"/>
            <circle cx="12" cy="12" r="2.4"/>
            @break
        @default
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 11v5M12 8h.01"/>
    @endswitch
</svg>
