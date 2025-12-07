{{-- resources/views/assignments/index.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Assignments</li>
@endsection

@section('styles')
    <link href="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/src/plugins/css/light/sweetalerts2/custom-sweetalert.css') }}" rel="stylesheet">
    <style>
        .shift-tab {
            padding: 10px 20px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .shift-tab:hover { border-color: #667eea; background: #f8f9ff; }
        .shift-tab.active { border-color: #667eea; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .shift-tab .shift-icon { font-size: 16px; }
        .shift-tab .shift-count {
            font-size: 11px;
            background: rgba(0,0,0,0.1);
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 4px;
        }
        .shift-tab.active .shift-count { background: rgba(255,255,255,0.25); }

        .assignment-card {
            border: 1px solid #e0e6ed;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
            transition: all 0.2s;
            background: #fff;
        }
        .assignment-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transform: translateY(-2px);
            border-color: #d0d6dd;
        }
        .assignment-card .table-badge {
            font-size: 14px;
            font-weight: 700;
            padding: 8px 14px;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .assignment-card .worker-info { display: flex; align-items: center; }
        .assignment-card .worker-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
            border: 2px solid #e0e6ed;
        }
        .assignment-card .worker-name { font-weight: 600; font-size: 15px; color: #333; }
        .assignment-card .worker-id { font-size: 12px; color: #888; }
        .assignment-card .shift-badge {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 15px;
            background: #e8f4fc;
            color: #1b55e2;
        }

        /* Improved Action Buttons */
        .action-btns {
            display: flex;
            gap: 6px;
        }
        .btn-action {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid #e0e6ed;
            background: #fff;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-action svg { width: 14px; height: 14px; }
        .btn-action.btn-edit { color: #667eea; }
        .btn-action.btn-edit:hover { background: #667eea; border-color: #667eea; color: #fff; }
        .btn-action.btn-delete { color: #e74c3c; }
        .btn-action.btn-delete:hover { background: #e74c3c; border-color: #e74c3c; color: #fff; }

        .stats-card {
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: transform 0.2s;
        }
        .stats-card:hover { transform: translateY(-2px); }
        .stats-card .stats-value { font-size: 2rem; font-weight: 700; }
        .stats-card .stats-label { font-size: 0.75rem; color: rgba(255,255,255,0.85); text-transform: uppercase; letter-spacing: 0.5px; }

        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state svg { width: 80px; height: 80px; color: #d3d3d3; margin-bottom: 20px; }

        .light-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-left: 8px;
            box-shadow: 0 0 6px currentColor;
        }
        .light-red { background: #e7515a; color: #e7515a; }
        .light-green { background: #1abc9c; color: #1abc9c; }
        .light-blue { background: #1b55e2; color: #1b55e2; }
        .light-yellow { background: #e2a03f; color: #e2a03f; }
        .light-off { background: #888; color: #888; box-shadow: none; }

        .time-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 6px;
            background: #f0f2f5;
            color: #555;
        }
        .time-badge svg { width: 12px; height: 12px; }

        .table-name-small {
            font-size: 0.75rem;
            color: #888;
            background: #f8f9fa;
            padding: 2px 8px;
            border-radius: 4px;
        }
    </style>
@endsection

@section('content')
    <div class="row layout-top-spacing">

        {{-- Header --}}
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h4 class="mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clipboard me-2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                            Assignments
                        </h4>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </span>
                            <input type="date" id="assignmentDate" class="form-control" value="{{ $date ?? date('Y-m-d') }}">
                            <button class="btn btn-primary" id="loadDataBtn" title="Refresh">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('assignments.bulk') }}?date={{ $date }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers me-1"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                            Bulk Assign
                        </a>
                        <a href="{{ route('assignments.create') }}?date={{ $date }}" class="btn btn-outline-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus me-1"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Add
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 layout-spacing">
            <div class="widget-content widget-content-area stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                <div class="stats-value">{{ $stats['total_tables'] ?? 0 }}</div>
                <div class="stats-label">Total Tables</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 layout-spacing">
            <div class="widget-content widget-content-area stats-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff;">
                <div class="stats-value">{{ $stats['assigned_tables'] ?? 0 }}</div>
                <div class="stats-label">Assigned</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 layout-spacing">
            <div class="widget-content widget-content-area stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff;">
                <div class="stats-value">{{ $stats['unassigned_tables'] ?? 0 }}</div>
                <div class="stats-label">Unassigned</div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 layout-spacing">
            <div class="widget-content widget-content-area stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff;">
                <div class="stats-value">{{ $assignments->total() ?? 0 }}</div>
                <div class="stats-label">Total Assignments</div>
            </div>
        </div>

        {{-- Shift Filter Tabs --}}
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="d-flex flex-wrap gap-2">
                    <div class="shift-tab active" data-shift="all">
                        <span class="shift-icon">📋</span>
                        <span>All Shifts</span>
                        <span class="shift-count" id="countAll">{{ $assignments->total() }}</span>
                    </div>
                    @foreach($shifts ?? [] as $shift)
                        <div class="shift-tab" data-shift="{{ $shift->id }}">
                            <span class="shift-icon">
                                @if(strtotime($shift->start_time) < strtotime('12:00'))
                                    🌅
                                @elseif(strtotime($shift->start_time) < strtotime('18:00'))
                                    ☀️
                                @else
                                    🌙
                                @endif
                            </span>
                            <span>{{ $shift->name }}</span>
                            <span class="shift-count" id="count{{ $shift->id }}">
                                {{ $assignments->where('shift_id', $shift->id)->count() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Assignments List --}}
        <div class="col-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="row" id="assignmentsList">
                    @forelse($assignments as $assignment)
                        <div class="col-xl-4 col-lg-6 col-md-6 assignment-item" data-shift="{{ $assignment->shift_id }}">
                            <div class="assignment-card">
                                {{-- Header Row --}}
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <span class="table-badge">{{ $assignment->table->table_number }}</span>
                                        @if($assignment->table->current_light_status)
                                            <span class="light-indicator light-{{ $assignment->table->current_light_status }}" title="{{ ucfirst($assignment->table->current_light_status) }} Light"></span>
                                        @endif
                                    </div>
                                    <div class="action-btns">
                                        <a href="{{ route('assignments.edit', $assignment->id) }}" class="btn-action btn-edit" title="Edit Assignment">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                        </a>
                                        <button type="button" class="btn-action btn-delete" onclick="deleteAssignment({{ $assignment->id }})" title="Remove Assignment">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Worker Info --}}
                                <div class="worker-info mb-3">
                                    <img src="{{ $assignment->worker->photo ?? asset('assets/src/assets/img/profile-30.png') }}" class="worker-avatar" alt="{{ $assignment->worker->name }}">
                                    <div>
                                        <div class="worker-name">{{ $assignment->worker->name }}</div>
                                        <div class="worker-id">#{{ $assignment->worker->worker_id }}</div>
                                    </div>
                                </div>

                                {{-- Footer Row --}}
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($assignment->shift_start && $assignment->shift_end)
                                            <span class="time-badge">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                                {{ \Carbon\Carbon::parse($assignment->shift_start)->format('h:i A') }} - {{ \Carbon\Carbon::parse($assignment->shift_end)->format('h:i A') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="table-name-small">{{ $assignment->table->table_name ?? 'Workstation' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clipboard"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                                <h5>No Assignments for {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</h5>
                                <p class="text-muted">Start by adding assignments or use bulk assignment</p>
                                <a href="{{ route('assignments.bulk') }}?date={{ $date }}" class="btn btn-primary mt-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers me-1"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                                    Go to Bulk Assignment
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if($assignments->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted small">
                            Showing {{ $assignments->firstItem() }} to {{ $assignments->lastItem() }} of {{ $assignments->total() }} assignments
                        </div>
                        {{ $assignments->appends(['date' => $date])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/src/plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const baseUrl = '{{ url("/") }}';

        // Load Data for Date
        document.getElementById('loadDataBtn').addEventListener('click', function() {
            const date = document.getElementById('assignmentDate').value;
            window.location.href = `{{ route('assignments.index') }}?date=${date}`;
        });

        // Date input change
        document.getElementById('assignmentDate').addEventListener('change', function() {
            window.location.href = `{{ route('assignments.index') }}?date=${this.value}`;
        });

        // Shift Tab Filter
        document.querySelectorAll('.shift-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active state
                document.querySelectorAll('.shift-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const shiftId = this.dataset.shift;

                // Filter assignments
                document.querySelectorAll('.assignment-item').forEach(item => {
                    if (shiftId === 'all' || item.dataset.shift === shiftId) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Delete Assignment
        function deleteAssignment(id) {
            Swal.fire({
                title: 'Remove Assignment?',
                text: 'This will unassign the worker from this table.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#888',
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`${baseUrl}/assignments/${id}`, {
                        headers: { 'X-CSRF-TOKEN': csrfToken }
                    })
                        .then(response => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed!',
                                text: 'Assignment has been removed.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        })
                        .catch(error => {
                            Swal.fire('Error', error.response?.data?.message || 'Failed to remove assignment', 'error');
                        });
                }
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Press 'B' for bulk assign
            if (e.key === 'b' && !e.ctrlKey && !e.metaKey && document.activeElement.tagName !== 'INPUT') {
                window.location.href = `{{ route('assignments.bulk') }}?date={{ $date }}`;
            }
            // Press 'N' for new assignment
            if (e.key === 'n' && !e.ctrlKey && !e.metaKey && document.activeElement.tagName !== 'INPUT') {
                window.location.href = `{{ route('assignments.create') }}?date={{ $date }}`;
            }
        });
    </script>
@endsection
