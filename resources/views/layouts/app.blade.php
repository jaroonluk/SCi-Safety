<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SCi-Safety')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #2c1816;
            --muted: #7a615c;
            --paper: #fbf4ee;
            --card: #fffdfb;
            --line: #f0ddd4;
            --help: #9b2c2c;
            --help-deep: #6e1d24;
            --gold: #e3b23c;
            --gold-deep: #8d6410;
            --sand: #fff6ec;
            --rose: #fdece8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Sarabun", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(880px 420px at -8% -12%, rgba(227, 178, 60, 0.34), transparent 62%),
                radial-gradient(720px 460px at 110% -8%, rgba(155, 44, 44, 0.14), transparent 58%),
                linear-gradient(180deg, #fff8f3 0%, var(--paper) 100%);
        }
        a { color: inherit; }
        .shell { width: min(1080px, calc(100% - 2rem)); margin: 0 auto; }
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
            box-shadow: 0 0 0 3px #fff, 0 0 0 5px var(--gold);
            background: #111;
        }
        .brand strong { display: block; font-size: 1.15rem; letter-spacing: 0.01em; }
        .brand span { display: block; color: var(--help); font-size: 0.92rem; font-weight: 600; }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 1.3rem;
            box-shadow: 0 16px 40px rgba(110, 29, 36, 0.06);
        }
        .icon { width: 1.25rem; height: 1.25rem; flex: none; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            border: 0;
            border-radius: 999px;
            padding: 0.85rem 1.25rem;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-google {
            width: 100%;
            background: var(--help);
            color: white;
        }
        .btn-google:hover { background: var(--help-deep); }
        .btn-quiet {
            background: white;
            color: var(--help-deep);
            border: 1px solid var(--line);
        }
        .btn-quiet:hover { border-color: var(--help); }
        .alert {
            display: flex;
            gap: 0.55rem;
            align-items: flex-start;
            background: var(--rose);
            color: var(--help-deep);
            border-radius: 0.85rem;
            padding: 0.8rem 0.95rem;
            margin-bottom: 1rem;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--rose);
            color: var(--help);
            border-radius: 999px;
            padding: 0.28rem 0.7rem;
            font-weight: 700;
            font-size: 0.92rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <a class="brand" href="{{ route('home') }}">
                <img class="logo" src="{{ asset('Science_KKU.svg.webp') }}" alt="ตราสัญลักษณ์คณะวิทยาศาสตร์ มหาวิทยาลัยขอนแก่น">
                <div>
                    <strong>SCi-Safety</strong>
                    <span>ขอความช่วยเหลือเมื่อเกิดเหตุเดือดร้อน</span>
                </div>
            </a>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-quiet" type="submit"><x-icon name="logout" /> ออกจากระบบ</button>
                </form>
            @endauth
        </header>
        @yield('content')
    </div>
</body>
</html>
