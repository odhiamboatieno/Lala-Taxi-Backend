<?php

namespace App\DataTables;

use App\Models\ParcelFare;
use Yajra\DataTables\Services\DataTable;

class ParcelFareDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('category', function ($fare) {
                return $fare->parcelCategory ? e($fare->parcelCategory->title) : 'N/A';
            })
            ->addColumn('vehicle', function ($fare) {
                return $fare->carType ? e($fare->carType->car_name) : 'N/A';
            })
            ->addColumn('location', function ($fare) {
                return $fare->location ? e($fare->location->name) : 'Global';
            })
            ->addColumn('status', function ($fare) {
                $badge = $fare->is_active ? 'success' : 'danger';
                $label = $fare->is_active ? 'Active' : 'Inactive';
                return '<span class="badge bg-' . $badge . '">' . $label . '</span>';
            })
            ->addColumn('action', function ($fare) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="' . url('admin/edit_parcel_pricing/' . $fare->id) . '" class="btn btn-success btn-sm" ' . $style . '><i class="bi bi-pencil-square"></i></a>&nbsp;';
                $delete = '<a data-href="' . url('admin/delete_parcel_pricing/' . $fare->id) . '" class="btn btn-danger btn-sm" ' . $style . ' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>';
                return $edit . $delete;
            })
            ->rawColumns(['status', 'action']);
    }

    public function query(ParcelFare $model)
    {
        return $model->with(['parcelCategory', 'carType', 'location'])->select();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('parcel-fares-table')
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
            ['data' => 'category', 'name' => 'parcelCategory.title', 'title' => 'Category', 'orderable' => false],
            ['data' => 'vehicle', 'name' => 'carType.car_name', 'title' => 'Vehicle', 'orderable' => false],
            ['data' => 'location', 'name' => 'location.name', 'title' => 'Location', 'orderable' => false],
            ['data' => 'base_fare', 'name' => 'base_fare', 'title' => 'Base Fare'],
            ['data' => 'per_km_rate', 'name' => 'per_km_rate', 'title' => 'Per KM Rate'],
            ['data' => 'min_fare', 'name' => 'min_fare', 'title' => 'Min Fare'],
            ['data' => 'status', 'name' => 'is_active', 'title' => 'Status', 'orderable' => false, 'searchable' => false],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
        ];
    }
}
