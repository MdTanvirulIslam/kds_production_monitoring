@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">Supervisor</a></li>
    <li class="breadcrumb-item active" aria-current="page">Quick Select</li>
@endsection

@section('styles')
    <style>
        .table-select-card {
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
        }
        .table-select-card:hover {
            border-color: #4361ee;
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(67, 97, 238, 0.2);
        }
        .table-select-card.selected {
            border-color: #1abc9c;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }
        .table-select-card .table-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #3b3f5c;
        }
        .table-select-card .worker-name {
            font-size: 0.85rem;
            color: #888ea8;
        }
        .table-select-card.light-red { border-left: 5px solid #e7515a; }
        .table-select-card.light-green { border-left: 5px solid #1abc9c; }
        .table-select-card.light-blue { border-left: 5px solid #2196f3; }
        .production-panel {
            position: sticky;
            top: 100px;
        }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        {{-- Header --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">📋 Quick Select Table</h3>
                        <p class="text-muted mb-0">Click on a table to log production</p>
                    </div>
                    <a href="{{ route('supervisor.scan') }}" class="btn btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera me-1"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                        Use QR Scanner
                    </a>
                </div>
            </div>
        </div>

        {{-- Table Grid --}}
        <div class="col-xl-8 col-lg-7 col-md-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <h5 class="mb-3">Select a Table</h5>
                <div class="row">
                    @foreach($tables as $table)
                        <div class="col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 mb-3">
                            <div class="table-select-card light-{{ $table->current_light_status }}"
                                 data-table-id="{{ $table->id }}"
                                 data-table-number="{{ $table->table_number }}"
                                 data-worker-id="{{ $table->currentAssignment?->worker?->id }}"
                                 data-worker-name="{{ $table->currentAssignment?->worker?->name ?? 'Unassigned' }}">
                                <div class="table-number">{{ $table->table_number }}</div>
                                <div class="worker-name">{{ $table->currentAssignment?->worker?->name ?? 'Unassigned' }}</div>
                                @if($table->current_light_status === 'red')
                                    <span class="badge badge-danger mt-1">🔴</span>
                                @elseif($table->current_light_status === 'green')
                                    <span class="badge badge-success mt-1">🟢</span>
                                @elseif($table->current_light_status === 'blue')
                                    <span class="badge badge-info mt-1">🔵</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Production Panel --}}
        <div class="col-xl-4 col-lg-5 col-md-12 layout-spacing">
            <div class="production-panel">
                <div class="widget-content widget-content-area br-8">
                    <div id="noSelectionPanel">
                        <div class="text-center p-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mouse-pointer mb-3"><path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"></path><path d="M13 13l6 6"></path></svg>
                            <h5>Select a Table</h5>
                            <p class="text-muted">Click on a table from the grid to log production</p>
                        </div>
                    </div>

                    <div id="productionPanel" style="display: none;">
                        <div class="text-center mb-3">
                            <h4 id="selectedTableNumber">-</h4>
                            <p id="selectedWorkerName" class="text-muted">-</p>
                        </div>

                        <form id="quickProductionForm">
                            <input type="hidden" id="quickTableId">
                            <input type="hidden" id="quickWorkerId">

                            <div class="mb-3">
                                <label class="form-label">Garments Count <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-lg" id="quickGarmentsCount" min="1" placeholder="Enter quantity" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Product Type</label>
                                <select class="form-select" id="quickProductType">
                                    <option value="">Select type...</option>
                                    <option value="Shirt">Shirt</option>
                                    <option value="Pant">Pant</option>
                                    <option value="T-Shirt">T-Shirt</option>
                                    <option value="Jacket">Jacket</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check me-1"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Log Production
                            </button>
                        </form>

                        <hr>

                        {{-- Quick Light Control --}}
                        <h6 class="text-center">Set Light</h6>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-danger btn-sm quick-light-btn" data-color="red">🔴</button>
                            <button class="btn btn-success btn-sm quick-light-btn" data-color="green">🟢</button>
                            <button class="btn btn-info btn-sm quick-light-btn" data-color="blue">🔵</button>
                            <button class="btn btn-secondary btn-sm quick-light-btn" data-color="off">⚫</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let selectedTableId = null;
        let selectedWorkerId = null;

        // Table card click
        document.querySelectorAll('.table-select-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove selection from all
                document.querySelectorAll('.table-select-card').forEach(c => c.classList.remove('selected'));

                // Select this one
                this.classList.add('selected');

                // Get data
                selectedTableId = this.dataset.tableId;
                selectedWorkerId = this.dataset.workerId;

                // Update panel
                document.getElementById('noSelectionPanel').style.display = 'none';
                document.getElementById('productionPanel').style.display = 'block';
                document.getElementById('selectedTableNumber').textContent = this.dataset.tableNumber;
                document.getElementById('selectedWorkerName').textContent = this.dataset.workerName;
                document.getElementById('quickTableId').value = selectedTableId;
                document.getElementById('quickWorkerId').value = selectedWorkerId;

                // Focus on input
                document.getElementById('quickGarmentsCount').focus();
            });
        });

        // Form submit
        document.getElementById('quickProductionForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!selectedWorkerId || selectedWorkerId === 'undefined') {
                alert('No worker assigned to this table!');
                return;
            }

            const garmentsCount = document.getElementById('quickGarmentsCount').value;

            axios.post('{{ route("supervisor.production.store") }}', {
                table_id: selectedTableId,
                worker_id: selectedWorkerId,
                garments_count: garmentsCount,
                product_type: document.getElementById('quickProductType').value
            }, {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
                .then(response => {
                    if (response.data.success) {
                        alert('✅ Production logged: ' + garmentsCount + ' garments');
                        document.getElementById('quickGarmentsCount').value = '';
                        document.getElementById('quickProductType').value = '';
                    }
                })
                .catch(error => {
                    alert('Error: ' + (error.response?.data?.message || error.message));
                });
        });

        // Light buttons
        document.querySelectorAll('.quick-light-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!selectedTableId) return;

                axios.post('{{ route("supervisor.light.set") }}', {
                    table_id: selectedTableId,
                    light_color: this.dataset.color
                }, {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                })
                    .then(response => {
                        if (response.data.success) {
                            // Update card visual
                            const card = document.querySelector(`.table-select-card[data-table-id="${selectedTableId}"]`);
                            card.classList.remove('light-red', 'light-green', 'light-blue');
                            if (this.dataset.color !== 'off') {
                                card.classList.add('light-' + this.dataset.color);
                            }
                        }
                    });
            });
        });
    </script>
@endsection
