@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">Supervisor</a></li>
    <li class="breadcrumb-item active" aria-current="page">My Activity</li>
@endsection

@section('styles')
    <link href="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css') }}" rel="stylesheet">
    <style>
        .table-card { position: relative; border-radius: 12px; padding: 15px; cursor: pointer; transition: all 0.2s ease; background: #fff; border: 2px solid #e0e6ed; min-height: 100px; }
        .table-card:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .table-card.selected { border-color: #1b55e2; box-shadow: 0 0 0 3px rgba(27, 85, 226, 0.2); }
        .table-card.light-red { border-left: 4px solid #e7515a; }
        .table-card.light-green { border-left: 4px solid #1abc9c; }
        .table-card.light-blue { border-left: 4px solid #2196f3; }
        .table-card.light-yellow { border-left: 4px solid #e2a03f; }
        .table-number { font-size: 1.1rem; font-weight: 700; color: #0e1726; }
        .table-worker { font-size: 12px; color: #888ea8; margin-top: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .table-production { font-size: 11px; color: #1abc9c; margin-top: 3px; }
        .esp32-badge { position: absolute; top: 8px; right: 8px; width: 10px; height: 10px; border-radius: 50%; }
        .esp32-badge.online { background: #1b55e2; animation: pulse-badge 1.5s infinite; }
        .esp32-badge.offline { background: #bfc9d4; }
        @keyframes pulse-badge { 0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(27, 85, 226, 0.4); } 50% { opacity: 0.8; box-shadow: 0 0 0 4px rgba(27, 85, 226, 0); } }
        .light-indicator { position: absolute; bottom: 8px; right: 8px; font-size: 14px; }
        .control-panel { background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 20px; }
        .light-btn { width: 50px; height: 50px; border-radius: 50%; border: 3px solid #e0e6ed; transition: all 0.2s ease; cursor: pointer; }
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
        .esp32-status.online .esp32-dot { background: #1b55e2; animation: pulse-badge 1.5s infinite; }
        .esp32-status.offline .esp32-dot { background: #e7515a; }
        .filter-btn { padding: 6px 14px; border-radius: 20px; font-size: 12px; margin: 2px; }
        .filter-btn.active { background: #1b55e2; color: #fff; }
        .command-queued { font-size: 11px; color: #1abc9c; margin-top: 8px; }
        .stat-box { text-align: center; padding: 8px; }
        .stat-value { font-size: 1.3rem; font-weight: 700; }
        .stat-label { font-size: 10px; color: #888ea8; text-transform: uppercase; }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">

        {{-- Tables Grid --}}
        <div class="col-xl-8 col-lg-7 col-md-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid me-2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        Select Table
                    </h5>
                    <div id="tableStats" class="text-muted small"></div>
                </div>

                {{-- Filter Buttons --}}
                <div class="mb-3">
                    <button class="btn btn-outline-secondary filter-btn active" data-filter="all">All</button>
                    <button class="btn btn-outline-danger filter-btn" data-filter="red">🔴 Red</button>
                    <button class="btn btn-outline-success filter-btn" data-filter="green">🟢 Green</button>
                    <button class="btn btn-outline-info filter-btn" data-filter="blue">🔵 Blue</button>
                    <button class="btn btn-outline-warning filter-btn" data-filter="yellow">🟡 Yellow</button>
                    <button class="btn btn-outline-primary filter-btn" data-filter="esp32">📡 ESP32 Online</button>
                </div>

                {{-- Tables Grid --}}
                <div class="row g-2" id="tablesGrid">
                    @foreach($tables as $table)
                        <div class="col-xl-2 col-lg-3 col-md-3 col-sm-4 col-6 table-item"
                             data-light="{{ $table->current_light_status ?? 'off' }}"
                             data-esp32="{{ $table->esp32_online ? 'online' : 'offline' }}">
                            <div class="table-card light-{{ $table->current_light_status ?? 'off' }}"
                                 data-table-id="{{ $table->id }}"
                                 data-table-number="{{ $table->table_number }}"
                                 data-table-name="{{ $table->table_name }}"
                                 data-worker-id="{{ $table->currentAssignment?->worker_id }}"
                                 data-worker-name="{{ $table->currentAssignment?->worker?->name ?? 'Unassigned' }}"
                                 data-light="{{ $table->current_light_status ?? 'off' }}"
                                 data-esp32="{{ $table->esp32_online ? '1' : '0' }}"
                                 data-production="{{ $table->today_production ?? 0 }}"
                                 data-target="{{ $table->hourly_target ?? 50 }}">

                                {{-- ESP32 Badge --}}
                                <div class="esp32-badge {{ $table->esp32_online ? 'online' : 'offline' }}"
                                     title="{{ $table->esp32_online ? 'ESP32 Online' : 'ESP32 Offline' }}"></div>

                                {{-- Table Number --}}
                                <div class="table-number">{{ $table->table_number }}</div>

                                {{-- Worker Name --}}
                                <div class="table-worker">
                                    {{ $table->currentAssignment?->worker?->name ?? 'Unassigned' }}
                                </div>

                                {{-- Today Production --}}
                                <div class="table-production">
                                    {{ $table->today_production ?? 0 }} pcs
                                </div>

                                {{-- Light Indicator --}}
                                <div class="light-indicator">
                                    @switch($table->current_light_status)
                                        @case('red') 🔴 @break
                                        @case('green') 🟢 @break
                                        @case('blue') 🔵 @break
                                        @case('yellow') 🟡 @break
                                        @default ⚫
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Control Panel --}}
        <div class="col-xl-4 col-lg-5 col-md-12 col-sm-12 layout-spacing">

            {{-- No Table Selected --}}
            <div id="noTableSection">
                <div class="widget-content widget-content-area br-8">
                    <div class="text-center py-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#888ea8" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mouse-pointer mb-3"><path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"></path><path d="M13 13l6 6"></path></svg>
                        <h5 class="text-muted">Select a Table</h5>
                        <p class="text-muted mb-0">Click on a table to control its light</p>
                    </div>
                </div>
            </div>

            {{-- Control Panel --}}
            <div id="controlSection" style="display: none;">
                <div class="control-panel">

                    {{-- Header with Table & ESP32 Status --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge badge-light-primary fs-5" id="selectedTableNumber">T001</span>
                            <div class="text-muted small mt-1" id="selectedTableName">Sewing Line 1</div>
                        </div>
                        <div id="selectedEsp32Status" class="esp32-status offline">
                            <span class="esp32-dot"></span>
                            <span id="selectedEsp32Text">ESP32 Offline</span>
                        </div>
                    </div>

                    {{-- Worker Info --}}
                    <div class="text-center mb-3 pb-3 border-bottom">
                        <h6 class="mb-1" id="selectedWorkerName">Worker Name</h6>
                        <small class="text-muted" id="selectedWorkerLabel">Assigned Worker</small>
                    </div>

                    {{-- Stats Row --}}
                    <div class="row mb-3">
                        <div class="col-4">
                            <div class="stat-box">
                                <div class="stat-value" id="selectedProduction">0</div>
                                <div class="stat-label">Today</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box">
                                <div class="stat-value" id="selectedTarget">50</div>
                                <div class="stat-label">Target/hr</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box">
                                <div class="stat-value" id="selectedLightDisplay">⚫</div>
                                <div class="stat-label">Light</div>
                            </div>
                        </div>
                    </div>

                    {{-- Light Control --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Light Control</label>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="light-btn light-btn-red" data-color="red" title="Alert - Quality Issue"></button>
                            <button class="light-btn light-btn-green" data-color="green" title="Good Work"></button>
                            <button class="light-btn light-btn-blue" data-color="blue" title="Need Help"></button>
                            <button class="light-btn light-btn-yellow" data-color="yellow" title="Warning"></button>
                            <button class="light-btn light-btn-off" data-color="off" title="Turn Off"></button>
                        </div>
                        <div id="commandQueued" class="command-queued text-center" style="display: none;">
                            ✓ Command queued for ESP32
                        </div>
                    </div>

                    {{-- Quick Production Log --}}
                    <div class="border-top pt-3">
                        <label class="form-label fw-bold small">Quick Production Log</label>
                        <form id="quickProductionForm">
                            <input type="hidden" id="quickTableId">
                            <input type="hidden" id="quickWorkerId">
                            <div class="input-group">
                                <input type="number" class="form-control" id="quickGarments" placeholder="Garments count" min="1" required>
                                <button type="submit" class="btn btn-primary">Log</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        let selectedTableId = null;
        let selectedWorkerId = null;

        // Initial Stats Update
        updateStats();

        function updateStats() {
            const cards = document.querySelectorAll('.table-card');
            let red = 0, green = 0, blue = 0, yellow = 0, esp32Online = 0;
            cards.forEach(card => {
                const light = card.dataset.light;
                const esp32 = card.dataset.esp32 === '1';
                if (light === 'red') red++;
                if (light === 'green') green++;
                if (light === 'blue') blue++;
                if (light === 'yellow') yellow++;
                if (esp32) esp32Online++;
            });
            document.getElementById('tableStats').innerHTML =
                `<span class="text-danger me-2">🔴 ${red}</span>
             <span class="text-success me-2">🟢 ${green}</span>
             <span class="text-info me-2">🔵 ${blue}</span>
             <span class="text-warning me-2">🟡 ${yellow}</span>
             <span class="text-primary">📡 ${esp32Online}</span>`;
        }

        // Filter Buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                document.querySelectorAll('.table-item').forEach(item => {
                    const light = item.dataset.light;
                    const esp32 = item.dataset.esp32;

                    let show = true;
                    if (filter === 'red' && light !== 'red') show = false;
                    if (filter === 'green' && light !== 'green') show = false;
                    if (filter === 'blue' && light !== 'blue') show = false;
                    if (filter === 'yellow' && light !== 'yellow') show = false;
                    if (filter === 'esp32' && esp32 !== 'online') show = false;

                    item.style.display = show ? '' : 'none';
                });
            });
        });

        // Table Card Selection
        document.querySelectorAll('.table-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('.table-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                // Get data
                selectedTableId = this.dataset.tableId;
                selectedWorkerId = this.dataset.workerId;
                const tableNumber = this.dataset.tableNumber;
                const tableName = this.dataset.tableName || '';
                const workerName = this.dataset.workerName;
                const light = this.dataset.light;
                const esp32 = this.dataset.esp32 === '1';
                const production = this.dataset.production;
                const target = this.dataset.target;

                // Show control panel
                document.getElementById('noTableSection').style.display = 'none';
                document.getElementById('controlSection').style.display = 'block';

                // Update UI
                document.getElementById('selectedTableNumber').textContent = tableNumber;
                document.getElementById('selectedTableName').textContent = tableName;
                document.getElementById('selectedWorkerName').textContent = workerName || 'Unassigned';
                document.getElementById('selectedWorkerLabel').textContent = workerName ? 'Assigned Worker' : 'No Worker Assigned';
                document.getElementById('selectedProduction').textContent = production;
                document.getElementById('selectedTarget').textContent = target;
                document.getElementById('quickTableId').value = selectedTableId;
                document.getElementById('quickWorkerId').value = selectedWorkerId || '';

                // Update ESP32 status
                const statusEl = document.getElementById('selectedEsp32Status');
                const statusText = document.getElementById('selectedEsp32Text');
                if (esp32) {
                    statusEl.classList.remove('offline');
                    statusEl.classList.add('online');
                    statusText.textContent = 'ESP32 Online';
                } else {
                    statusEl.classList.remove('online');
                    statusEl.classList.add('offline');
                    statusText.textContent = 'ESP32 Offline';
                }

                // Update light display
                updateLightDisplay(light);

                // Set active light button
                document.querySelectorAll('.light-btn').forEach(btn => btn.classList.remove('active'));
                const activeBtn = document.querySelector(`.light-btn[data-color="${light}"]`);
                if (activeBtn) activeBtn.classList.add('active');

                document.getElementById('commandQueued').style.display = 'none';
            });
        });

        // Update Light Display
        function updateLightDisplay(color) {
            const display = document.getElementById('selectedLightDisplay');
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
                if (!selectedTableId) {
                    Swal.fire('Error', 'Please select a table first', 'warning');
                    return;
                }

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
                table_id: selectedTableId,
                light_color: color,
                reason: reasons[color] || 'Manual Control'
            })
                .then(response => {
                    if (response.data.success) {
                        updateLightDisplay(color);

                        // Update table card
                        const card = document.querySelector(`.table-card[data-table-id="${selectedTableId}"]`);
                        if (card) {
                            card.dataset.light = color;
                            card.className = `table-card light-${color} selected`;
                            const icons = { red: '🔴', green: '🟢', blue: '🔵', yellow: '🟡', off: '⚫' };
                            card.querySelector('.light-indicator').textContent = icons[color] || '⚫';

                            // Update parent item
                            card.closest('.table-item').dataset.light = color;
                        }

                        // Show ESP32 queue status
                        if (response.data.data && response.data.data.command_queued) {
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

                        updateStats();
                    }
                })
                .catch(error => {
                    Swal.fire('Error', error.response?.data?.message || 'Failed to set light', 'error');
                });
        }

        // Quick Production Form
        document.getElementById('quickProductionForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!selectedWorkerId) {
                Swal.fire('Error', 'No worker assigned to this table', 'warning');
                return;
            }

            const garments = document.getElementById('quickGarments').value;

            axios.post('{{ route("supervisor.production.store") }}', {
                table_id: selectedTableId,
                worker_id: selectedWorkerId,
                garments_count: garments,
                product_type: '',
                notes: 'Quick log from table select'
            })
                .then(response => {
                    if (response.data.success) {
                        // Update production count
                        const currentCount = parseInt(document.getElementById('selectedProduction').textContent);
                        const newCount = currentCount + parseInt(garments);
                        document.getElementById('selectedProduction').textContent = newCount;

                        // Update card
                        const card = document.querySelector(`.table-card[data-table-id="${selectedTableId}"]`);
                        if (card) {
                            card.dataset.production = newCount;
                            card.querySelector('.table-production').textContent = newCount + ' pcs';
                        }

                        // Reset form
                        document.getElementById('quickGarments').value = '';

                        Swal.fire({
                            icon: 'success',
                            title: 'Production Logged!',
                            text: `${garments} garments recorded`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    Swal.fire('Error', error.response?.data?.message || 'Failed to log production', 'error');
                });
        });

        // Auto-refresh device status every 10 seconds
        setInterval(function() {
            fetch('{{ route("supervisor.device.status") }}')
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.statuses) {
                        Object.keys(data.statuses).forEach(tableId => {
                            const status = data.statuses[tableId];
                            const card = document.querySelector(`.table-card[data-table-id="${tableId}"]`);
                            if (card) {
                                // Update ESP32 online status
                                card.dataset.esp32 = status.online ? '1' : '0';
                                const badge = card.querySelector('.esp32-badge');
                                if (badge) {
                                    badge.className = 'esp32-badge ' + (status.online ? 'online' : 'offline');
                                    badge.title = status.online ? 'ESP32 Online' : 'ESP32 Offline';
                                }

                                // Update parent item
                                const parentItem = card.closest('.table-item');
                                if (parentItem) {
                                    parentItem.dataset.esp32 = status.online ? 'online' : 'offline';
                                }

                                // Update light status
                                const currentLight = card.dataset.light;
                                if (currentLight !== status.current_light_status) {
                                    card.dataset.light = status.current_light_status;
                                    card.className = card.className.replace(/light-\w+/, 'light-' + status.current_light_status);
                                    const icons = { red: '🔴', green: '🟢', blue: '🔵', yellow: '🟡', off: '⚫' };
                                    const lightIndicator = card.querySelector('.light-indicator');
                                    if (lightIndicator) {
                                        lightIndicator.textContent = icons[status.current_light_status] || '⚫';
                                    }
                                    if (parentItem) {
                                        parentItem.dataset.light = status.current_light_status;
                                    }
                                }
                            }
                        });

                        // Update selected table if any
                        if (selectedTableId) {
                            const status = data.statuses[selectedTableId];
                            if (status) {
                                const statusEl = document.getElementById('selectedEsp32Status');
                                const statusText = document.getElementById('selectedEsp32Text');
                                if (statusEl && statusText) {
                                    if (status.online) {
                                        statusEl.classList.remove('offline');
                                        statusEl.classList.add('online');
                                        statusText.textContent = 'ESP32 Online';
                                    } else {
                                        statusEl.classList.remove('online');
                                        statusEl.classList.add('offline');
                                        statusText.textContent = 'ESP32 Offline';
                                    }
                                }

                                // Update light display if changed
                                updateLightDisplay(status.current_light_status);

                                // Update active button
                                document.querySelectorAll('.light-btn').forEach(btn => btn.classList.remove('active'));
                                const activeBtn = document.querySelector(`.light-btn[data-color="${status.current_light_status}"]`);
                                if (activeBtn) activeBtn.classList.add('active');
                            }
                        }

                        // Update stats
                        updateStats();
                    }
                })
                .catch(err => console.log('Status refresh error:', err));
        }, 10000);
    </script>
@endsection
