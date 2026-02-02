@extends('layouts.app')

@section('title', 'لوحة تحليلات الحضور المتقدمة')

@section('styles')
.stat-card {
transition: all 0.3s;
border-right: 5px solid #3182ce;
}
.stat-card:hover { transform: translateY(-5px); }

.ranking-card {
border-radius: 12px;
overflow: hidden;
height: 100%;
}

.ranking-item {
padding: 12px 15px;
border-bottom: 1px solid #f1f1f1;
display: flex;
justify-content: space-between;
align-items: center;
}

.ranking-item:last-child { border-bottom: none; }

.chart-container {
background: white;
border-radius: 12px;
padding: 20px;
box-shadow: 0 4px 6px rgba(0,0,0,0.02);
margin-bottom: 20px;
}

.badge-late { background: #fff5f5; color: #c53030; }
.badge-working { background: #f0fff4; color: #2f855a; }
@endsection

@section('content')
<!-- Header & Global Month Filter -->
<div class="row mb-4 align-items-center">
    <div class="col-md-9">
        <h4 class="fw-bold text-dark mb-1"><i class="bi bi-cpu text-primary"></i> داشبورد مؤشرات الأداء (KPIs)</h4>
        <p class="text-muted mb-0">تحليل الموظفين الأكثر التزاماً ومقارنة ساعات العمل الفعلية</p>
    </div>
    <div class="col-md-3">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-calendar3"></i></span>
            <input type="month" id="monthFilter" class="form-control" value="{{ now()->format('Y-m') }}">
        </div>
    </div>
</div>

<!-- Key Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-3 shadow-sm" style="border-right-color: #3182ce;">
            <div class="text-muted small fw-bold">إجمالي الموظفين النشطين</div>
            <h2 class="fw-bold mb-0" id="totalEmployees">0</h2>
            <div class="text-primary"><i class="bi bi-people"></i> موظف في التقرير</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 shadow-sm" style="border-right-color: #ecc94b;">
            <div class="text-muted small fw-bold">إجمالي وقت التأخير</div>
            <h2 class="fw-bold mb-0" id="totalLateFormatted">00:00</h2>
            <div class="text-warning"><i class="bi bi-clock-history"></i> ساعة:دقيقة</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-3 shadow-sm" style="border-right-color: #48bb78;">
            <div class="text-muted small fw-bold">إجمالي البصمات</div>
            <h2 class="fw-bold mb-0" id="totalPunches">0</h2>
            <div class="text-success"><i class="bi bi-fingerprint"></i> حركة مسجلة</div>
        </div>
    </div>
</div>

<!-- Performance Ranking -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card ranking-card shadow-sm border-0">
            <div class="card-header bg-success text-white fw-bold py-3">
                <i class="bi bi-trophy-fill me-2"></i> الأعلى التزاماً (أقل تأخير)
            </div>
            <div class="card-body p-0" id="topPerformers">
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card ranking-card shadow-sm border-0">
            <div class="card-header bg-danger text-white fw-bold py-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> الأكثر تأخراً
            </div>
            <div class="card-body p-0" id="needsImprovement">
            </div>
        </div>
    </div>
</div>

<!-- Hours Comparison Chart -->
<div class="row g-3 mb-5">
    <div class="col-md-12">
        <div class="chart-container shadow-sm p-4" style="height: 450px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-graph-up"></i> مقارنة ساعات العمل اليومية (الفعلي vs المخطط 8 ساعات)</h6>
                <div class="small text-muted">المتوسط العام للمؤسسة باليوم</div>
            </div>
            <canvas id="hoursChart"></canvas>
        </div>
    </div>
</div>

<!-- NEW: Individual Employee Analysis Section -->
<hr class="my-5">
<div class="row mb-4">
    <div class="col-12 text-center">
        <h4 class="fw-bold text-dark"><i class="bi bi-person-bounding-box text-info"></i> تحليل الأداء الفردي للموظف</h4>
        <p class="text-muted">اختر الموظف والشهر لعرض الرسم البياني الخاص بساعات عمله</p>
    </div>
</div>

<div class="row justify-content-center mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 p-3">
            <form id="empAnalysisForm" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">اختر الموظف</label>
                    <select id="empSelect" class="form-select">
                        <option value="">-- اختر الموظف --</option>
                        @foreach($employeeCodes as $code)
                        <option value="{{ $code }}">موظف ({{ $code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">اختر الشهر</label>
                    <input type="month" id="empMonth" class="form-control" value="{{ now()->format('Y-m') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info text-white w-100 fw-bold">
                        <i class="bi bi-search"></i> عرض التحليل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-3 mb-5" id="empChartSection" style="display: none;">
    <div class="col-md-12">
        <div class="chart-container shadow-sm p-4" style="height: 450px;">
            <h6 class="fw-bold mb-4" id="empChartTitle"><i class="bi bi-bar-chart"></i> عدد ساعات العمل اليومية للموظف</h6>
            <canvas id="empChart"></canvas>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const monthFilter = document.getElementById('monthFilter');
    const empAnalysisForm = document.getElementById('empAnalysisForm');
    const empSelect = document.getElementById('empSelect');
    const empMonth = document.getElementById('empMonth');
    const empChartSection = document.getElementById('empChartSection');

    let hoursChart = null;
    let empChart = null;

    document.addEventListener('DOMContentLoaded', function() {
        loadDashboard();
    });

    monthFilter.addEventListener('change', loadDashboard);

    function loadDashboard() {
        const month = monthFilter.value;
        fetch(`{{ route('attendance.dashboard.stats') }}?month=${month}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateUI(data);
                }
            });
    }

    function updateUI(data) {
        document.getElementById('totalEmployees').textContent = data.summary.total_employees;
        document.getElementById('totalLateFormatted').textContent = data.summary.total_late_formatted;
        document.getElementById('totalPunches').textContent = data.summary.total_punches;

        let topHtml = '';
        data.ranking.top.forEach((emp, index) => {
            topHtml += `
                <div class="ranking-item">
                    <div>
                        <span class="badge bg-success rounded-circle me-2">${index+1}</span>
                        <strong>${emp.name}</strong>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-working d-block mb-1">${emp.work_days} يوم عمل</span>
                        <small class="text-success fw-bold">تأخير: ${emp.late_formatted}</small>
                    </div>
                </div>
            `;
        });
        document.getElementById('topPerformers').innerHTML = topHtml || '<div class="p-4 text-center text-muted">لا توجد بيانات</div>';

        let bottomHtml = '';
        data.ranking.bottom.forEach((emp, index) => {
            if (emp.late_minutes > 0) {
                bottomHtml += `
                    <div class="ranking-item">
                        <div>
                            <span class="badge bg-danger rounded-circle me-2">${index+1}</span>
                            <strong>${emp.name}</strong>
                        </div>
                        <div class="text-end">
                            <span class="badge badge-late d-block mb-1">${emp.late_formatted} تأخير</span>
                            <small class="text-muted">${emp.work_days} يوم عمل</small>
                        </div>
                    </div>
                `;
            }
        });
        document.getElementById('needsImprovement').innerHTML = bottomHtml || '<div class="p-4 text-center text-muted">لا توجد سجلات تأخير</div>';

        renderGlobalChart(data.charts);
    }

    function renderGlobalChart(chartData) {
        const ctx = document.getElementById('hoursChart').getContext('2d');
        if (hoursChart) hoursChart.destroy();

        hoursChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                        label: 'ساعات العمل الفعلية',
                        data: chartData.actual_data,
                        borderColor: '#3182ce',
                        backgroundColor: 'rgba(49, 130, 206, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3
                    },
                    {
                        label: 'المعيار الرسمي (8 ساعات)',
                        data: chartData.standard_data,
                        borderColor: '#e53e3e',
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 12
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Individual Employee Analysis
    empAnalysisForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const empCode = empSelect.value;
        const month = empMonth.value;

        if (!empCode) {
            alert('الرجاء اختيار موظف أولاً');
            return;
        }

        fetch(`{{ route('attendance.dashboard.employee_stats') }}?employee_code=${empCode}&month=${month}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    empChartSection.style.display = 'block';
                    document.getElementById('empChartTitle').innerHTML = `<i class="bi bi-bar-chart"></i> تحليل ساعات عمل موظف (${empCode}) لشهر ${month}`;
                    renderEmpChart(data);
                    // Smooth scroll to chart
                    empChartSection.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
    });

    function renderEmpChart(chartData) {
        const ctx = document.getElementById('empChart').getContext('2d');
        if (empChart) empChart.destroy();

        empChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'عدد الساعات',
                    data: chartData.data,
                    backgroundColor: '#38b2ac',
                    borderRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 15,
                        title: {
                            display: true,
                            text: 'ساعة'
                        }
                    }
                }
            }
        });
    }
</script>
@endsection