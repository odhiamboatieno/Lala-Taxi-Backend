<?php

namespace App\DataTables;

use App\Models\ParcelTask;
use Yajra\DataTables\Services\DataTable;

class ParcelTaskDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('icon', function ($task) {
                if ($task->icon_path) {
                    return '<img src="' . asset($task->icon_path) . '" width="40" height="40" alt="' . e($task->title) . '">';
                }
                return '<span class="text-muted">No icon</span>';
            })
            ->addColumn('status', function ($task) {
                $badge = $task->is_active ? 'success' : 'danger';
                $label = $task->is_active ? 'Active' : 'Inactive';
                return '<span class="badge bg-' . $badge . '">' . $label . '</span>';
            })
            ->addColumn('action', function ($task) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="' . url('admin/edit_parcel_task/' . $task->id) . '" class="btn btn-success btn-sm" ' . $style . '><i class="bi bi-pencil-square"></i></a>&nbsp;';
                $delete = '<a data-href="' . url('admin/delete_parcel_task/' . $task->id) . '" class="btn btn-danger btn-sm" ' . $style . ' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>&nbsp;';
                $toggle = '<a href="' . url('admin/toggle_parcel_task_status/' . $task->id) . '" class="btn btn-warning btn-sm" ' . $style . '><i class="bi bi-arrow-repeat"></i></a>';
                return $edit . $delete . $toggle;
            })
            ->rawColumns(['icon', 'status', 'action']);
    }

    public function query(ParcelTask $model)
    {
        return $model->select();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('parcel-tasks-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('lBfrtip')
            ->orderBy(0)
            ->buttons(['csv', 'excel', 'print', 'reset'])
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'language' => [
                    'paginate' => [
                        'previous' => '<i class="ri-arrow-left-s-line fw-semibold"></i>',
                        'next' => '<i class="ri-arrow-right-s-line fw-semibold"></i>'
                    ]
                ],
                'drawCallback' => 'function(settings) {
                    $(".pagination").addClass("pagination-primary mb-0");
                    $(".paginate_button").addClass("page-item");
                    $(".paginate_button a").addClass("page-link");
                }'
            ]);
    }

    protected function getColumns()
    {
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'ID'],
            ['data' => 'icon', 'name' => 'icon_path', 'title' => 'Icon', 'orderable' => false, 'searchable' => false],
            ['data' => 'key', 'name' => 'key', 'title' => 'Key'],
            ['data' => 'title', 'name' => 'title', 'title' => 'Title'],
            ['data' => 'status', 'name' => 'is_active', 'title' => 'Status', 'orderable' => false, 'searchable' => false],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
        ];
    }
}
