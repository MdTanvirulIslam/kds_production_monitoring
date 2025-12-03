@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('workers.index') }}">Workers</a></li>
    <li class="breadcrumb-item active" aria-current="page">All Workers</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="d-flex justify-content-between mb-4">
                    <h3 class="mb-0">👥 All Workers</h3>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('workers.create') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus me-1">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add New Worker
                        </a>
                    @endif
                </div>

                {{-- Filters --}}
                <form method="GET" action="{{ route('workers.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Search workers..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="skill_level" class="form-select">
                                <option value="">All Skills</option>
                                <option value="beginner" {{ request('skill_level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="intermediate" {{ request('skill_level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="expert" {{ request('skill_level') == 'expert' ? 'selected' : '' }}>Expert</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary w-100">Filter</button>
                        </div>
                    </div>
                </form>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Worker ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Skill Level</th>
                            <th>Current Table</th>
                            <th>Today's Production</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($workers as $worker)
                            <tr>
                                <td>
                                    <img src="{{ $worker->photo_url }}"
                                         alt="{{ $worker->name }}"
                                         class="rounded-circle"
                                         width="40" height="40"
                                         style="object-fit: cover;">
                                </td>
                                <td><strong>{{ $worker->worker_id }}</strong></td>
                                <td>{{ $worker->name }}</td>
                                <td>{{ $worker->phone ?? '-' }}</td>
                                <td>
                                    @php
                                        $skillBadge = match($worker->skill_level) {
                                            'expert' => 'badge-success',
                                            'intermediate' => 'badge-warning',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $skillBadge }}">{{ ucfirst($worker->skill_level) }}</span>
                                </td>
                                <td>{{ $worker->currentAssignment?->table?->table_number ?? '-' }}</td>
                                <td><span class="badge badge-primary">{{ $worker->getTodayProduction() }}</span></td>
                                <td>
                                    @if($worker->is_active)
                                        <span class="badge badge-light-success">Active</span>
                                    @else
                                        <span class="badge badge-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('workers.show', $worker) }}" class="btn btn-sm btn-outline-info" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </a>
                                    @if(auth()->user()->role === 'admin')
                                        <a href="{{ route('workers.edit', $worker) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No workers found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $workers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
