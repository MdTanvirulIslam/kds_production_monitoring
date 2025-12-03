@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tables.index') }}">Tables</a></li>
    <li class="breadcrumb-item active" aria-current="page">All Tables</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="d-flex justify-content-between mb-4">
                    <h3 class="mb-0">📋 All Tables</h3>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('tables.create') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus me-1">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add New Table
                        </a>
                    @endif
                </div>

                {{-- Filters --}}
                <form method="GET" action="{{ route('tables.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search tables..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary w-100">Filter</button>
                        </div>
                    </div>
                </form>

                {{-- Success Message --}}
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
                            <th>Table #</th>
                            <th>Name</th>
                            <th>Current Worker</th>
                            <th>Light Status</th>
                            <th>Today's Production</th>
                            <th>ESP32 IP</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($tables as $table)
                            <tr>
                                <td><strong>{{ $table->table_number }}</strong></td>
                                <td>{{ $table->table_name }}</td>
                                <td>{{ $table->currentAssignment?->worker?->name ?? '-' }}</td>
                                <td>
                                    @if($table->current_light_status === 'red')
                                        <span class="badge badge-danger">🔴 Red</span>
                                    @elseif($table->current_light_status === 'green')
                                        <span class="badge badge-success">🟢 Green</span>
                                    @elseif($table->current_light_status === 'blue')
                                        <span class="badge badge-info">🔵 Blue</span>
                                    @else
                                        <span class="badge badge-secondary">⚫ Off</span>
                                    @endif
                                </td>
                                <td>{{ $table->getTodayProduction() }}</td>
                                <td>{{ $table->esp32_ip ?? '-' }}</td>
                                <td>
                                    @if($table->is_active)
                                        <span class="badge badge-light-success">Active</span>
                                    @else
                                        <span class="badge badge-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('tables.show', $table) }}" class="btn btn-sm btn-outline-info" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </a>
                                    <a href="{{ route('tables.qr-download', $table) }}" class="btn btn-sm btn-outline-secondary" title="Download QR">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    </a>
                                    @if(auth()->user()->role === 'admin')
                                        <a href="{{ route('tables.edit', $table) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No tables found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $tables->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
