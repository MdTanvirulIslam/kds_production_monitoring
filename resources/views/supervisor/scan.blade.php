@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">Supervisor</a></li>
    <li class="breadcrumb-item active" aria-current="page">QR Scanner</li>
@endsection

@section('styles')
    <style>
        #reader {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            overflow: hidden;
        }
        #reader video {
            border-radius: 8px;
        }
        .scanner-container {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
        }
        .worker-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 25px;
            color: #fff;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .worker-info-card .worker-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.5);
            object-fit: cover;
        }
        .worker-info-card h4 {
            color: #fff;
            margin-top: 15px;
        }
        .worker-info-card .text-muted-light {
            color: rgba(255,255,255,0.7);
        }
        .stat-box {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }
        .stat-box h3 {
            color: #fff;
            margin: 0;
            font-size: 1.8rem;
        }
        .stat-box small {
            color: rgba(255,255,255,0.8);
        }
        .light-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #e0e6ed;
            transition: all 0.3s ease;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .light-btn:hover {
            transform: scale(1.1);
        }
        .light-btn.active {
            transform: scale(1.15);
            box-shadow: 0 0 20px currentColor;
        }
        .light-btn-red { background: #e7515a; color: #fff; }
        .light-btn-green { background: #1abc9c; color: #fff; }
        .light-btn-blue { background: #2196f3; color: #fff; }
        .light-btn-off { background: #888ea8; color: #fff; }
        .production-form-card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .scan-result-success {
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .manual-input-section {
            background: #fff3cd;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Page Header --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">📷 QR Code Scanner</h3>
                        <p class="text-muted mb-0">Scan table QR code to log production</p>
                    </div>
                    <a href="{{ route('supervisor.quick-select') }}" class="btn btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid me-1"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        Quick Select Table
                    </a>
                </div>
            </div>
        </div>

        {{-- Scanner Section --}}
        <div class="col-xl-5 col-lg-6 col-md-12 layout-spacing">
            <div class="scanner-container">
                <h5 class="text-center mb-3">📱 Point Camera at QR Code</h5>

                {{-- QR Scanner --}}
                <div id="reader"></div>

                {{-- Scanner Controls --}}
                <div class="text-center mt-3">
                    <button id="startScanBtn" class="btn btn-primary btn-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera me-2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                        Start Scanner
                    </button>
                    <button id="stopScanBtn" class="btn btn-danger btn-lg" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x me-2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Stop Scanner
                    </button>
                </div>

                {{-- Scan Status --}}
                <div id="scanStatus" class="alert alert-info mt-3 text-center">
                    <small>Click "Start Scanner" and point your camera at a table's QR code</small>
                </div>

                {{-- Manual Input Option --}}
                <div class="manual-input-section">
                    <h6>🔢 Or Enter Table Number Manually</h6>
                    <div class="input-group">
                        <input type="text" id="manualTableNumber" class="form-control" placeholder="e.g., T001">
                        <button class="btn btn-secondary" type="button" id="manualSearchBtn">
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Result Section --}}
        <div class="col-xl-7 col-lg-6 col-md-12 layout-spacing">
            {{-- No Table Selected --}}
            <div id="noTableSection">
                <div class="alert alert-light-warning text-center p-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera mb-3"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    <h4>No Table Selected</h4>
                    <p class="mb-0">Scan a QR code or select a table manually to begin logging production.</p>
                </div>
            </div>

            {{-- Table & Worker Info (Hidden initially) --}}
            <div id="tableInfoSection" style="display: none;" class="scan-result-success">
                {{-- Worker Info Card --}}
                <div class="worker-info-card mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <img id="workerPhoto" src="{{ asset('assets/src/assets/img/profile-30.png') }}" alt="Worker" class="worker-photo">
                            <h4 id="workerName">-</h4>
                            <span id="workerID" class="text-muted-light">-</span>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="stat-box">
                                        <h3 id="tableNumber">-</h3>
                                        <small>Table</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="stat-box">
                                        <h3 id="todayProduction">0</h3>
                                        <small>Today's Production</small>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-box">
                                <span id="shiftTime">Shift: Full Day</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Light Control --}}
                <div class="production-form-card mb-4">
                    <h5 class="mb-3">💡 Set Light Indicator</h5>
                    <div class="d-flex justify-content-center gap-3 mb-3">
                        <button class="light-btn light-btn-red" data-color="red" title="Quality Issue / Alert">
                            🔴
                        </button>
                        <button class="light-btn light-btn-green" data-color="green" title="Good Work">
                            🟢
                        </button>
                        <button class="light-btn light-btn-blue" data-color="blue" title="Need Help">
                            🔵
                        </button>
                        <button class="light-btn light-btn-off" data-color="off" title="Turn Off">
                            ⚫
                        </button>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">
                            🔴 Quality Issue &nbsp;|&nbsp; 🟢 Good Work &nbsp;|&nbsp; 🔵 Need Help &nbsp;|&nbsp; ⚫ Normal
                        </small>
                    </div>
                </div>

                {{-- Production Form --}}
                <div class="production-form-card">
                    <h5 class="mb-3">📝 Log Production</h5>
                    <form id="productionForm">
                        <input type="hidden" id="formTableId" name="table_id">
                        <input type="hidden" id="formWorkerId" name="worker_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Garments Count <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-lg" id="garmentsCount" name="garments_count" min="1" placeholder="Enter quantity" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Type</label>
                                <select class="form-select form-select-lg" id="productType" name="product_type">
                                    <option value="">Select type...</option>
                                    <option value="Shirt">Shirt</option>
                                    <option value="Pant">Pant</option>
                                    <option value="T-Shirt">T-Shirt</option>
                                    <option value="Jacket">Jacket</option>
                                    <option value="Dress">Dress</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="productionNotes" name="notes" rows="2" placeholder="Any additional notes..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100" id="submitProductionBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle me-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            Log Production
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Success Modal --}}
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#1abc9c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <h4 class="text-success">Production Logged!</h4>
                    <p id="successMessage" class="text-muted">Successfully logged 10 garments</p>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Continue</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Variables
        let html5QrcodeScanner = null;
        let currentTableId = null;
        let currentWorkerId = null;
        let isScanning = false;

        // CSRF Token for AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // ==========================================
        // QR CODE SCANNER
        // ==========================================

        // Start Scanner
        document.getElementById('startScanBtn').addEventListener('click', function() {
            this.style.display = 'none';
            document.getElementById('stopScanBtn').style.display = 'inline-block';
            document.getElementById('scanStatus').innerHTML = '<small class="text-primary">📷 Scanner active - Point at QR code...</small>';
            document.getElementById('scanStatus').className = 'alert alert-primary mt-3 text-center';

            html5QrcodeScanner = new Html5Qrcode("reader");

            html5QrcodeScanner.start(
                { facingMode: "environment" }, // Use back camera
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                onScanSuccess,
                onScanFailure
            ).catch(err => {
                console.error('Scanner error:', err);
                document.getElementById('scanStatus').innerHTML = '<small class="text-danger">❌ Camera access denied. Please allow camera permission.</small>';
                document.getElementById('scanStatus').className = 'alert alert-danger mt-3 text-center';
                resetScannerButtons();
            });

            isScanning = true;
        });

        // Stop Scanner
        document.getElementById('stopScanBtn').addEventListener('click', function() {
            stopScanner();
        });

        function stopScanner() {
            if (html5QrcodeScanner && isScanning) {
                html5QrcodeScanner.stop().then(() => {
                    isScanning = false;
                    resetScannerButtons();
                }).catch(err => console.error('Stop error:', err));
            }
        }

        function resetScannerButtons() {
            document.getElementById('startScanBtn').style.display = 'inline-block';
            document.getElementById('stopScanBtn').style.display = 'none';
        }

        // On successful scan
        function onScanSuccess(decodedText, decodedResult) {
            console.log('Scanned:', decodedText);

            // Stop scanner
            stopScanner();

            // Update status
            document.getElementById('scanStatus').innerHTML = '<small class="text-success">✅ QR Code detected! Processing...</small>';
            document.getElementById('scanStatus').className = 'alert alert-success mt-3 text-center';

            // Process the QR code
            processQRCode(decodedText);
        }

        function onScanFailure(error) {
            // Silently ignore scan failures (normal behavior when no QR in view)
        }

        // ==========================================
        // PROCESS QR CODE
        // ==========================================

        function processQRCode(qrCode) {
            axios.post('{{ route("supervisor.scan.process") }}', {
                qr_code: qrCode
            }, {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
                .then(response => {
                    if (response.data.success) {
                        displayTableInfo(response.data.data);
                        document.getElementById('scanStatus').innerHTML = '<small class="text-success">✅ Table found! Ready to log production.</small>';
                    } else {
                        showError(response.data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError(error.response?.data?.message || 'Failed to process QR code');
                });
        }

        // ==========================================
        // MANUAL TABLE SEARCH
        // ==========================================

        document.getElementById('manualSearchBtn').addEventListener('click', function() {
            const tableNumber = document.getElementById('manualTableNumber').value.trim();
            if (tableNumber) {
                // Create QR code format for manual search
                processQRCode('TABLE:' + tableNumber + ':0');
            }
        });

        // Enter key for manual search
        document.getElementById('manualTableNumber').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('manualSearchBtn').click();
            }
        });

        // ==========================================
        // DISPLAY TABLE INFO
        // ==========================================

        function displayTableInfo(data) {
            // Hide no table section, show info section
            document.getElementById('noTableSection').style.display = 'none';
            document.getElementById('tableInfoSection').style.display = 'block';

            // Store IDs
            currentTableId = data.table.id;
            document.getElementById('formTableId').value = data.table.id;

            // Table info
            document.getElementById('tableNumber').textContent = data.table.table_number;
            document.getElementById('todayProduction').textContent = data.today_production;

            // Worker info
            if (data.worker) {
                currentWorkerId = data.worker.id;
                document.getElementById('formWorkerId').value = data.worker.id;
                document.getElementById('workerName').textContent = data.worker.name;
                document.getElementById('workerID').textContent = data.worker.worker_id;
                document.getElementById('workerPhoto').src = data.worker.photo || '{{ asset("assets/src/assets/img/profile-30.png") }}';
            } else {
                currentWorkerId = null;
                document.getElementById('formWorkerId').value = '';
                document.getElementById('workerName').textContent = 'No Worker Assigned';
                document.getElementById('workerID').textContent = 'Please assign a worker first';
                document.getElementById('workerPhoto').src = '{{ asset("assets/src/assets/img/profile-30.png") }}';
            }

            // Shift info
            if (data.assignment) {
                document.getElementById('shiftTime').textContent = 'Shift: ' + data.assignment.shift_start + ' - ' + data.assignment.shift_end;
            } else {
                document.getElementById('shiftTime').textContent = 'No shift assigned today';
            }

            // Set active light button
            document.querySelectorAll('.light-btn').forEach(btn => btn.classList.remove('active'));
            const activeBtn = document.querySelector(`.light-btn[data-color="${data.table.current_light_status}"]`);
            if (activeBtn) activeBtn.classList.add('active');
        }

        function showError(message) {
            document.getElementById('scanStatus').innerHTML = '<small class="text-danger">❌ ' + message + '</small>';
            document.getElementById('scanStatus').className = 'alert alert-danger mt-3 text-center';
        }

        // ==========================================
        // LIGHT CONTROL
        // ==========================================

        document.querySelectorAll('.light-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!currentTableId) {
                    alert('Please scan a table first!');
                    return;
                }

                const color = this.dataset.color;
                setLight(color, this);
            });
        });

        function setLight(color, buttonElement) {
            // Visual feedback
            document.querySelectorAll('.light-btn').forEach(btn => btn.classList.remove('active'));
            buttonElement.classList.add('active');

            axios.post('{{ route("supervisor.light.set") }}', {
                table_id: currentTableId,
                light_color: color,
                reason: getReasonForColor(color)
            }, {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
                .then(response => {
                    if (response.data.success) {
                        // Show brief notification
                        const msg = color === 'off' ? 'Light turned off' : 'Light set to ' + color.toUpperCase();
                        showToast(msg, 'success');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to set light', 'error');
                });
        }

        function getReasonForColor(color) {
            switch(color) {
                case 'red': return 'Quality Issue / Alert';
                case 'green': return 'Good Work';
                case 'blue': return 'Need Help';
                default: return 'Reset';
            }
        }

        // ==========================================
        // PRODUCTION FORM SUBMIT
        // ==========================================

        document.getElementById('productionForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!currentTableId) {
                alert('Please scan a table first!');
                return;
            }

            if (!currentWorkerId) {
                alert('No worker assigned to this table. Please assign a worker first.');
                return;
            }

            const garmentsCount = document.getElementById('garmentsCount').value;
            if (!garmentsCount || garmentsCount < 1) {
                alert('Please enter a valid garments count');
                return;
            }

            // Disable submit button
            const submitBtn = document.getElementById('submitProductionBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            axios.post('{{ route("supervisor.production.store") }}', {
                table_id: currentTableId,
                worker_id: currentWorkerId,
                garments_count: garmentsCount,
                product_type: document.getElementById('productType').value,
                notes: document.getElementById('productionNotes').value
            }, {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
                .then(response => {
                    if (response.data.success) {
                        // Update production count
                        const currentCount = parseInt(document.getElementById('todayProduction').textContent);
                        document.getElementById('todayProduction').textContent = currentCount + parseInt(garmentsCount);

                        // Show success modal
                        document.getElementById('successMessage').textContent = 'Successfully logged ' + garmentsCount + ' garments';
                        new bootstrap.Modal(document.getElementById('successModal')).show();

                        // Reset form
                        document.getElementById('garmentsCount').value = '';
                        document.getElementById('productType').value = '';
                        document.getElementById('productionNotes').value = '';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to log production: ' + (error.response?.data?.message || error.message));
                })
                .finally(() => {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle me-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>Log Production';
                });
        });

        // ==========================================
        // TOAST NOTIFICATION
        // ==========================================

        function showToast(message, type = 'success') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 200px;';
            toast.innerHTML = message;
            document.body.appendChild(toast);

            // Remove after 3 seconds
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
@endsection
