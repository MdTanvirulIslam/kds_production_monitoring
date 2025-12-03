@extends('layouts.layout')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Assignments</a></li>
    <li class="breadcrumb-item active" aria-current="page">All Assignments</li>
@endsection

@section('content')
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="d-flex justify-content-between mb-4">
                    <h3 class="mb-0">🔗 Table Assignments</h3>
                    <a href="{{ route('assignments.create', ['date' => $date]) }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus me-1">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        New Assignment
                    </a>
                </div>

                {{-- Date Filter --}}
                <form method="GET" action="{{ route('assignments.index') }}" class="mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <input type="date" name="date" class="form-control" value="{{ $date }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary">View Date</button>
                        </div>
                        <div class="col-md-7 text-end">
                            <span class="badge badge-light-primary">Total: {{ $stats['total_tables'] }}</span>
                            <span class="badge badge-light-success">Assigned: {{ $stats['assigned_tables'] }}</span>
                            <span class="badge badge-light-warning">Unassigned: {{ $stats['unassigned_tables'] }}</span>
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
                            <th>Table</th>
                            <th>Worker</th>
                            <th>Shift</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($assignments as $assignment)
                            <tr>
                                <td><strong>{{ $assignment->table->table_number }}</strong></td>
                                <td>{{ $assignment->worker->name }}</td>
                                <td>
                                    @if($assignment->shift_start && $assignment->shift_end)
                                        {{ $assignment->shift_start }} - {{ $assignment->shift_end }}
                                    @else
                                        Full Day
                                    @endif
                                </td>
                                <td>
                                    @if($assignment->status === 'active')
                                        <span class="badge badge-light-success">Active</span>
                                    @elseif($assignment->status === 'completed')
                                        <span class="badge badge-light-primary">Completed</span>
                                    @else
                                        <span class="badge badge-light-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="{{ route('assignments.destroy', $assignment) }}" class="d-inline" onsubmit="return confirm('Delete this assignment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No assignments for this date</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $assignments->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
