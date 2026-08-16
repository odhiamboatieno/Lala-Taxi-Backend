<?php

namespace App\DataTables;

use App\Models\ParcelPackageSize;
use Yajra\DataTables\Services\DataTable;

class ParcelPackageSizeDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('status', function ($size) {
                $badge = $size->is_active ? 'success' : 'danger';
                $label = $size->is_active ? 'Active' : 'Inactive';
                return '<span class="badge bg-' . $badge . '">' . $label . '</span>';
            })
            ->addColumn('action', function ($size) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="' . url('admin/edit_parcel_package_size/' . $size->id) . '" class="btn btn-success btn-sm" ' . $style . '><i class="bi bi-pencil-square"></i></a>&nbsp;';
                $delete = '<a data-href="' . url('admin/delete_parcel_package_size/' . $size->id) . '" class="btn btn-danger btn-sm" ' . $style . ' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>&nbsp;';
                $toggle = '<a href="' . url('admin/toggle_parcel_package_size_status/' . $size->id) . '" class="btn btn-warning btn-sm" ' . $style . '><i class="bi bi-arrow-repeat"></i></a>';
                return $edit . $delete . $toggle;
            })
            ->rawColumns(['status', 'action']);
    }

    public function query(ParcelPackageSize $model)
    {
        return $model->select();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('parcel-package-sizes-table')
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
            ['data' => 'key', 'name' => 'key', 'title' => 'Key'],
            ['data' => 'title', 'name' => 'title', 'title' => 'Title'],
            ['data' => 'subtitle', 'name' => 'subtitle', 'title' => 'Subtitle'],
            ['data' => 'price_multiplier', 'name' => 'price_multiplier', 'title' => 'Multiplier'],
            ['data' => 'status', 'name' => 'is_active', 'title' => 'Status', 'orderable' => false, 'searchable' => false],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
        ];
    }
}
