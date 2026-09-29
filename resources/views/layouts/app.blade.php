<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SCi-Safety')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1c1917;
            --muted: #78716c;
            --paper: #fdf8f6;
            --card: #ffffff;
            --line: #f3e4e1;
            --help: #9f1239;
            --help-deep: #881337;
            --accent: #be123c;
            --gold: #be123c;
            --gold-deep: #9f1239;
            --sand: #fff1f2;
            --rose: #ffe4e6;
            --success: #047857;
            --success-bg: #ecfdf5;
            --warn: #b45309;
            --warn-bg: #fffbeb;
            --danger: #be123c;
            --danger-bg: #fff1f2;
            --info-bg: #fff1f2;
            --space: 8px;
            --radius: 12px;
            --shadow: 0 8px 24px rgba(159, 18, 57, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "IBM Plex Sans Thai", sans-serif;
            font-size: 1.05rem;
            line-height: 1.65;
            color: var(--ink);
            background: var(--paper);
        }
        a { color: inherit; }
        .shell { width: min(1120px, calc(100% - 2rem)); margin: 0 auto; }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.1rem 0 1.3rem;
        }
        .brand { display: flex; gap: 0.85rem; align-items: center; text-decoration: none; min-width: 0; }
        .logo {
            width: 4.1rem;
            height: 4.1rem;
            border-radius: 50%;
            object-fit: cover;
            flex: none;
            box-shadow: 0 0 0 3px #fff, 0 0 0 5px var(--accent);
            background: #111;
        }
        .brand strong { display: block; font-size: 1.15rem; letter-spacing: 0.01em; }
        .brand span { display: block; color: var(--accent); font-size: 0.92rem; font-weight: 600; }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .icon { width: 1.25rem; height: 1.25rem; flex: none; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            border: 0;
            border-radius: 10px;
            padding: 0.8rem 1.15rem;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:focus-visible, a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 3px solid #fda4af;
            outline-offset: 2px;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .btn:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }
        .btn-solid, .btn-google { background: var(--help); color: white; }
        .btn-google { width: 100%; }
        .btn-solid:hover, .btn-google:hover { background: var(--help-deep); }
        .btn-accent { background: var(--accent); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-warn { background: var(--warn); color: #1c1917; }
        .btn-quiet {
            background: white;
            color: var(--help);
            border: 1px solid var(--line);
        }
        .btn-quiet:hover { border-color: var(--accent); color: var(--accent); }
        .alert {
            display: flex;
            gap: 0.55rem;
            align-items: flex-start;
            background: var(--danger-bg);
            color: #9f1239;
            border-radius: 0.85rem;
            padding: 0.8rem 0.95rem;
            margin-bottom: 1rem;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--info-bg);
            color: var(--help);
            border-radius: 999px;
            padding: 0.28rem 0.7rem;
            font-weight: 700;
            font-size: 0.92rem;
        }
        .site-header {
            position: sticky;
            top: 0;
            z-index: 30;
            background: #9f1239;
            color: white;
            box-shadow: 0 8px 20px rgba(136, 19, 55, 0.22);
        }
        .site-header .brand strong,
        .site-header .brand span { color: white; }
        .site-header .brand span { max-width: 26rem; font-size: 0.78rem; line-height: 1.35; font-weight: 500; opacity: 0.92; }
        .site-header .logo { box-shadow: 0 0 0 2px white; }
        .header-inner {
            display: flex;
            align-items: center;
            gap: 0.75rem 1rem;
            min-height: 4.6rem;
            padding: 0.45rem 0;
        }
        .logo { width: 3rem; height: 3rem; }
        .primary-nav { display: flex; align-items: center; min-width: 0; }
        .menu {
            display: flex;
            align-items: center;
            gap: 0.1rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .menu a, .more summary {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            min-height: 44px;
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            cursor: pointer;
        }
        .menu a .icon, .more summary .icon { color: white; width: 1.1rem; height: 1.1rem; }
        .menu a:hover, .more summary:hover, .more[open] summary { background: rgba(255, 255, 255, 0.16); }
        .menu a[aria-current="page"], .more summary[aria-current="page"] {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: inset 0 -2px 0 white;
        }
        .header-tools { display: flex; align-items: center; gap: 0.35rem; margin-left: auto; }
        .header-tools .btn { min-height: 44px; }
        .site-header .btn-solid { background: white; color: var(--help); }
        .site-header .btn-solid:hover { background: #ffe4e6; color: var(--help-deep); }
        .site-header .btn-quiet { background: transparent; color: white; border-color: rgba(255, 255, 255, 0.55); }
        .site-header .btn-quiet:hover { background: rgba(255, 255, 255, 0.14); color: white; border-color: white; }
        .more { position: relative; }
        .more summary { list-style: none; }
        .more summary::-webkit-details-marker { display: none; }
        .more summary::after {
            content: "";
            width: 0.4rem;
            height: 0.4rem;
            margin-top: -0.2rem;
            border-right: 2px solid white;
            border-bottom: 2px solid white;
            transform: rotate(45deg);
        }
        .more-panel {
            position: absolute;
            top: calc(100% + 0.55rem);
            right: 0;
            z-index: 40;
            width: 22rem;
            margin: 0;
            padding: 0.45rem;
            background: white;
            color: var(--ink);
            border: 1px solid #f3e4e1;
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(28, 25, 23, 0.16);
        }
        .more-panel::before {
            content: "";
            position: absolute;
            top: -6px;
            right: 1.3rem;
            width: 12px;
            height: 12px;
            background: white;
            border-left: 1px solid #f3e4e1;
            border-top: 1px solid #f3e4e1;
            transform: rotate(45deg);
        }
        .more-kicker {
            margin: 0.4rem 0.7rem 0.15rem;
            color: var(--muted);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        .more-group {
            margin: 0.55rem 0.7rem 0.15rem;
            color: var(--help);
            font-size: 0.75rem;
            font-weight: 700;
        }
        .more-item {
            display: flex;
            align-items: flex-start;
            gap: 0.7rem;
            width: 100%;
            margin-top: 0.15rem;
            padding: 0.55rem 0.6rem;
            border-radius: 10px;
            color: var(--ink);
            text-decoration: none;
            box-shadow: none;
        }
        .more-ico {
            width: 2.05rem;
            height: 2.05rem;
            display: grid;
            place-items: center;
            flex: none;
            border-radius: 8px;
            background: #fff1f2;
            color: var(--help);
        }
        .more-ico .icon { width: 1.05rem; height: 1.05rem; color: var(--help); }
        .more-item strong { display: block; font-size: 0.95rem; line-height: 1.3; }
        .more-item small { display: block; margin-top: 0.05rem; color: var(--muted); font-size: 0.78rem; font-weight: 500; line-height: 1.35; }
        .more-item:hover { background: #fff7f7; }
        .more-item:hover .more-ico { background: #ffe4e6; }
        .more-item[aria-current="page"] { background: #fff1f2; }
        .more-item[aria-current="page"] .more-ico { background: white; }
        @media (max-width: 900px) {
            .header-inner { flex-wrap: wrap; }
            .primary-nav { order: 3; width: 100%; overflow-x: auto; }
            .menu { flex-wrap: nowrap; }
            .brand span { display: none; }
        }
        .page { padding: 1.35rem 1.45rem; margin-bottom: 1rem; }
        .field { display: grid; gap: 0.3rem; font-weight: 600; margin-bottom: 0.75rem; }
        input, select, textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 0.8rem;
            padding: 0.65rem 0.75rem;
            font: inherit;
            background: white;
            color: var(--ink);
        }
        textarea { min-height: 6rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        .check { display: flex; gap: 0.6rem; align-items: flex-start; font-weight: 500; }
        .check input { width: auto; margin-top: 0.25rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.45rem 0.3rem; border-bottom: 1px solid var(--line); vertical-align: top; }
        .pill-success { background: var(--success-bg); color: var(--success); }
        .pill-warning { background: var(--warn-bg); color: #92400e; }
        .pill-danger { background: var(--danger-bg); color: var(--danger); }
        .pill-info { background: var(--info-bg); color: var(--help); }
        .reassure, .trust-line {
            display: flex;
            gap: 0.7rem;
            align-items: flex-start;
            background: var(--info-bg);
            color: var(--help);
            border: 1px solid #fecdd3;
            border-radius: var(--radius);
            padding: 0.9rem 1rem;
            margin: 0 0 1rem;
        }
        .stepper { display: flex; gap: 0.35rem; margin: 1rem 0; overflow-x: auto; padding-bottom: 0.2rem; }
        .stepper span {
            flex: 1;
            min-width: 6.2rem;
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--muted);
            border-top: 3px solid var(--line);
            padding-top: 0.45rem;
        }
        .stepper .done { color: var(--success); border-color: var(--success); }
        .stepper .current { color: var(--help); border-color: var(--accent); }
        .wizard-dots { display: flex; gap: 0.5rem; margin: 0.8rem 0 1rem; }
        .wizard-dots span { width: 2rem; height: 0.35rem; border-radius: 99px; background: var(--line); }
        .wizard-dots .is-done, .wizard-dots .is-current { background: var(--accent); }
        .choice-grid { display: flex; flex-wrap: wrap; gap: 0.45rem; }
        .choice {
            border: 1px solid var(--line);
            background: white;
            color: var(--ink);
            border-radius: 10px;
            padding: 0.55rem 0.75rem;
            font: inherit;
            cursor: pointer;
        }
        .choice.is-on { border-color: var(--accent); background: var(--info-bg); color: var(--help); font-weight: 600; }
        .kanban { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; align-items: start; }
        .lane { background: #f8efec; border-radius: var(--radius); padding: 0.75rem; }
        .lane h2 { margin: 0 0 0.7rem; font-size: 1rem; }
        .mini { display: block; padding: 0.85rem; margin-bottom: 0.55rem; text-decoration: none; color: inherit; }
        .decision-bar { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .sheet { margin-top: 0.8rem; }
        @media (max-width: 900px) {
            .kanban { grid-template-columns: 1fr; }
            .decision-dock {
                position: sticky;
                bottom: 0;
                background: white;
                border-top: 1px solid var(--line);
                padding: 0.75rem 0 0.2rem;
            }
        }
        @media (max-width: 720px) { .grid-2 { grid-template-columns: 1fr; } }
    </style>
    @stack('styles')
</head>
<body>
    <header class="site-header">
        <div class="shell header-inner">
            <a class="brand" href="{{ route('home') }}">
                <img class="logo" src="{{ asset('Science_KKU.svg.webp') }}" alt="ตราสัญลักษณ์คณะวิทยาศาสตร์ มหาวิทยาลัยขอนแก่น">
                <div>
                    <strong>SCi-Safety</strong>
                    <span>ระบบบริหารจัดการคำขอตรวจสอบภาพจากกล้องวงจรปิด คณะวิทยาศาสตร์ มหาวิทยาลัยขอนแก่น</span>
                </div>
            </a>
            @auth
                @include('partials.nav')
            @endauth
        </div>
    </header>
    <div class="shell" style="padding-top:1rem">
        @if (session('status'))
            <p class="alert" style="background:var(--success-bg);color:#065f46">{{ session('status') }}</p>
        @endif
        <p class="trust-line"><x-icon name="lock" /> <span>ระบบนี้เปิดให้เข้าตรวจสอบภาพภายใต้การควบคุมของเจ้าหน้าที่เท่านั้น โดยไม่มีการส่งมอบไฟล์หรือบันทึกภาพหน้าจอ บันทึกการใช้งานอ่านย้อนหลังได้อย่างเดียว</span></p>
        @yield('content')
    </div>
</body>
</html>
