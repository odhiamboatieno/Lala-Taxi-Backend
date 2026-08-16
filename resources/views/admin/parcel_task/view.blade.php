@extends('admin.template')

@section('title-sub', 'Parcel Tasks')
@section('pagetitle', 'Parcel Tasks')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1">Parcel Tasks</h4>
                <div class="text-muted">Manage delivery task types like pickup, dropoff, or return.</div>
            </div>
            <a class="btn btn-primary" href="{{ url('admin/add_parcel_task') }}">Add Task</a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('admin/parcel_tasks') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search task key or title">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/parcel_tasks') }}" class="btn btn-light">Reset</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Key</th>
                            <th>Title</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td>{{ $task->key }}</td>
                                <td>{{ $task->title }}</td>
                                <td>{{ $task->sort_order }}</td>
                                <td><span class="badge {{ $task->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $task->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="text-end d-flex justify-content-end gap-2">
                                    <a href="{{ url('admin/edit_parcel_task/'.$task->id) }}" class="btn btn-sm btn-outline-success">Edit</a>
                                    <a href="{{ url('admin/toggle_parcel_task_status/'.$task->id) }}" class="btn btn-sm btn-outline-primary">Toggle</a>
                                    <a href="{{ url('admin/delete_parcel_task/'.$task->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this parcel task?')">Delete</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No parcel tasks found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tasks->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $tasks->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
