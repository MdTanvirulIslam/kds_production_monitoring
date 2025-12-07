{{-- resources/views/assignments/show.blade.php --}}
@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Assignments</a></li>
    <li class="breadcrumb-item active" aria-current="page">View Assignment</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-10 col-md-12 mx-auto">
            <div class="widget-content widget-content-area br-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clipboard me-2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                        Assignment Details
                    </h5>
                    <div>
                        <a href="{{ route('assignments.edit', $assignment->id) }}" class="btn btn-primary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit me-1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            Edit
                        </a>
                    </div>
                </div>

                <div class="row">
                    {{-- Table Info --}}
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid me-2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                    Table Information
                                </h6>
                                <div class="mb-2">
                                <span class="badge badge-light-primary" style="font-size: 18px; padding: 10px 15px;">
                                    {{ $assignment->table->table_number }}
                                </span>
                                </div>
                                <p class="mb-1"><strong>Name:</strong> {{ $assignment->table->table_name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Location:</strong> {{ $assignment->table->location ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Hourly Target:</strong> {{ $assignment->table->hourly_target ?? 'N/A' }} pcs</p>
                            </div>
                        </div>
                    </div>

                    {{-- Worker Info --}}
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user me-2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    Worker Information
                                </h6>
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ $assignment->worker->photo ?? asset('assets/src/assets/img/profile-30.png') }}"
                                         class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;" alt="">
                                    <div>
                                        <h6 class="mb-0">{{ $assignment->worker->name }}</h6>
                                        <small class="text-muted">{{ $assignment->worker->worker_id }}</small>
                                    </div>
                                </div>
                                <p class="mb-1"><strong>Skill Level:</strong> {{ $assignment->worker->skill_level ?? 'General' }}</p>
                                <p class="mb-0"><strong>Department:</strong> {{ $assignment->worker->department ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Assignment Info --}}
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info me-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                    Assignment Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="text-muted small">Date</label>
                                        <p class="mb-0 fw-bold">{{ $assignment->assigned_date->format('M d, Y') }}</p>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="text-muted small">Shift Time</label>
                                        <p class="mb-0 fw-bold">
                                            @if($assignment->shift_start && $assignment->shift_end)
                                                {{ \Carbon\Carbon::parse($assignment->shift_start)->format('h:i A') }} - {{ \Carbon\Carbon::parse($assignment->shift_end)->format('h:i A') }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="text-muted small">Status</label>
                                        <p class="mb-0">
                                            @if($assignment->status == 'active')
                                                <span class="badge badge-light-success">Active</span>
                                            @elseif($assignment->status == 'completed')
                                                <span class="badge badge-light-primary">Completed</span>
                                            @else
                                                <span class="badge badge-light-danger">Cancelled</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="text-muted small">Created</label>
                                        <p class="mb-0 fw-bold">{{ $assignment->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                                @if($assignment->notes)
                                    <div class="mt-3">
                                        <label class="text-muted small">Notes</label>
                                        <p class="mb-0">{{ $assignment->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Back Button --}}
                <div class="mt-3">
                    <a href="{{ route('assignments.index', ['date' => $assignment->assigned_date->format('Y-m-d')]) }}" class="btn btn-light">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left me-1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                        Back to Assignments
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
