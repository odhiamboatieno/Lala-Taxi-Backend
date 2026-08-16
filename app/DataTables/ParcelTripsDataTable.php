<?php

namespace App\DataTables;

use App\Models\Trips;
use Yajra\DataTables\Services\DataTable;

class ParcelTripsDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('category', function ($trip) {
                return $trip->parcelCategory ? e($trip->parcelCategory->title) : ($trip->parcelRequest ? e($trip->parcelRequest->category) : 'N/A');
            })
            ->addColumn('recipient', function ($trip) {
                return $trip->parcelRequest ? e($trip->parcelRequest->recipient_name) : 'N/A';
            })
            ->addColumn('driver', function ($trip) {
                return $trip->driver ? e($trip->driver->first_name . ' ' . $trip->driver->last_name) : 'Unassigned';
            })
            ->addColumn('pod_status', function ($trip) {
                if ($trip->parcel_pod_signature || $trip->parcel_pod_photo) {
                    return '<span class="badge bg-success">Collected</span>';
                }
                if ($trip->status === 'Completed') {
                    return '<span class="badge bg-warning">Pending</span>';
                }
                return '<span class="badge bg-secondary">N/A</span>';
            })
            ->addColumn('status', function ($trip) {
                $colors = [
                    'Pending' => 'warning',
                    'In Transit' => 'info',
                    'Completed' => 'success',
                    'Cancelled' => 'danger',
                ];
                $color = $colors[$trip->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . e($trip->status) . '</span>';
            })
            ->addColumn('action', function ($trip) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $view = '<a href="' . url('admin/view_parcel_trip/' . $trip->id) . '" class="btn btn-info btn-sm" ' . $style . '><i class="bi bi-eye"></i></a>&nbsp;';
                if (!in_array($trip->status, ['Completed', 'Cancelled'])) {
                    $cancel = '<a href="' . url('admin/cancel_parcel_trip/' . $trip->id) . '" class="btn btn-danger btn-sm" ' . $style . '><i class="bi bi-x-circle"></i></a>';
                    return $view . $cancel;
                }
                return $view;
            })
            ->rawColumns(['pod_status', 'status', 'action']);
    }

    public function query(Trips $model)
    {
        return $model->with(['driver', 'parcelCategory', 'parcelRequest'])
            ->where('service_category', 'parcel')
            ->select();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('parcel-trips-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('lBfrtip')
            ->orderBy(0, 'desc')
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
            ['data' => 'id', 'name' => 'id', 'title' => 'Trip ID'],
            ['data' => 'category', 'name' => 'parcelCategory.title', 'title' => 'Category', 'orderable' => false],
            ['data' => 'recipient', 'name' => 'parcelRequest.recipient_name', 'title' => 'Recipient', 'orderable' => false],
            ['data' => 'driver', 'name' => 'driver.first_name', 'title' => 'Driver', 'orderable' => false],
            ['data' => 'fare', 'name' => 'fare', 'title' => 'Fare'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            ['data' => 'pod_status', 'name' => 'parcel_pod_signature', 'title' => 'POD', 'orderable' => false, 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Date'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
        ];
    }
}
