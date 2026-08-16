<?php

/**
 * Cancel Trips DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Cancel Trips
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\Cancel;
use Yajra\DataTables\Services\DataTable;
use DB;

class CancelTripsDataTable extends DataTable
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
            ->of($query);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Cancel $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Cancel $model)
    {
        $cancel = $model->where(function($query)  {
                            if(LOGIN_USER_TYPE=='company') {
                                $query->whereHas('trip.driver',function($q1) {
                                    $q1->where('company_id',auth('company')->id());
                                });
                            }
                        })
                        ->join('trips', function($join) {
                            $join->on('trips.id', '=', 'cancel.trip_id');
                        })
                        ->join('users', function($join) {
                            $join->on('users.id', '=', 'trips.user_id');
                        })
                        ->join('cancel_reasons', function($join) {
                            $join->on('cancel_reasons.id', '=', 'cancel.cancel_reason_id');
                        })
                        ->leftJoin('users as u', function($join) {
                            $join->on('u.id', '=', 'trips.driver_id');
                        })
                        ->leftJoin('companies', function($join) {
                            $join->on('u.company_id', '=', 'companies.id');
                        })
                        ->select(['cancel.id as id', 'cancel.created_at', 'u.first_name as driver_name', 'users.first_name as rider_name','cancel_reasons.reason as cancel_reason', 'cancel.cancel_comments as cancel_comments ','cancel.cancelled_by as cancelled_by', 'cancel.created_at as cancel_created_at','cancel.*','trips.pickup_location as pickup_location','trips.drop_location as drop_location','trips.created_at as trips_created_at','companies.name as company_name']);
        return $cancel;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                ->setTableId('cancel-trips-table')
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
        $columns = [
            ['data' => 'id', 'name' => 'cancel.id', 'title' => __('messages.admin.manage_trip.table.id')],
            ['data' => 'trips_created_at', 'name' => 'trips.created_at', 'title' => __('messages.admin.manage_trip.table.trip_date')],
        ];

        if(LOGIN_USER_TYPE == 'admin') {
            $columns[] = ['data' => 'company_name', 'name' => 'companies.name', 'title' => __('messages.admin.manage_trip.table.company_name')];
        }

        return array_merge($columns,[
            ['data' => 'driver_name', 'name' => 'u.first_name', 'title' => __('messages.admin.manage_trip.table.driver_name')],
            ['data' => 'rider_name', 'name' => 'users.first_name', 'title' => __('messages.admin.manage_trip.table.rider_name')],
            ['data' => 'pickup_location', 'name' => 'trips.pickup_location', 'title' => __('messages.admin.manage_trip.table.pickup_location')],
            ['data' => 'drop_location', 'name' => 'trips.drop_location', 'title' => __('messages.admin.manage_trip.table.drop_location')],
            ['data' => 'cancel_reason', 'name' => 'cancel_reasons.reason', 'title' =>  __('messages.admin.manage_trip.table.reason')],
            ['data' => 'cancel_comments', 'name' => 'cancel.cancel_comments', 'title' =>  __('messages.admin.manage_trip.table.comments')],
            ['data' => 'cancelled_by', 'name' => 'cancel.cancelled_by', 'title' =>  __('messages.admin.manage_trip.table.cancelled_by')],
            ['data' => 'cancel_created_at', 'name' => 'cancel.created_at', 'title' =>  __('messages.admin.manage_trip.table.cancel_at')],
        ]);
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'cancel_trips_' . date('YmdHis');
    }
}
