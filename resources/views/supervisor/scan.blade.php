{{-- resources/views/supervisor/scan.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="#">Supervisor</a></li>
<li class="breadcrumb-item active" aria-current="page">QR Scanner</li>
@endsection

@section('styles')
<link href="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.css') }}" rel="stylesheet">
<link href="{{ asset('assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css') }}" rel="stylesheet">
<style>
    #reader { width: 100%; max-width: 400px; margin: 0 auto; border-radius: 12px; overflow: hidden; }
    #reader video { border-radius: 12px; }
    .light-btn { width: 55px; height: 55px; border-radius: 50%; border: 3px solid #e0e6ed; transition: all 0.2s ease; cursor: pointer; }
    .light-btn:hover { transform: scale(1.05); }
    .light-btn.active { transform: scale(1.1); box-shadow: 0 0 15px currentColor; border-color: #fff; }
    .light-btn-red { background: #e7515a; color: #e7515a; }
    .light-btn-green { background: #1abc9c; color: #1abc9c; }
    .light-btn-blue { background: #2196f3; color: #2196f3; }
    .light-btn-yellow { background: #e2a03f; color: #e2a03f; }
    .light-btn-off { background: #888ea8; color: #888ea8; }
    .esp32-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .esp32-status.online { background: rgba(27, 85, 226, 0.15); color: #1b55e2; }
    .esp32-status.offline { background: rgba(231, 81, 90, 0.15); color: #e7515a; }
    .esp32-dot { width: 8px; height: 8px; border-radius: 50%; }
    .esp32-status.online .esp32-dot { background: #1b55e2; animation: pulse-dot 1.5s infinite; }
    .esp32-status.offline .esp32-dot { background: #e7515a; }
    @keyframes pulse-dot { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.2); } }
    .worker-card { background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .stat-item { text-align: center; padding: 10px; }
    .stat-value { font-size: 1.5rem; font-weight: 700; color: #0e1726; }
    .stat-label { font-size: 11px; color: #888ea8; text-transform: uppercase; letter-spacing: 0.5px; }
    .command-queued { font-size: 11px; color: #1abc9c; margin-top: 8px; }
</style>
@endsection

@section('content')
<div class="row layout-top-spacing">
    
    {{-- Scanner Card --}}
    <div class="col-xl-5 col-lg-6 col-md-12 col-sm-12 layout-spacing">
        <div class="widget-content widget-content-area br-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera me-2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    QR Scanner
                </h5>
            </div>
            
            <div id="reader"></div>
            
            <div class="text-center mt-3">
                <button id="startScanBtn" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-play me-1"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                    Start Scanner
                </button>
                <button id="stopScanBtn" class="btn btn-danger" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-square me-1"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
                    Stop
                </button>
            </div>
            
            <div class="alert alert-light-info mt-3 mb-0" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info me-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                Point camera at QR code on table
            </div>
        </div>
    </div>

    {{-- Worker Info & Controls --}}
    <div class="col-xl-7 col-lg-6 col-md-12 col-sm-12 layout-spacing">
        
        {{-- No Worker Selected --}}
        <div id="noWorkerSection">
            <div class="widget-content widget-content-area br-8">
                <div class="text-center py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#888ea8" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-maximize mb-3"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
                    <h5 class="text-muted">Scan a QR Code</h5>
                    <p class="text-muted mb-0">Scan the QR code on a table to view worker info and controls</p>
                </div>
            </div>
        </div>

        {{-- Worker Info Card --}}
        <div id="workerInfoSection" style="display: none;">
            <div class="worker-card p-4">
                
                {{-- Header with Table & ESP32 Status --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge badge-light-primary fs-6" id="tableNumber">T001</span>
                        <span class="badge badge-light-secondary ms-2" id="tableName">Sewing Line 1</span>
                    </div>
                    <div id="esp32Status" class="esp32-status offline">
                        <span class="esp32-dot"></span>
                        <span id="esp32StatusText">ESP32 Offline</span>
                    </div>
                </div>

                {{-- Worker Profile --}}
                <div class="text-center mb-3">
                    <img id="workerPhoto" src="{{ asset('assets/src/assets/img/profile-30.png') }}" alt="Worker" class="rounded-circle mb-2" width="80" height="80" style="object-fit: cover; border: 3px solid #e0e6ed;">
                    <h5 class="mb-0" id="workerName">Worker Name</h5>
                    <small class="text-muted" id="workerID">WRK-001</small>
                </div>

                {{-- Stats Row --}}
                <div class="row mb-4">
                    <div class="col-4">
                        <div class="stat-item">
                            <div class="stat-value text-primary" id="todayProduction">0</div>
                            <div class="stat-label">Today</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <div class="stat-value text-success" id="hourlyTarget">0</div>
                            <div class="stat-label">Target/Hr</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <div class="stat-value" id="currentLightDisplay">⚫</div>
                            <div class="stat-label">Light</div>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Light Control --}}
                <div class="mb-4">
                    <h6 class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-sun me-2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                        Light Control
                    </h6>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <button class="light-btn light-btn-red" data-color="red" title="Quality Issue">🔴</button>
                        <button class="light-btn light-btn-green" data-color="green" title="Good Work">🟢</button>
                        <button class="light-btn light-btn-blue" data-color="blue" title="Need Help">🔵</button>
                        <button class="light-btn light-btn-yellow" data-color="yellow" title="Warning">🟡</button>
                        <button class="light-btn light-btn-off" data-color="off" title="Turn Off">⚫</button>
                    </div>
                    <div id="commandQueued" class="command-queued text-center" style="display: none;">
                        ✓ Command queued for ESP32
                    </div>
                </div>

                <hr>

                {{-- Production Form --}}
                <div>
                    <h6 class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-3 me-2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                        Log Production
                    </h6>
                    <form id="productionForm">
                        <input type="hidden" id="formTableId" name="table_id">
                        <input type="hidden" id="formWorkerId" name="worker_id">
                        
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Garments <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="garmentsCount" name="garments_count" min="1" required placeholder="0">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Product Type</label>
                                <select class="form-select" id="productType" name="product_type">
                                    <option value="">Select...</option>
                                    <option value="Shirt">Shirt</option>
                                    <option value="Pant">Pant</option>
                                    <option value="Jacket">Jacket</option>
                                    <option value="T-Shirt">T-Shirt</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="productionNotes" name="notes" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check me-1"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Log Production
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script>
    let html5QrcodeScanner = null;
    let currentTableId = null;
    let currentWorkerId = null;
    let esp32Online = false;

    // Start Scanner
    document.getElementById('startScanBtn').addEventListener('click', function() {
        this.style.display = 'none';
        document.getElementById('stopScanBtn').style.display = 'inline-block';
        
        html5QrcodeScanner = new Html5Qrcode("reader");
        html5QrcodeScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            onScanFailure
        ).catch(err => {
            console.error('Camera error:', err);
            Swal.fire('Camera Error', 'Could not access camera. Please check permissions.', 'error');
            document.getElementById('startScanBtn').style.display = 'inline-block';
            document.getElementById('stopScanBtn').style.display = 'none';
        });
    });

    // Stop Scanner
    document.getElementById('stopScanBtn').addEventListener('click', function() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop();
        }
        this.style.display = 'none';
        document.getElementById('startScanBtn').style.display = 'inline-block';
    });

    // On QR Scan Success
    function onScanSuccess(decodedText, decodedResult) {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop();
            document.getElementById('stopScanBtn').style.display = 'none';
            document.getElementById('startScanBtn').style.display = 'inline-block';
        }
        processQRCode(decodedText);
    }

    function onScanFailure(error) {
        // Ignore scan failures
    }

    // Process QR Code
    function processQRCode(qrCode) {
        axios.post('{{ route("supervisor.scan.process") }}', { qr_code: qrCode })
            .then(response => {
                if (response.data.success) {
                    displayTableInfo(response.data.data);
                } else {
                    Swal.fire('Error', response.data.message || 'QR code not recognized', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', error.response?.data?.message || 'Failed to process QR code', 'error');
            });
    }

    // Display Table Info
    function displayTableInfo(data) {
        document.getElementById('noWorkerSection').style.display = 'none';
        document.getElementById('workerInfoSection').style.display = 'block';

        // Table info
        currentTableId = data.table.id;
        document.getElementById('tableNumber').textContent = data.table.table_number;
        document.getElementById('tableName').textContent = data.table.table_name || 'Workstation';
        document.getElementById('formTableId').value = data.table.id;
        document.getElementById('todayProduction').textContent = data.today_production || 0;
        document.getElementById('hourlyTarget').textContent = data.table.hourly_target || 50;

        // ESP32 Status
        esp32Online = data.table.esp32_online || false;
        updateESP32Status(esp32Online);

        // Worker info
        if (data.worker) {
            currentWorkerId = data.worker.id;
            document.getElementById('workerName').textContent = data.worker.name;
            document.getElementById('workerID').textContent = data.worker.worker_id;
            document.getElementById('workerPhoto').src = data.worker.photo || '{{ asset("assets/src/assets/img/profile-30.png") }}';
            document.getElementById('formWorkerId').value = data.worker.id;
        } else {
            currentWorkerId = null;
            document.getElementById('workerName').textContent = 'No Worker Assigned';
            document.getElementById('workerID').textContent = '-';
            document.getElementById('workerPhoto').src = '{{ asset("assets/src/assets/img/profile-30.png") }}';
            document.getElementById('formWorkerId').value = '';
        }

        // Current light status
        updateLightDisplay(data.table.current_light_status || 'off');
        
        // Set active light button
        document.querySelectorAll('.light-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.querySelector(`.light-btn[data-color="${data.table.current_light_status}"]`);
        if (activeBtn) activeBtn.classList.add('active');
        
        // Hide command queued message
        document.getElementById('commandQueued').style.display = 'none';
    }

    // Update ESP32 Status Display
    function updateESP32Status(online) {
        const statusEl = document.getElementById('esp32Status');
        const statusText = document.getElementById('esp32StatusText');
        
        if (online) {
            statusEl.classList.remove('offline');
            statusEl.classList.add('online');
            statusText.textContent = 'ESP32 Online';
        } else {
            statusEl.classList.remove('online');
            statusEl.classList.add('offline');
            statusText.textContent = 'ESP32 Offline';
        }
    }

    // Update Light Display
    function updateLightDisplay(color) {
        const display = document.getElementById('currentLightDisplay');
        const icons = { red: '🔴', green: '🟢', blue: '🔵', yellow: '🟡', off: '⚫' };
        display.textContent = icons[color] || '⚫';
        display.className = 'stat-value';
        if (color === 'red') display.classList.add('text-danger');
        else if (color === 'green') display.classList.add('text-success');
        else if (color === 'blue') display.classList.add('text-info');
        else if (color === 'yellow') display.classList.add('text-warning');
    }

    // Light Control Buttons
    document.querySelectorAll('.light-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const color = this.dataset.color;
            setLight(color);
            
            document.querySelectorAll('.light-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Set Light
    function setLight(color) {
        const reasons = {
            red: 'Quality Issue',
            green: 'Good Work',
            blue: 'Need Help',
            yellow: 'Warning',
            off: 'Reset'
        };

        axios.post('{{ route("supervisor.light.set") }}', {
            table_id: currentTableId,
            light_color: color,
            reason: reasons[color] || 'Manual Control'
        })
        .then(response => {
            if (response.data.success) {
                updateLightDisplay(color);
                
                // Show ESP32 queue status
                if (response.data.command_queued) {
                    document.getElementById('commandQueued').style.display = 'block';
                    setTimeout(() => {
                        document.getElementById('commandQueued').style.display = 'none';
                    }, 3000);
                }
                
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                Toast.fire({
                    icon: 'success',
                    title: color === 'off' ? 'Light turned off' : `Light set to ${color.toUpperCase()}`
                });
            }
        })
        .catch(error => {
            Swal.fire('Error', error.response?.data?.message || 'Failed to set light', 'error');
        });
    }

    // Production Form Submit
    document.getElementById('productionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentWorkerId) {
            Swal.fire('Error', 'No worker assigned to this table', 'warning');
            return;
        }

        const formData = {
            table_id: document.getElementById('formTableId').value,
            worker_id: document.getElementById('formWorkerId').value,
            garments_count: document.getElementById('garmentsCount').value,
            product_type: document.getElementById('productType').value,
            notes: document.getElementById('productionNotes').value
        };

        axios.post('{{ route("supervisor.production.store") }}', formData)
            .then(response => {
                if (response.data.success) {
                    // Update today's production count
                    const currentCount = parseInt(document.getElementById('todayProduction').textContent);
                    document.getElementById('todayProduction').textContent = currentCount + parseInt(formData.garments_count);
                    
                    // Reset form
                    document.getElementById('garmentsCount').value = '';
                    document.getElementById('productType').value = '';
                    document.getElementById('productionNotes').value = '';
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Production Logged!',
                        text: `${formData.garments_count} garments recorded`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            })
            .catch(error => {
                Swal.fire('Error', error.response?.data?.message || 'Failed to log production', 'error');
            });
    });
</script>
@endsection
