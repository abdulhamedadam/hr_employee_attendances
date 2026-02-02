<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام الحضور الذكي')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f0f2f5;
            padding-top: 76px;
            font-family: 'Cairo', sans-serif;
        }

        .navbar {
            background: #ffffff;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            padding: 0.8rem 0;
        }

        .nav-link {
            font-weight: 600;
            color: #4a5568 !important;
            padding: 0.5rem 1.2rem !important;
            border-radius: 8px;
            transition: all 0.3s;
            margin: 0 2px;
        }

        .nav-link:hover {
            color: #3182ce !important;
            background: #ebf8ff;
        }

        .nav-link.active {
            color: #fff !important;
            background: #3182ce;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: #3182ce !important;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            margin-bottom: 20px;
        }

        @yield('styles')
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('attendance.dashboard') }}">
                <i class="bi bi-shield-check"></i> نظام الحضور
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('attendance.dashboard') ? 'active' : '' }}"
                            href="{{ route('attendance.dashboard') }}">
                            <i class="bi bi-speedometer2"></i> لوحة البيانات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('attendance.monthly') ? 'active' : '' }}"
                            href="{{ route('attendance.monthly') }}">
                            <i class="bi bi-grid-3x3"></i> الشيت الشهري
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('attendance.report') ? 'active' : '' }}"
                            href="{{ route('attendance.report') }}">
                            <i class="bi bi-file-earmark-text"></i> التقارير التفصيلية
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('attendance.import') ? 'active' : '' }}"
                            href="{{ route('attendance.import') }}">
                            <i class="bi bi-cloud-upload"></i> استيراد ملفات
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center text-muted">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <span class="fw-bold">مدير الموارد البشرية</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-2">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>

</html>