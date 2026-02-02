@extends('layouts.app')

@section('title', 'استيراد بيانات الحضور')

@section('styles')
.upload-card {
background: white;
padding: 30px;
border-radius: 8px;
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
margin-bottom: 20px;
}

.upload-area {
border: 2px dashed #dee2e6;
border-radius: 6px;
padding: 40px 20px;
text-align: center;
background: #fafafa;
cursor: pointer;
}

.upload-area:hover {
border-color: #0d6efd;
background: #f8f9fa;
}

.upload-area.dragover {
border-color: #198754;
background: #d1e7dd;
}

.upload-icon {
font-size: 48px;
color: #6c757d;
margin-bottom: 15px;
}

.btn-primary {
padding: 10px 30px;
}

.progress-section {
display: none;
margin-top: 20px;
}

.stats-row {
display: none;
margin-top: 15px;
}

.stat-box {
background: #f8f9fa;
padding: 15px;
border-radius: 6px;
text-align: center;
border-right: 3px solid #dee2e6;
}

.stat-box.total { border-right-color: #0d6efd; }
.stat-box.processing { border-right-color: #0dcaf0; }
.stat-box.success { border-right-color: #198754; }
.stat-box.failed { border-right-color: #dc3545; }

.stat-number {
font-size: 24px;
font-weight: bold;
margin-bottom: 5px;
}

.stat-label {
font-size: 13px;
color: #6c757d;
}

.table-card {
background: white;
padding: 20px;
border-radius: 8px;
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.table thead {
background: #f8f9fa;
}

.badge {
padding: 6px 12px;
}
@endsection

@section('content')
<!-- Upload Section -->
<div class="upload-card">
    <h5 class="mb-3"><i class="bi bi-cloud-upload"></i> رفع ملف Excel</h5>
    <p class="text-muted">الترتيب المطلوب للأعمدة: 1. التاريخ (Date)، 2. كود الموظف (Code)، 3. الوقت (Time)، 4. ملاحظات (اختياري)</p>

    <div class="upload-area" id="uploadZone">
        <i class="bi bi-file-earmark-excel upload-icon"></i>
        <h5>اختر ملف Excel للرفع</h5>
        <p class="text-muted mb-3">أو اسحب الملف وأفلته هنا</p>
        <input type="file" id="fileInput" accept=".xlsx,.xls,.csv" style="display: none;">
        <button class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
            <i class="bi bi-folder2-open"></i> اختيار ملف
        </button>
        <p class="text-muted small mt-3 mb-0">الحد الأقصى: 10MB | الصيغ: XLSX, XLS, CSV</p>
    </div>

    <!-- Progress Section -->
    <div class="progress-section" id="progressSection">
        <div class="alert alert-info" id="statusAlert">
            <i class="bi bi-info-circle"></i> <span id="statusText">جاري رفع الملف...</span>
        </div>

        <div class="progress" style="height: 25px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated"
                id="progressBar"
                role="progressbar"
                style="width: 0%">0%</div>
        </div>

        <!-- Stats -->
        <div class="row stats-row g-3 mt-2" id="statsRow">
            <div class="col-md-3">
                <div class="stat-box total">
                    <div class="stat-number text-primary" id="totalRows">0</div>
                    <div class="stat-label">إجمالي السجلات</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box processing">
                    <div class="stat-number text-info" id="processedRows">0</div>
                    <div class="stat-label">تم معالجتها</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box success">
                    <div class="stat-number text-success" id="successRows">0</div>
                    <div class="stat-label">ناجحة</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box failed">
                    <div class="stat-number text-danger" id="failedRows">0</div>
                    <div class="stat-label">فاشلة</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Imports Table -->
@if($recentImports->count() > 0)
<div class="table-card">
    <h5 class="mb-3"><i class="bi bi-clock-history"></i> عمليات الاستيراد الأخيرة</h5>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="60">#</th>
                    <th>اسم الملف</th>
                    <th width="120">الحالة</th>
                    <th width="100">الإجمالي</th>
                    <th width="100">ناجحة</th>
                    <th width="100">فاشلة</th>
                    <th width="150">التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentImports as $import)
                <tr>
                    <td>{{ $import->id }}</td>
                    <td>
                        <i class="bi bi-file-earmark-excel text-success"></i>
                        {{ $import->filename }}
                    </td>
                    <td>
                        @if($import->status == 'pending')
                        <span class="badge bg-warning text-dark">قيد الانتظار</span>
                        @elseif($import->status == 'processing')
                        <span class="badge bg-info">جاري المعالجة</span>
                        @elseif($import->status == 'completed')
                        <span class="badge bg-success">مكتمل</span>
                        @else
                        <span class="badge bg-danger">فشل</span>
                        @endif
                    </td>
                    <td>{{ number_format($import->total_rows) }}</td>
                    <td><strong class="text-success">{{ number_format($import->success_rows) }}</strong></td>
                    <td><strong class="text-danger">{{ number_format($import->failed_rows) }}</strong></td>
                    <td class="text-muted small">{{ $import->created_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const progressSection = document.getElementById('progressSection');
    const progressBar = document.getElementById('progressBar');
    const statusAlert = document.getElementById('statusAlert');
    const statusText = document.getElementById('statusText');
    const statsRow = document.getElementById('statsRow');

    let currentImportId = null;
    let pollingInterval = null;

    // Drag and drop
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });

    uploadZone.addEventListener('dragleave', function() {
        uploadZone.classList.remove('dragover');
    });

    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            handleFileUpload(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileUpload(e.target.files[0]);
        }
    });

    function handleFileUpload(file) {
        const formData = new FormData();
        formData.append('excel_file', file);

        progressSection.style.display = 'block';
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
        statusAlert.className = 'alert alert-info';
        statusText.textContent = 'جاري رفع الملف...';
        statsRow.style.display = 'none';

        fetch('{{ route("attendance.upload") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentImportId = data.import_id;
                    statusAlert.className = 'alert alert-success';
                    statusText.textContent = 'تم رفع الملف بنجاح! جاري المعالجة...';
                    statsRow.style.display = 'flex';
                    startPolling();
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('حدث خطأ أثناء رفع الملف');
                console.error(error);
            });
    }

    function startPolling() {
        pollingInterval = setInterval(function() {
            fetch('/attendance/status/' + currentImportId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateProgress(data.data);

                        if (data.data.status === 'completed' || data.data.status === 'failed') {
                            clearInterval(pollingInterval);

                            if (data.data.status === 'completed') {
                                statusAlert.className = 'alert alert-success';
                                statusText.textContent = 'تمت المعالجة بنجاح!';
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                statusAlert.className = 'alert alert-danger';
                                statusText.textContent = 'فشلت المعالجة: ' + data.data.error_message;
                            }
                        }
                    }
                })
                .catch(error => console.error(error));
        }, 2000);
    }

    function updateProgress(data) {
        const percentage = data.progress_percentage;
        progressBar.style.width = percentage + '%';
        progressBar.textContent = percentage.toFixed(1) + '%';

        document.getElementById('totalRows').textContent = data.total_rows.toLocaleString();
        document.getElementById('processedRows').textContent = data.processed_rows.toLocaleString();
        document.getElementById('successRows').textContent = data.success_rows.toLocaleString();
        document.getElementById('failedRows').textContent = data.failed_rows.toLocaleString();
    }

    function showError(message) {
        statusAlert.className = 'alert alert-danger';
        statusText.textContent = message;
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
    }
</script>
@endsection