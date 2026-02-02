@extends('layouts.app')

@section('title', 'تقرير الحضور والانصراف')

@section('styles')
.filter-card {
background: white;
padding: 20px;
border-radius: 8px;
box-shadow: 0 2px 4px rgba(0,0,0,0.1);
margin-bottom: 20px;
}

.report-card {
background: white;
padding: 20px;
border-radius: 8px;
box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table thead {
background: #f8f9fa;
}

.loading {
text-align: center;
padding: 40px;
display: none;
}

.no-data {
text-align: center;
padding: 40px;
color: #6c757d;
display: none;
}

.badge-check-in {
background: #d1e7dd;
color: #0f5132;
}

.badge-check-out {
background: #f8d7da;
color: #842029;
}

.text-danger-custom {
color: #dc3545;
font-weight: bold;
}

.text-success-custom {
color: #198754;
font-weight: bold;
}
@endsection

@section('content')
<!-- Filters -->
<div class="filter-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> الفلاتر</h5>
        <small class="text-muted fw-normal fs-6">(مواعيد العمل الأساسية: 08:00 - 16:00)</small>
    </div>
    <form id="filterForm">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">الموظف</label>
                <select class="form-select" id="employeeFilter" name="employee_code">
                    <option value="">الكل</option>
                    @foreach($employeeCodes as $code)
                    <option value="{{ $code }}">موظف ({{ $code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">التاريخ (يوم محدد)</label>
                <input type="date" class="form-control" id="dateFilter" name="date">
            </div>
            <div class="col-md-4">
                <label class="form-label">الشهر</label>
                <input type="month" class="form-control" id="monthFilter" name="month">
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> بحث
            </button>
            <button type="button" class="btn btn-secondary" id="resetBtn">
                <i class="bi bi-arrow-clockwise"></i> إعادة تعيين
            </button>
        </div>
    </form>
</div>

<!-- Report -->
<div class="report-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5><i class="bi bi-table"></i> نتائج التقرير</h5>
        <span class="badge bg-primary" id="recordCount">0 سجل</span>
    </div>

    <!-- Loading -->
    <div class="loading" id="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">جاري التحميل...</span>
        </div>
        <p class="mt-2 text-primary font-monospace">جاري معالجة البيانات...</p>
    </div>

    <!-- No Data -->
    <div class="no-data" id="noData">
        <i class="bi bi-inbox" style="font-size: 48px;"></i>
        <p class="mt-2">لا توجد سجلات مطابقة لهذه الفلاتر</p>
    </div>

    <!-- Table -->
    <div class="table-responsive" id="reportTable" style="display: none;">
        <table class="table table-hover border">
            <thead class="table-light">
                <tr>
                    <th>الموظف</th>
                    <th>التاريخ</th>
                    <th>الحضور</th>
                    <th>الانصراف</th>
                    <th>إجمالي الساعات</th>
                    <th>التأخير</th>
                    <th>الإضافي</th>
                    <th width="50"></th>
                </tr>
            </thead>
            <tbody id="reportBody">
            </tbody>
        </table>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>تفاصيل حركات اليوم</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const filterForm = document.getElementById('filterForm');
    const loading = document.getElementById('loading');
    const noData = document.getElementById('noData');
    const reportTable = document.getElementById('reportTable');
    const reportBody = document.getElementById('reportBody');
    const recordCount = document.getElementById('recordCount');
    const dateFilter = document.getElementById('dateFilter');
    const monthFilter = document.getElementById('monthFilter');
    const resetBtn = document.getElementById('resetBtn');

    // Initial load
    document.addEventListener('DOMContentLoaded', function() {
        loadData();
    });

    // Form submit
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadData();
    });

    // Reset button
    resetBtn.addEventListener('click', function() {
        filterForm.reset();
        loadData();
    });

    // Filter clearing logic
    dateFilter.addEventListener('change', function() {
        if (dateFilter.value) monthFilter.value = '';
    });
    monthFilter.addEventListener('change', function() {
        if (monthFilter.value) dateFilter.value = '';
    });

    function loadData() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }

        loading.style.display = 'block';
        noData.style.display = 'none';
        reportTable.style.display = 'none';

        fetch('{{ route("attendance.report.data") }}?' + params.toString())
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                if (data.success && data.data.length > 0) {
                    displayData(data.data);
                    recordCount.textContent = data.data.length + ' سجل';
                } else {
                    noData.style.display = 'block';
                    recordCount.textContent = '0 سجل';
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                noData.style.display = 'block';
                console.error('Fetch Error:', error);
            });
    }

    function displayData(data) {
        reportBody.innerHTML = '';
        data.forEach(record => {
            const row = document.createElement('tr');
            const lateClass = record.late_formatted !== '00:00' ? 'text-danger-custom' : '';
            const overtimeClass = record.overtime_formatted !== '00:00' ? 'text-success-custom' : '';

            row.innerHTML = `
                <td><strong>${record.employee_name}</strong></td>
                <td>${record.date}</td>
                <td><span class="badge badge-check-in text-dark border-success border">${record.check_in || '-'}</span></td>
                <td><span class="badge badge-check-out text-dark border-danger border">${record.check_out || '-'}</span></td>
                <td><strong>${record.total_hours || '-'}</strong></td>
                <td class="${lateClass}">${record.late_formatted}</td>
                <td class="${overtimeClass}">${record.overtime_formatted}</td>
                <td>
                    <button class="btn btn-sm btn-link" onclick='showDetails(${JSON.stringify(record)})'>
                        <i class="bi bi-eye"></i>
                    </button>
                </td>
            `;
            reportBody.appendChild(row);
        });
        reportTable.style.display = 'block';
    }

    function showDetails(record) {
        const modalBody = document.getElementById('modalBody');
        let html = `
            <div class="alert alert-info py-2">
                <strong>الموظف:</strong> ${record.employee_name} | <strong>التاريخ:</strong> ${record.date}
            </div>
            <table class="table table-sm table-striped">
                <thead>
                    <tr><th>التوقيت</th><th>ملاحظات</th></tr>
                </thead>
                <tbody>
        `;
        record.all_records.forEach(r => {
            html += `<tr><td>${r.time}</td><td>${r.notes || '-'}</td></tr>`;
        });
        html += `
                </tbody>
            </table>
            <div class="row mt-3 g-2">
                <div class="col-md-6"><div class="p-2 border rounded bg-light text-center">التأخير: <strong>${record.late_formatted}</strong></div></div>
                <div class="col-md-6"><div class="p-2 border rounded bg-light text-center">الإضافي: <strong>${record.overtime_formatted}</strong></div></div>
            </div>
        `;
        modalBody.innerHTML = html;
        new bootstrap.Modal(document.getElementById('detailsModal')).show();
    }
</script>
@endsection