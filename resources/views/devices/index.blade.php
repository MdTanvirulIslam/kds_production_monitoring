@extends('layouts.layout')

@section('title', 'ESP32 Devices')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/src/plugins/src/table/datatable/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/src/plugins/css/light/table/datatable/dt-global_style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/src/plugins/css/light/table/datatable/custom_dt_custom.css') }}">
    <style>
        /* Status Dot Animation */
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        .status-dot.online {
            background: #fff;
            animation: pulse-dot 1.5s infinite;
        }
        .status-dot.offline {
            background: rgba(255,255,255,0.7);
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.9); }
        }

        /* LED Indicator */
        .led-indicator {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-block;
            border: 2px solid rgba(0,0,0,0.1);
            vertical-align: middle;
        }
        .led-indicator.off { background: #888; }
        .led-indicator.red { background: #e7515a; box-shadow: 0 0 12px rgba(231, 81, 90, 0.6); }
        .led-indicator.green { background: #00ab55; box-shadow: 0 0 12px rgba(0, 171, 85, 0.6); }
        .led-indicator.blue { background: #2196f3; box-shadow: 0 0 12px rgba(33, 150, 243, 0.6); }
        .led-indicator.yellow { background: #e2a03f; box-shadow: 0 0 12px rgba(226, 160, 63, 0.6); }
        .led-indicator.white { background: #fff; box-shadow: 0 0 12px rgba(255, 255, 255, 0.8); border-color: #ddd; }

        /* Connection Status Buttons */
        .connection-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            min-width: 90px;
            justify-content: center;
        }
        .connection-status.online {
            background: linear-gradient(135deg, #00ab55 0%, #00d68f 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 171, 85, 0.4);
        }
        .connection-status.offline {
            background: linear-gradient(135deg, #e7515a 0%, #ff6b6b 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(231, 81, 90, 0.4);
        }

        /* Notification Row */
        .notification-row.unread {
            background-color: #fffbeb !important;
        }

        /* Color Buttons */
        .color-btn {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 2px solid transparent;
            cursor: pointer;
            padding: 0;
            transition: all 0.2s ease;
        }
        .color-btn:hover {
            transform: scale(1.15);
            border-color: #3b3f5c;
        }
        .color-btn.red { background: #e7515a; }
        .color-btn.green { background: #00ab55; }
        .color-btn.blue { background: #2196f3; }
        .color-btn.yellow { background: #e2a03f; }
        .color-btn.off { background: #888; }

        /* Stat Cards */
        .stat-card {
            border-radius: 10px;
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
        }

        /* Table Styles */
        .device-table-number {
            font-size: 1.1rem;
            font-weight: 700;
            color: #3b3f5c;
        }
        .shift-badge {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 15px;
        }
        .worker-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .worker-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4361ee 0%, #805dca 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 11px;
            color: #fff;
        }

        /* Refresh Indicator */
        .refresh-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #888;
        }
        .refresh-indicator .spinner {
            width: 14px;
            height: 14px;
            border: 2px solid #e0e6ed;
            border-top-color: #4361ee;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: none;
        }
        .refresh-indicator.loading .spinner {
            display: inline-block;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .table > :not(caption) > * > * {
            vertical-align: middle;
        }
        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: center;
        }
        .action-buttons .btn {
            padding: 4px 8px;
        }

        /* DataTable Pagination Arrow Icons */
        .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next {
            font-size: 0;
            padding: 5px 10px !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.previous::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-left: 2px solid #888;
            border-bottom: 2px solid #888;
            transform: rotate(45deg);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.next::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-right: 2px solid #888;
            border-top: 2px solid #888;
            transform: rotate(45deg);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.previous:hover::before,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next:hover::before {
            border-color: #4361ee;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled::before {
            border-color: #ccc;
        }

        /* New notification highlight animation */
        @keyframes newNotification {
            0% { background-color: #fef3c7; }
            50% { background-color: #fde68a; }
            100% { background-color: #fffbeb; }
        }
        .notification-row.new-alert {
            animation: newNotification 1s ease-in-out 3;
        }
    </style>
@endsection

@section('content')
    <div class="layout-px-spacing">
        <div class="middle-content container-xxl p-0">

            <!-- Breadcrumb -->
            <div class="page-meta mb-4">
                <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">ESP32 Devices</li>
                    </ol>
                </nav>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                    <div class="card stat-card bg-primary h-100">
                        <div class="card-body d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h2 class="text-white mb-1" id="totalDevices">{{ $stats['total_devices'] }}</h2>
                                <p class="text-white mb-0 opacity-75">Total Devices</p>
                            </div>
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect></svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                    <div class="card stat-card bg-success h-100">
                        <div class="card-body d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h2 class="text-white mb-1" id="onlineDevices">{{ $stats['online_devices'] }}</h2>
                                <p class="text-white mb-0 opacity-75">Online</p>
                            </div>
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><circle cx="12" cy="20" r="2"></circle></svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                    <div class="card stat-card bg-danger h-100">
                        <div class="card-body d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h2 class="text-white mb-1" id="offlineDevices">{{ $stats['offline_devices'] }}</h2>
                                <p class="text-white mb-0 opacity-75">Offline</p>
                            </div>
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path><circle cx="12" cy="20" r="2"></circle></svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                    <div class="card stat-card bg-warning h-100">
                        <div class="card-body d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h2 class="text-white mb-1" id="unreadAlerts">{{ $stats['unread_alerts'] }}</h2>
                                <p class="text-white mb-0 opacity-75">Unread Alerts</p>
                            </div>
                            <div class="stat-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Table - NOW ON TOP -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        Button Press Notifications
                        <span class="badge bg-warning ms-2" id="notificationBadge">{{ $stats['unread_alerts'] }}</span>
                    </h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="refresh-indicator" id="notifRefreshIndicator">
                            <div class="spinner"></div>
                            <span>Auto-refresh: <span id="notifCountdown">5</span>s</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Mark All Read
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="clearAll()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                Clear All
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="notificationsTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                            <tr>
                                <th>Table</th>
                                <th>Device</th>
                                <th>Worker</th>
                                <th>Shift</th>
                                <th>Alert Type</th>
                                <th>Previous LED</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                            </thead>
                            <tbody id="notificationsBody">
                            @foreach($notifications as $n)
                                @php
                                    $notifShiftName = null;
                                    $shiftData = $n->table?->currentAssignment?->shift;
                                    if ($shiftData) {
                                        if (is_string($shiftData)) {
                                            $decoded = json_decode($shiftData, true);
                                            $notifShiftName = $decoded['name'] ?? null;
                                        } elseif (is_object($shiftData)) {
                                            $notifShiftName = $shiftData->name ?? null;
                                        } elseif (is_array($shiftData)) {
                                            $notifShiftName = $shiftData['name'] ?? null;
                                        }
                                    }
                                @endphp
                                <tr class="notification-row {{ !$n->is_read ? 'unread' : '' }}" data-id="{{ $n->id }}">
                                    <td><span class="device-table-number">{{ $n->table_number }}</span></td>
                                    <td><code class="text-primary">{{ $n->device_id }}</code></td>
                                    <td>
                                        @if($n->worker)
                                            <div class="worker-info">
                                                <div class="worker-avatar">{{ strtoupper(substr($n->worker->name, 0, 2)) }}</div>
                                                <span>{{ $n->worker->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notifShiftName)
                                            <span class="badge shift-badge bg-light-info">{{ $notifShiftName }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                    <span class="badge bg-warning text-dark">
                                        {{ ucfirst(str_replace('_', ' ', $n->alert_type)) }}
                                    </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="led-indicator {{ $n->previous_color ?? 'off' }}" style="width:18px;height:18px;"></span>
                                            <span class="text-uppercase small">{{ $n->previous_color ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $n->pressed_at?->format('h:i A') }}</div>
                                        <small class="text-muted">{{ $n->pressed_at?->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        @if($n->is_read)
                                            <span class="badge bg-secondary">Read</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Unread</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @if(!$n->is_read)
                                                <button class="btn btn-sm btn-outline-success" onclick="markRead({{ $n->id }})" title="Mark as Read">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                </button>
                                            @endif
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteNotif({{ $n->id }})" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Devices Table -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect></svg>
                        Connected Devices
                    </h5>
                    <div class="refresh-indicator" id="deviceRefreshIndicator">
                        <div class="spinner"></div>
                        <span>Auto-refresh: <span id="refreshCountdown">5</span>s</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="devicesTable" class="table table-striped table-hover" style="width:100%">
                            <thead>
                            <tr>
                                <th>Table</th>
                                <th>Device</th>
                                <th>Worker</th>
                                <th>Shift</th>
                                <th>LED Status</th>
                                <th>Connection</th>
                                <th>Last Seen</th>
                                <th class="text-center">Set Light</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($devices as $device)
                                <tr data-id="{{ $device['id'] }}">
                                    <td>
                                        <span class="device-table-number">{{ $device['table_number'] }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <code class="text-primary">{{ $device['device_id'] }}</code>
                                            @if($device['ip_address'])
                                                <br><small class="text-muted">{{ $device['ip_address'] }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($device['worker'] !== 'Unassigned')
                                            <div class="worker-info">
                                                <div class="worker-avatar">{{ strtoupper(substr($device['worker'], 0, 2)) }}</div>
                                                <span>{{ $device['worker'] }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($device['shift'])
                                            <span class="badge shift-badge bg-light-info">{{ $device['shift'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="led-indicator {{ $device['current_color'] ?? 'off' }}"></span>
                                            <span class="text-uppercase fw-bold small">{{ $device['current_color'] ?? 'off' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                    <span class="connection-status {{ $device['online'] ? 'online' : 'offline' }}">
                                        <span class="status-dot {{ $device['online'] ? 'online' : 'offline' }}"></span>
                                        {{ $device['online'] ? 'Online' : 'Offline' }}
                                    </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $device['last_seen_text'] }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="color-btn red" onclick="setLight({{ $device['id'] }}, 'red')" title="Red"></button>
                                            <button class="color-btn green" onclick="setLight({{ $device['id'] }}, 'green')" title="Green"></button>
                                            <button class="color-btn blue" onclick="setLight({{ $device['id'] }}, 'blue')" title="Blue"></button>
                                            <button class="color-btn yellow" onclick="setLight({{ $device['id'] }}, 'yellow')" title="Yellow"></button>
                                            <button class="color-btn off" onclick="setLight({{ $device['id'] }}, 'off')" title="Off"></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/src/plugins/src/table/datatable/datatables.js') }}"></script>
    <script>
        // DataTable instances
        let devicesTable, notificationsTable;
        let countdown = 5;
        let countdownTimer;
        let knownNotificationIds = new Set();

        // Initialize known notification IDs
        @foreach($notifications as $n)
        knownNotificationIds.add({{ $n->id }});
        @endforeach

        // Initialize DataTables
        $(document).ready(function() {
            // Notifications DataTable - on top
            notificationsTable = $('#notificationsTable').DataTable({
                "dom": '<"row"<"col-md-6"l><"col-md-6"f>>' +
                    '<"table-responsive"t>' +
                    '<"row"<"col-md-6"i><"col-md-6"p>>',
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "order": [[6, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": [8] },
                    { "className": "text-center", "targets": [8] }
                ],
                "language": {
                    "search": "",
                    "searchPlaceholder": "Search notifications...",
                    "lengthMenu": "_MENU_ per page",
                    "paginate": {
                        "previous": "",
                        "next": ""
                    }
                }
            });

            // Devices DataTable
            devicesTable = $('#devicesTable').DataTable({
                "dom": '<"row"<"col-md-6"l><"col-md-6"f>>' +
                    '<"table-responsive"t>' +
                    '<"row"<"col-md-6"i><"col-md-6"p>>',
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "order": [[0, "asc"]],
                "columnDefs": [
                    { "orderable": false, "targets": [7] },
                    { "className": "text-center", "targets": [7] }
                ],
                "language": {
                    "search": "",
                    "searchPlaceholder": "Search devices...",
                    "lengthMenu": "_MENU_ per page",
                    "paginate": {
                        "previous": "",
                        "next": ""
                    }
                }
            });

            // Start auto-refresh
            startAutoRefresh();
        });

        // Auto-refresh with countdown
        function startAutoRefresh() {
            countdown = 5;
            updateCountdown();

            countdownTimer = setInterval(function() {
                countdown--;
                updateCountdown();

                if (countdown <= 0) {
                    refreshAll();
                    countdown = 5;
                }
            }, 1000);
        }

        function updateCountdown() {
            document.getElementById('refreshCountdown').textContent = countdown;
            document.getElementById('notifCountdown').textContent = countdown;
        }

        // Refresh both tables
        function refreshAll() {
            refreshDevices();
            refreshNotifications();
        }

        // Refresh devices silently
        function refreshDevices() {
            const indicator = document.getElementById('deviceRefreshIndicator');
            indicator.classList.add('loading');

            fetch('{{ route("devices.status") }}')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Update stats
                        document.getElementById('totalDevices').textContent = data.stats.total;
                        document.getElementById('onlineDevices').textContent = data.stats.online;
                        document.getElementById('offlineDevices').textContent = data.stats.offline;

                        // Update each device row
                        data.devices.forEach(device => {
                            const row = document.querySelector(`#devicesTable tr[data-id="${device.id}"]`);
                            if (row) {
                                // Update LED indicator
                                const led = row.querySelector('.led-indicator');
                                if (led) {
                                    led.className = 'led-indicator ' + (device.current_color || 'off');
                                }
                                const ledText = row.querySelector('.led-indicator + span');
                                if (ledText) {
                                    ledText.textContent = (device.current_color || 'off').toUpperCase();
                                }

                                // Update connection status
                                const connStatus = row.querySelector('.connection-status');
                                if (connStatus) {
                                    connStatus.className = 'connection-status ' + (device.online ? 'online' : 'offline');
                                    connStatus.innerHTML = `<span class="status-dot ${device.online ? 'online' : 'offline'}"></span>${device.online ? 'Online' : 'Offline'}`;
                                }

                                // Update last seen
                                const lastSeen = row.querySelectorAll('td')[6];
                                if (lastSeen) {
                                    lastSeen.innerHTML = `<small class="text-muted">${device.last_seen}</small>`;
                                }
                            }
                        });
                    }
                })
                .finally(() => {
                    indicator.classList.remove('loading');
                });
        }

        // Refresh notifications silently
        function refreshNotifications() {
            const indicator = document.getElementById('notifRefreshIndicator');
            indicator.classList.add('loading');

            fetch('{{ route("devices.notifications") }}')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Update counts
                        document.getElementById('unreadAlerts').textContent = data.unread_count;
                        document.getElementById('notificationBadge').textContent = data.unread_count;

                        // Check for new notifications
                        data.notifications.forEach(notif => {
                            const existingRow = document.querySelector(`#notificationsTable tr[data-id="${notif.id}"]`);

                            if (!existingRow) {
                                // New notification - add to table
                                addNewNotificationRow(notif);
                            } else {
                                // Update existing row
                                updateNotificationRow(existingRow, notif);
                            }
                        });

                        // Remove deleted notifications
                        const currentIds = data.notifications.map(n => n.id);
                        document.querySelectorAll('#notificationsTable tbody tr[data-id]').forEach(row => {
                            const rowId = parseInt(row.getAttribute('data-id'));
                            if (!currentIds.includes(rowId)) {
                                notificationsTable.row(row).remove().draw(false);
                            }
                        });
                    }
                })
                .finally(() => {
                    indicator.classList.remove('loading');
                });
        }

        // Add new notification row
        function addNewNotificationRow(notif) {
            const workerHtml = notif.worker
                ? `<div class="worker-info"><div class="worker-avatar">${notif.worker.name.substring(0,2).toUpperCase()}</div><span>${notif.worker.name}</span></div>`
                : '<span class="text-muted">N/A</span>';

            const shiftName = getShiftName(notif.table?.current_assignment?.shift);
            const shiftHtml = shiftName
                ? `<span class="badge shift-badge bg-light-info">${shiftName}</span>`
                : '<span class="text-muted">-</span>';

            const pressedAt = notif.pressed_at ? new Date(notif.pressed_at) : null;
            const timeHtml = pressedAt
                ? `<div>${pressedAt.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true})}</div><small class="text-muted">${pressedAt.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}</small>`
                : '-';

            const statusHtml = notif.is_read
                ? '<span class="badge bg-secondary">Read</span>'
                : '<span class="badge bg-warning text-dark">Unread</span>';

            const actionsHtml = `
            <div class="action-buttons">
                ${!notif.is_read ? `<button class="btn btn-sm btn-outline-success" onclick="markRead(${notif.id})" title="Mark as Read"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg></button>` : ''}
                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotif(${notif.id})" title="Delete"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
            </div>
        `;

            const rowNode = notificationsTable.row.add([
                `<span class="device-table-number">${notif.table_number}</span>`,
                `<code class="text-primary">${notif.device_id}</code>`,
                workerHtml,
                shiftHtml,
                `<span class="badge bg-warning text-dark">${notif.alert_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>`,
                `<div class="d-flex align-items-center gap-2"><span class="led-indicator ${notif.previous_color || 'off'}" style="width:18px;height:18px;"></span><span class="text-uppercase small">${notif.previous_color || 'N/A'}</span></div>`,
                timeHtml,
                statusHtml,
                actionsHtml
            ]).draw(false).node();

            $(rowNode).addClass('notification-row new-alert' + (!notif.is_read ? ' unread' : ''));
            $(rowNode).attr('data-id', notif.id);

            // Remove animation class after animation completes
            setTimeout(() => {
                $(rowNode).removeClass('new-alert');
            }, 3000);

            // Play notification sound (optional)
            // playNotificationSound();

            showToast('New button press alert from ' + notif.table_number, 'warning');
        }

        // Update existing notification row
        function updateNotificationRow(row, notif) {
            if (notif.is_read) {
                row.classList.remove('unread');
                const statusCell = row.querySelectorAll('td')[7];
                if (statusCell) {
                    statusCell.innerHTML = '<span class="badge bg-secondary">Read</span>';
                }
                const markReadBtn = row.querySelector('.btn-outline-success');
                if (markReadBtn) markReadBtn.remove();
            }
        }

        // Extract shift name from JSON
        function getShiftName(shift) {
            if (!shift) return null;
            if (typeof shift === 'string') {
                try {
                    const decoded = JSON.parse(shift);
                    return decoded.name || null;
                } catch (e) {
                    return null;
                }
            }
            if (typeof shift === 'object') {
                return shift.name || null;
            }
            return null;
        }

        // Set light color
        function setLight(tableId, color) {
            fetch('{{ route("devices.command") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ table_id: tableId, color: color })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`#devicesTable tr[data-id="${tableId}"]`);
                        if (row) {
                            const led = row.querySelector('.led-indicator');
                            if (led) {
                                led.className = 'led-indicator ' + color;
                            }
                            const ledText = row.querySelector('.led-indicator + span');
                            if (ledText) {
                                ledText.textContent = color.toUpperCase();
                            }
                        }
                        showToast('Light set to ' + color.toUpperCase(), 'success');
                    }
                });
        }

        // Mark notification as read
        function markRead(id) {
            fetch('/devices/notifications/' + id + '/read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`#notificationsTable tr[data-id="${id}"]`);
                        if (row) {
                            row.classList.remove('unread');
                            const statusCell = row.querySelectorAll('td')[7];
                            if (statusCell) {
                                statusCell.innerHTML = '<span class="badge bg-secondary">Read</span>';
                            }
                            row.querySelector('.btn-outline-success')?.remove();
                        }
                        refreshNotificationCount();
                        showToast('Marked as read', 'success');
                    }
                });
        }

        // Refresh notification count only
        function refreshNotificationCount() {
            fetch('{{ route("devices.notifications") }}?count_only=true')
                .then(r => r.json())
                .then(data => {
                    if (data.unread_count !== undefined) {
                        document.getElementById('unreadAlerts').textContent = data.unread_count;
                        document.getElementById('notificationBadge').textContent = data.unread_count;
                    }
                });
        }

        // Mark all as read
        function markAllAsRead() {
            fetch('{{ route("devices.mark-all-read") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll('#notificationsTable tr.unread').forEach(row => {
                            row.classList.remove('unread');
                            const statusCell = row.querySelectorAll('td')[7];
                            if (statusCell) {
                                statusCell.innerHTML = '<span class="badge bg-secondary">Read</span>';
                            }
                            row.querySelector('.btn-outline-success')?.remove();
                        });
                        refreshNotificationCount();
                        showToast('All notifications marked as read', 'success');
                    }
                });
        }

        // Delete notification
        function deleteNotif(id) {
            if (!confirm('Delete this notification?')) return;

            fetch('/devices/notifications/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`#notificationsTable tr[data-id="${id}"]`);
                        if (row) {
                            notificationsTable.row(row).remove().draw(false);
                        }
                        refreshNotificationCount();
                        showToast('Notification deleted', 'success');
                    }
                });
        }

        // Clear all notifications
        function clearAll() {
            if (!confirm('Clear ALL notifications? This cannot be undone.')) return;

            fetch('{{ route("devices.clear-all") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        notificationsTable.clear().draw();
                        refreshNotificationCount();
                        showToast('All notifications cleared', 'success');
                    }
                });
        }

        // Toast notification
        function showToast(message, type = 'info') {
            if (typeof Snackbar !== 'undefined') {
                Snackbar.show({
                    text: message,
                    pos: 'bottom-right',
                    backgroundColor: type === 'success' ? '#00ab55' : type === 'error' ? '#e7515a' : type === 'warning' ? '#e2a03f' : '#2196f3',
                    duration: 3000
                });
            } else {
                const toast = document.createElement('div');
                toast.className = 'alert alert-' + (type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info');
                toast.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;min-width:250px;box-shadow:0 4px 12px rgba(0,0,0,0.15);animation:fadeIn 0.3s';
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            }
        }
    </script>
@endsection
