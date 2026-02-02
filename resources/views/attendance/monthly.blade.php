@extends('layouts.app')

@section('title', 'الشيت الشهري الذكي')

@section('styles')
.matrix-container {
background: white;
border-radius: 12px;
padding: 15px;
box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.table-matrix th, .table-matrix td {
padding: 6px 2px;
text-align: center;
border: 1px solid #dee2e6;
font-size: 12px;
min-width: 32px;
}

.table-matrix th.sticky-col, .table-matrix td.sticky-col {
position: sticky;
right: 0;
background: #f8fafc;
z-index: 10;
min-width: 130px;
text-align: right;
padding-right: 10px;
}

.day-cell {
cursor: pointer;
transition: all 0.2s;
height: 35px;
}

/* Colors as requested: Absent Red, Present Green */
.status-present { background: #198754 !important; color: white !important; } /* Green */
.status-late { background: #ffc107 !important; color: #000 !important; font-weight: bold; } /* Yellow */
.status-absent { background: #dc3545 !important; color: white !important; } /* Red */
.status-empty { color: #cbd5e0; }

.legend-item { display: inline-flex; align-items: center; margin-left: 20px; font-size: 14px; }
.legend-box { width: 14px; height: 14px; border-radius: 2px; margin-left: 5px; }
@endsection

@section('content')
<div class="matrix-container position-relative">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 text-primary fw-bold"><i class="bi bi-grid-3x3-gap"></i> شيت حضور الموظفين</h4>
        <div class="d-flex gap-2">
            <input type="month" id="monthFilter" class="form-control" value="{{ now()->format('Y-m') }}">
            <button class="btn btn-primary" id="loadBtn"><i class="bi bi-arrow-repeat"></i> تحديث</button>
        </div>
    </div>

    <div class="mb-3">
        <div class="legend-item">
            <div class="legend-box" style="background: #198754"></div> حضور تام
        </div>
        <div class="legend-item">
            <div class="legend-box" style="background: #ffc107"></div> تأخير
        </div>
        <div class="legend-item">
            <div class="legend-box" style="background: #dc3545"></div> غياب
        </div>
        <small class="text-muted ms-3">* اضغط على اليوم لعرض التفاصيل</small>
    </div>

    <div class="table-responsive">
        <table class="table table-matrix table-bordered" id="matrixTable">
            <thead class="table-light" id="matrixHead"></thead>
            <tbody id="matrixBody"></tbody>
        </table>
    </div>
</div>

<!-- Day Details Modal -->
<div class="modal fade" id="dayDataModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-calendar-event me-2"></i>تفاصيل اليوم</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dayModalBody">
                <!-- Details injected here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const monthFilter = document.getElementById('monthFilter');
    const loadBtn = document.getElementById('loadBtn');
    const matrixHead = document.getElementById('matrixHead');
    const matrixBody = document.getElementById('matrixBody');
    const dayDataModal = new bootstrap.Modal(document.getElementById('dayDataModal'));

    document.addEventListener('DOMContentLoaded', loadMatrix);
    loadBtn.addEventListener('click', loadMatrix);

    function loadMatrix() {
        fetch(`{{ route('attendance.monthly.data') }}?month=${monthFilter.value}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) renderMatrix(data);
            });
    }

    function renderMatrix(data) {
        let headHtml = '<tr><th class="sticky-col">الموظف</th>';
        for (let i = 1; i <= data.days_in_month; i++) headHtml += `<th>${i}</th>`;
        headHtml += '</tr>';
        matrixHead.innerHTML = headHtml;

        let bodyHtml = '';
        data.data.forEach(emp => {
            bodyHtml += `<tr><td class="sticky-col"><strong>موظف (${emp.employee_code})</strong></td>`;
            for (let i = 1; i <= data.days_in_month; i++) {
                const record = emp.days[i];
                let cellClass = 'status-absent';
                let content = '-';

                if (record) {
                    cellClass = record.late_formatted !== '00:00' ? 'status-late' : 'status-present';
                    content = '✔️';
                }

                // Store row data in a global-ish object for modal
                const stringified = record ? encodeURIComponent(JSON.stringify(record)) : null;
                bodyHtml += `<td class="day-cell ${cellClass}" onclick="showDayInfo('${stringified}', '${emp.employee_code}', '${i}')">${content}</td>`;
            }
            bodyHtml += '</tr>';
        });
        matrixBody.innerHTML = bodyHtml;
    }

    function showDayInfo(dataStr, empCode, day) {
        const body = document.getElementById('dayModalBody');
        const month = monthFilter.value;

        if (dataStr === 'null') {
            body.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-x-circle text-danger fs-1"></i>
                    <h5 class="mt-3">الموظف غائب</h5>
                    <p class="text-muted">لم يتم رصد أي بصمة في هذا اليوم (${day}-${month})</p>
                </div>
            `;
        } else {
            const data = JSON.parse(decodeURIComponent(dataStr));
            body.innerHTML = `
                <div class="mb-4 text-center">
                    <span class="badge bg-primary px-3 py-2">موظف: ${empCode}</span>
                    <span class="badge bg-secondary px-3 py-2">التاريخ: ${data.date}</span>
                </div>
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted">وقت الحضور</div>
                            <h4 class="${data.late_formatted !== '00:00' ? 'text-warning' : 'text-success'}">${data.check_in || '-'}</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted">وقت الانصراف</div>
                            <h4 class="text-danger">${data.check_out || '-'}</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted">التأخير</div>
                            <h4 class="text-danger">${data.late_formatted}</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted">إجمالي الساعات</div>
                            <h4 class="text-primary">${data.total_hours}</h4>
                        </div>
                    </div>
                </div>
                <hr>
                <h6>جميع بصمات اليوم:</h6>
                <table class="table table-sm table-striped">
                    <tbody>
                        ${data.all_records.map(r => `<tr><td>${r.time}</td><td>${r.notes || '-'}</td></tr>`).join('')}
                    </tbody>
                </table>
            `;
        }
        dayDataModal.show();
    }
</script>
@endsection