@extends('layouts.layout')

@section('styles')
    <style>
        .table-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        .table-card {
            background: #fff;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #e0e0e0;
            position: relative;
        }
        .table-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .table-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        }
        .table-card .table-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
        }
        .table-card .worker-name {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-card .light-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .table-card .light-indicator.red { background: #e74c3c; box-shadow: 0 0 10px #e74c3c; }
        .table-card .light-indicator.green { background: #2ecc71; box-shadow: 0 0 10px #2ecc71; }
        .table-card .light-indicator.blue { background: #3498db; box-shadow: 0 0 10px #3498db; }
        .table-card .light-indicator.yellow { background: #e2a03f; box-shadow: 0 0 10px #e2a03f; }
        .table-card .light-indicator.off { background: #ccc; }

        /* ESP32 Status Badge */
        .table-card .esp32-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ccc;
        }
        .table-card .esp32-badge.online {
            background: #28a745;
            animation: pulse-badge 1.5s infinite;
        }
        @keyframes pulse-badge {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.2); }
        }

        /* Control Panel */
        .control-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            color: #fff;
            position: sticky;
            top: 20px;
        }
        .light-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            font-size: 24px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .light-btn:hover { transform: scale(1.1); }
        .light-btn.red { background: #e74c3c; }
        .light-btn.green { background: #2ecc71; }
        .light-btn.blue { background: #3498db; }
        .light-btn.yellow { background: #e2a03f; }
        .light-btn.off { background: #555; }
        .light-btn.active {
            box-shadow: 0 0 20px currentColor;
            transform: scale(1.1);
        }
        .production-input {
            font-size: 2rem;
            text-align: center;
            font-weight: bold;
        }
        .quick-add-btn {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            font-weight: bold;
        }

        /* Filter Buttons */
        .filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-btn:hover, .filter-btn.active {
            background: #667eea;
            color: #fff;
            border-color: #667eea;
        }

        /* Status Legend */
        .status-legend {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
        }
        .legend-item .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Table Grid --}}
        <div class="col-xl-8 col-lg-7 col-md-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Quick Select Table</h3>
                    <a href="{{ route('supervisor.scan') }}" class="btn btn-outline-primary">
                        Scan QR
                    </a>
                </div>

                {{-- Status Legend --}}
                <div class="status-legend">
                    <div class="legend-item">
                        <span class="dot" style="background: #e74c3c; box-shadow: 0 0 5px #e74c3c;"></span>
                        Alert
                    </div>
                    <div class="legend-item">
                        <span class="dot" style="background: #2ecc71; box-shadow: 0 0 5px #2ecc71;"></span>
                        Good
                    </div>
                    <div class="legend-item">
                        <span class="dot" style="background: #3498db; box-shadow: 0 0 5px #3498db;"></span>
                        Help
                    </div>
                    <div class="legend-item">
                        <span class="dot" style="background: #e2a03f; box-shadow: 0 0 5px #e2a03f;"></span>
                        Warning
                    </div>
                    <div class="legend-item">
                        <span class="dot" style="background: #ccc;"></span>
                        Off
                    </div>
                    <div class="legend-item">
                        <span class="dot" style="background: #28a745; animation: pulse-badge 1.5s infinite;"></span>
                        ESP32 Online
                    </div>
                </div>

                {{-- Filter --}}
                <div class="d-flex gap-2 mb-4 flex-wrap">
                    <button class="filter-btn active" onclick="filterTables('all')">All</button>
                    <button class="filter-btn" onclick="filterTables('red')">Alerts</button>
                    <button class="filter-btn" onclick="filterTables('green')">Good</button>
                    <button class="filter-btn" onclick="filterTables('blue')">Help</button>
                    <button class="filter-btn" onclick="filterTables('yellow')">Warning</button>
                    <button class="filter-btn" onclick="filterTables('assigned')">Assigned</button>
                    <button class="filter-btn" onclick="filterTables('online')">Online</button>
                </div>

                {{-- Table Grid --}}
                <div class="table-grid" id="tableGrid">
                    @foreach($tables as $table)
                        <div class="table-card"
                             data-table-id="{{ $table->id }}"
                             data-table-number="{{ $table->table_number }}"
                             data-light-status="{{ $table->current_light_status ?? 'off' }}"
                             data-has-worker="{{ $table->currentAssignment ? 'yes' : 'no' }}"
                             data-worker-id="{{ $table->currentAssignment?->worker?->id }}"
                             data-worker-name="{{ $table->currentAssignment?->worker?->name ?? 'Unassigned' }}"
                             data-esp32-online="{{ $table->esp32_online ? 'yes' : 'no' }}"
                             onclick="selectTable(this)">

                            {{-- ESP32 Status Badge - Now using esp32_online from controller --}}
                            <span class="esp32-badge {{ $table->esp32_online ? 'online' : '' }}"
                                  title="{{ $table->esp32_online ? 'ESP32 Online' : 'ESP32 Offline' }}"></span>

                            {{-- Light Indicator --}}
                            <span class="light-indicator {{ $table->current_light_status ?? 'off' }}"></span>

                            <div class="table-number">{{ $table->table_number }}</div>
                            <div class="worker-name">{{ $table->currentAssignment?->worker?->name ?? 'Unassigned' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Control Panel --}}
        <div class="col-xl-4 col-lg-5 col-md-12 layout-spacing">
            <div class="control-panel">
                {{-- No Table Selected State --}}
                <div id="noTableSelected" class="text-center py-5">
                    <p class="mb-0 opacity-75">Select a table from the grid</p>
                </div>

                {{-- Table Controls --}}
                <div id="tableControls" style="display: none;">
                    <input type="hidden" id="selectedTableId">
                    <input type="hidden" id="selectedWorkerId">

                    <div class="text-center mb-3">
                        <h4 id="selectedTableNumber" class="mb-1">T001</h4>
                        <small id="selectedWorkerName" class="opacity-75">Worker Name</small>
                        <div class="mt-2">
                        <span id="selectedEsp32Status" class="badge rounded-pill px-3 py-2" style="background: rgba(255,255,255,0.2);">
                            ESP32 Offline
                        </span>
                        </div>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.2);">

                    {{-- Light Controls --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Light Control</h6>
                        <div class="d-flex justify-content-center gap-2 mb-2">
                            <button class="light-btn red" onclick="setLight('red')" title="Alert"></button>
                            <button class="light-btn green" onclick="setLight('green')" title="Good"></button>
                            <button class="light-btn blue" onclick="setLight('blue')" title="Help"></button>
                            <button class="light-btn yellow" onclick="setLight('yellow')" title="Warning"></button>
                            <button class="light-btn off" onclick="setLight('off')" title="Off"></button>
                        </div>
                        <small id="lightStatus" class="opacity-75">Current: Off</small>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.2);">

                    {{-- Production Form --}}
                    <div class="bg-white text-dark rounded p-3">
                        <h6 class="mb-3">Log Production</h6>

                        <div class="mb-3">
                            <label class="form-label">Garments Count</label>
                            <div class="d-flex align-items-center gap-2">
                                <button class="btn btn-outline-secondary quick-add-btn" onclick="adjustCount(-10)">-10</button>
                                <button class="btn btn-outline-secondary quick-add-btn" onclick="adjustCount(-1)">-1</button>
                                <input type="number" id="garmentsCount" class="form-control production-input" value="0" min="0">
                                <button class="btn btn-outline-primary quick-add-btn" onclick="adjustCount(1)">+1</button>
                                <button class="btn btn-outline-primary quick-add-btn" onclick="adjustCount(10)">+10</button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Type</label>
                            <input type="text" id="productType" class="form-control" placeholder="Optional">
                        </div>

                        <button class="btn btn-success w-100" onclick="submitProduction()">
                            Submit Production
                        </button>
                    </div>
                </div>
            </div>

            {{-- Status Messages --}}
            <div id="statusMessage" class="alert mt-3" style="display: none;"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let selectedTable = null;

        function selectTable(element) {
            // Remove previous selection
            document.querySelectorAll('.table-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Select new
            element.classList.add('selected');

            // Get data
            selectedTable = {
                id: element.dataset.tableId,
                tableNumber: element.dataset.tableNumber,
                lightStatus: element.dataset.lightStatus,
                workerId: element.dataset.workerId,
                workerName: element.dataset.workerName,
                hasWorker: element.dataset.hasWorker === 'yes',
                esp32Online: element.dataset.esp32Online === 'yes'
            };

            // Show controls
            document.getElementById('noTableSelected').style.display = 'none';
            document.getElementById('tableControls').style.display = 'block';

            // Update UI
            document.getElementById('selectedTableNumber').textContent = selectedTable.tableNumber;
            document.getElementById('selectedWorkerName').textContent = selectedTable.workerName;
            document.getElementById('selectedTableId').value = selectedTable.id;
            document.getElementById('selectedWorkerId').value = selectedTable.workerId;

            // ESP32 Status
            const esp32Badge = document.getElementById('selectedEsp32Status');
            if (selectedTable.esp32Online) {
                esp32Badge.textContent = 'ESP32 Online';
                esp32Badge.style.background = 'rgba(40, 167, 69, 0.3)';
            } else {
                esp32Badge.textContent = 'ESP32 Offline';
                esp32Badge.style.background = 'rgba(255,255,255,0.2)';
            }

            // Light buttons
            updateLightButtons(selectedTable.lightStatus);

            // Reset form
            document.getElementById('garmentsCount').value = 0;
            document.getElementById('productType').value = '';
        }

        function updateLightButtons(color) {
            document.querySelectorAll('.light-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            const activeBtn = document.querySelector('.control-panel .light-btn.' + color);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }

            document.getElementById('lightStatus').textContent = 'Current: ' + color.charAt(0).toUpperCase() + color.slice(1);
        }

        function setLight(color) {
            if (!selectedTable) return;

            fetch('{{ route("supervisor.light.set") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    table_id: selectedTable.id,
                    light_color: color
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateLightButtons(color);

                        // Update table card
                        const card = document.querySelector(`.table-card[data-table-id="${selectedTable.id}"]`);
                        if (card) {
                            card.dataset.lightStatus = color;
                            card.querySelector('.light-indicator').className = 'light-indicator ' + color;
                        }

                        // Show message
                        let msg = data.message;
                        if (data.data && data.data.command_queued) {
                            msg += ' (Queued)';
                        }
                        showStatus(msg, 'success');
                    } else {
                        showStatus('Failed to set light', 'danger');
                    }
                })
                .catch(error => {
                    showStatus('Error: ' + error.message, 'danger');
                });
        }

        function adjustCount(amount) {
            const input = document.getElementById('garmentsCount');
            let value = parseInt(input.value) || 0;
            value = Math.max(0, value + amount);
            input.value = value;
        }

        function submitProduction() {
            if (!selectedTable || !selectedTable.hasWorker) {
                showStatus('No worker assigned to this table', 'danger');
                return;
            }

            const garmentsCount = parseInt(document.getElementById('garmentsCount').value) || 0;

            if (garmentsCount <= 0) {
                showStatus('Please enter garments count', 'danger');
                return;
            }

            fetch('{{ route("supervisor.production.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    table_id: selectedTable.id,
                    worker_id: selectedTable.workerId,
                    garments_count: garmentsCount,
                    product_type: document.getElementById('productType').value
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showStatus(data.message + ' (' + data.data.logged_at + ')', 'success');
                        document.getElementById('garmentsCount').value = 0;
                        document.getElementById('productType').value = '';
                    } else {
                        showStatus(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showStatus('Error: ' + error.message, 'danger');
                });
        }

        function filterTables(filter) {
            // Update filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // Filter cards
            document.querySelectorAll('.table-card').forEach(card => {
                let show = true;

                if (filter === 'red' && card.dataset.lightStatus !== 'red') show = false;
                if (filter === 'green' && card.dataset.lightStatus !== 'green') show = false;
                if (filter === 'blue' && card.dataset.lightStatus !== 'blue') show = false;
                if (filter === 'yellow' && card.dataset.lightStatus !== 'yellow') show = false;
                if (filter === 'assigned' && card.dataset.hasWorker !== 'yes') show = false;
                if (filter === 'online' && card.dataset.esp32Online !== 'yes') show = false;

                card.style.display = show ? 'block' : 'none';
            });
        }

        function showStatus(message, type) {
            const statusDiv = document.getElementById('statusMessage');
            statusDiv.className = 'alert alert-' + type + ' mt-3';
            statusDiv.textContent = message;
            statusDiv.style.display = 'block';

            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 4000);
        }

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
                                card.dataset.esp32Online = status.online ? 'yes' : 'no';
                                const badge = card.querySelector('.esp32-badge');
                                if (badge) {
                                    badge.className = 'esp32-badge ' + (status.online ? 'online' : '');
                                    badge.title = status.online ? 'ESP32 Online' : 'ESP32 Offline';
                                }

                                // Update light status
                                card.dataset.lightStatus = status.current_light_status;
                                const light = card.querySelector('.light-indicator');
                                if (light) {
                                    light.className = 'light-indicator ' + status.current_light_status;
                                }
                            }
                        });

                        // Update selected table if any
                        if (selectedTable) {
                            const status = data.statuses[selectedTable.id];
                            if (status) {
                                selectedTable.esp32Online = status.online;
                                selectedTable.lightStatus = status.current_light_status;

                                const esp32Badge = document.getElementById('selectedEsp32Status');
                                if (esp32Badge) {
                                    if (status.online) {
                                        esp32Badge.textContent = 'ESP32 Online';
                                        esp32Badge.style.background = 'rgba(40, 167, 69, 0.3)';
                                    } else {
                                        esp32Badge.textContent = 'ESP32 Offline';
                                        esp32Badge.style.background = 'rgba(255,255,255,0.2)';
                                    }
                                }

                                updateLightButtons(status.current_light_status);
                            }
                        }
                    }
                });
        }, 10000);
    </script>
@endsection
