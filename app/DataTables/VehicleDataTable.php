<?php

/**
 * Vehicle DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Vehicle
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\Vehicle;
use Yajra\DataTables\Services\DataTable;
use DB;

class VehicleDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('action', function ($vehicle) {
                 $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                 $edit = '<a href="'.url(LOGIN_USER_TYPE.'/edit_vehicle/'.$vehicle->id).'" class="btn btn-success btn-sm" '.$style.'><i class="bi bi-pencil-square"></i></a>' ;
                $delete = '<a data-href="'.url(LOGIN_USER_TYPE.'/delete_vehicle/'.$vehicle->id).'" class="btn btn-danger btn-sm" '.$style.' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>';

                return $edit.'&nbsp'.$delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param Vehicle $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Vehicle $model)
    {
        $vehicles = $model->join('users', function ($join) {
                $join->on('users.id', '=', 'vehicle.user_id');
            })
            ->leftJoin('companies', function ($join) {
                $join->on('companies.id', '=', 'vehicle.company_id');
            })
            ->select('vehicle.id as id','vehicle.status as status','vehicle.vehicle_name as vehicle_name','vehicle.vehicle_number as vehicle_number','vehicle.vehicle_type', 'users.first_name as driver_name','companies.name as company_name');

        //If login user is company then get that company vehicles only
        if (LOGIN_USER_TYPE=='company') {
            $vehicles = $vehicles->where('vehicle.company_id',auth()->guard('company')->user()->id);
        }
        return $vehicles;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                ->setTableId('vehicles-table')
                ->columns($this->getColumns())
                ->minifiedAjax()
                ->dom('lBfrtip')
                ->orderBy(0)
                ->buttons(
                    ['csv', 'excel', 'print', 'reset']
                )
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

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            ['data' => 'id', 'name' => 'vehicle.id', 'title' => __('messages.admin.manage_vehicles.table.id')],
            ['data' => 'company_name', 'name' => 'companies.name', 'title' => __('messages.admin.manage_vehicles.table.company_name')],
            ['data' => 'driver_name', 'name' => 'users.first_name', 'title' => __('messages.admin.manage_vehicles.table.driver_name')],
            ['data' => 'vehicle_type', 'name' => 'vehicle.vehicle_type', 'title' => __('messages.admin.manage_vehicles.table.vehicle_type')],
            ['data' => 'vehicle_name', 'name' => 'vehicle.vehicle_name', 'title' => __('messages.admin.manage_vehicles.table.make')],
            ['data' => 'vehicle_number', 'name' => 'vehicle.vehicle_number', 'title' => __('messages.admin.manage_vehicles.table.vehicle_number')],
            ['data' => 'status', 'name' => 'vehicle.status', 'title' => __('messages.admin.manage_vehicles.table.status')],
            ['data' => 'action', 'name' => 'action', 'title' => __('messages.admin.manage_vehicles.table.action'), 'orderable' => false, 'searchable' => false, 'exportable' => false],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'vehicles_' . date('YmdHis');
    }
}
