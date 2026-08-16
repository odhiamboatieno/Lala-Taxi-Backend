<?php

/**
 * Car Type DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Car Type
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace Modules\ManualBooking\DataTables;

use App\Models\ScheduleRide;
use Yajra\DataTables\Services\DataTable;
use DB;

class  LaterBookingDataTable extends DataTable
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
            ->editColumn('driver_name', function($schedule_rides) {
                $button = '';
                if ($schedule_rides->status=='Pending' && $schedule_rides->booking_type=='Manual Booking') {
                    $button = '<br><a href="'.url(LOGIN_USER_TYPE.'/manual_booking/'.$schedule_rides->id).'" class="btn btn-primary edit_'.$schedule_rides->id.'"><i class="fa fa-edit"></i></a>';
                }

                if ($schedule_rides->status=='Completed' && $schedule_rides->driver_name==null && $schedule_rides->trip_id != null) {
                    $driver_name = $schedule_rides->trip_driver_name;
                }else{
                    $driver_name = ($schedule_rides->driver_name==null?'Auto Assign':$schedule_rides->driver_name);
                }
                return $driver_name.' '.$button;
            })
            ->editColumn('status', function($schedule_rides) {
                if ($schedule_rides->status=='Pending' && $schedule_rides->booking_type=='Manual Booking') {
                    $status = '<span class="cancel_'.$schedule_rides->id.'">'.$schedule_rides->status.'</span>';
                    if (!empty($schedule_rides->assignment_status)) {
                        $status .= '<br><small class="text-muted">Assignment: '.e(ucwords(str_replace('_', ' ', $schedule_rides->assignment_status))).'</small>';
                    }
                    if (!empty($schedule_rides->driver_note)) {
                        $status .= '<br><small class="text-muted">'.e($schedule_rides->driver_note).'</small>';
                    }
                    $status .= '<br><span data-toggle="modal" data-target="#cancel_popup" class="btn btn-primary cancel_button" schedule_id="'.$schedule_rides->id.'">'. __('messages.admin.manage_manual_booking_page.cancel').'</span>';
                }elseif($schedule_rides->status=='Cancelled'){
                    if ($schedule_rides->schedule_cancel==null) {
                        $status = $schedule_rides->status;
                    }else{
                        $status =  __('messages.admin.manage_manual_booking_page.cancelled_by').' '.$schedule_rides->schedule_cancel->cancel_by.'<br><span data-toggle="modal" data-target="#cancel_reason_popup" class="btn btn-primary cancel_button" schedule_id="'.$schedule_rides->id.'" cancel_by="'.$schedule_rides->schedule_cancel->cancel_by.'" reason="'.$schedule_rides->schedule_cancel->cancel_reason.'" cancel_reason="'.$schedule_rides->schedule_cancel->cancel_reasons->reason.'">'. __('messages.admin.manage_manual_booking_page.cancel_reason').'</a>';
                    }
                }elseif($schedule_rides->status=='Car Not Found'){
                    if($schedule_rides->schedule_date <= date('Y-m-d')){
                        logger("testing".$schedule_rides->schedule_time." current time ".date("H:i")." schedule date ".$schedule_rides->schedule_date);
                        if($schedule_rides->schedule_date <=  date('Y-m-d')){
                            if($schedule_rides->schedule_time <= date("H:i") ) {
                                $status = '';
                            }
                            else
                               $status =  '<span class="immediate_request_'.$schedule_rides->id.'">'.$schedule_rides->status.'</span><br><span class="immediate_request btn btn-primary" schedule_id="'.$schedule_rides->id.'">'.__('messages.admin.manage_manual_booking_page.immediate_request').'</span>';     
                        }
                        else
                        {
                            $status = '<span class="immediate_request_'.$schedule_rides->id.'">'.$schedule_rides->status.'</span><br><span class="immediate_request btn btn-primary" schedule_id="'.$schedule_rides->id.'">'.__('messages.admin.manage_manual_booking_page.immediate_request').'</span>';
                        }
                    } 
                   else
                   {
                        $status = '<span class="immediate_request_'.$schedule_rides->id.'">'.$schedule_rides->status.'</span><br><span class="immediate_request btn btn-primary" schedule_id="'.$schedule_rides->id.'">'.__('messages.admin.manage_manual_booking_page.immediate_request').'</span>';
                   }
                }elseif($schedule_rides->status=='Completed' && $schedule_rides->booking_type=='Manual Booking' && $schedule_rides->trip_id != null && $schedule_rides->trip_status == 'Cancelled'){
                    $status =  __('messages.admin.manage_manual_booking_page.trip_cancelled');
                }elseif($schedule_rides->status=='Completed' && $schedule_rides->trip_id != null ){
                    $status = $schedule_rides->trip_status;
                }else{
                    $status = $schedule_rides->status;
                }
                return $status;
            })
            ->addColumn('date_time', function ($schedule_rides) {
                return date("Y-m-d H:i a",strtotime($schedule_rides->schedule_date.' '.$schedule_rides->schedule_time));
            })
            ->addColumn('company_name', function ($schedule_rides) {
                return $schedule_rides->company_name != '' ? $schedule_rides->company_name : '-';
            })
            ->addColumn('trip_details', function ($schedule_rides) {
                if ($schedule_rides->trip_id==null) {
                    return '---';
                }
                return '<a href="'.url(LOGIN_USER_TYPE.'/view_trips/'.$schedule_rides->trip_id).'" class="btn btn-primary"><i class="fa fa-eye"></i></a>';
            })
            ->addColumn('action', function ($car_type) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="'.url('admin/edit_car_type/'.$car_type->id).'" class="btn btn-success btn-sm" '.$style.'><i class="bi bi-pencil-square"></i></a>&nbsp;';
                $delete = '<a data-href="'.url('admin/delete_car_type/'.$car_type->id).'" class="btn btn-danger btn-sm" '.$style.' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>';

                return $edit.$delete;
            })
            ->rawColumns(['driver_name','status', 'trip_details','action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param ScheduleRide $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ScheduleRide $model)
    {
        $queue = request('queue', 'all');

        $schedule_rides = $model->where('schedule_ride.location_id','!=',0)
                        ->join('users as rider', function($join) {
                            $join->on('rider.id', '=', 'schedule_ride.user_id');
                        })
                        ->leftJoin('users as driver', function($join) {
                            $join->on('driver.id', '=', 'schedule_ride.driver_id');
                        })
                        ->leftJoin('companies', function($join) {
                            $join->on('driver.company_id', '=', 'companies.id');
                        })
                        ->join('car_type', function($join) {
                            $join->on('car_type.id', '=', 'schedule_ride.car_id');
                        })
                        ->leftJoin('request as ride_request', function($join) {
                            $join->on('ride_request.schedule_id', '=', 'schedule_ride.id')
                            ->whereIn('ride_request.id', function($query) {
                                $query->selectRaw('MAX(request.id) from request,schedule_ride where schedule_ride.id = request.schedule_id')->groupBy('request.schedule_id');
                            });
                        })
                        ->leftJoin('trips', function($join) {
                            $join->on('ride_request.id', '=', 'trips.request_id');
                        })
                        ->leftJoin('users as trip_driver', function($join) {
                            $join->on('trips.driver_id', '=', 'trip_driver.id');
                        })
                        ->with('schedule_cancel.cancel_reasons')
                        ->select([
                            'schedule_ride.*',
                            'rider.first_name as rider_name',
                            'driver.first_name as driver_name',
                            DB::raw('DATE_FORMAT(CONCAT(schedule_ride.schedule_date," ", schedule_ride.schedule_time), "%d %M %Y %H:%i") as date_time'),
                            'companies.name as company_name',
                            'trips.id as trip_id',
                            'trips.status as trip_status',
                            'trip_driver.first_name as trip_driver_name',
                        ])
                        ->where(function($query)  {
                            if(LOGIN_USER_TYPE=='company') {
                                $query->where('schedule_ride.company_id',auth('company')->user()->id)
                                ->orWhereHas('driver',function($q1){
                                    $q1->where('driver.company_id',auth('company')->user()->id);
                                });
                            }
                        })
                        ->distinct();

        if ($queue === 'asap') {
            $schedule_rides->where('schedule_ride.booking_mode', 'asap');
        }
        elseif ($queue === 'scheduled') {
            $schedule_rides->where('schedule_ride.booking_mode', 'scheduled');
        }
        elseif ($queue === 'assigned') {
            $schedule_rides->whereIn('schedule_ride.assignment_status', ['assigned', 'accepted']);
        }
        elseif ($queue === 'dispatch_due') {
            $schedule_rides->where('schedule_ride.status', 'Pending')
                ->whereNotNull('schedule_ride.dispatch_at')
                ->whereBetween('schedule_ride.dispatch_at', [now()->subMinutes(5), now()->addMinutes(30)]);
        }
        elseif ($queue === 'completed') {
            $schedule_rides->where('schedule_ride.status', 'Completed');
        }
        elseif ($queue === 'cancelled') {
            $schedule_rides->where('schedule_ride.status', 'Cancelled');
        }
        elseif ($queue === 'no_show') {
            $schedule_rides->where('schedule_ride.status', 'No Show');
        }

        return $schedule_rides;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                ->setTableId('later-booking-table')
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
        $company_columns = [];
        if(LOGIN_USER_TYPE == 'admin') {
            $company_columns = [
                ['data' => 'company_name', 'name' => 'companies.name', 'title' => __('messages.admin.manage_manual_booking_page.table.company_name')]
            ];
        }
        $columns = [
            ['data' => 'id', 'name' => 'schedule_ride.id', 'title' => 'Id'],
            ['data' => 'booking_type', 'name' => 'schedule_ride.booking_type', 'title' => __('messages.admin.manage_manual_booking_page.table.booked_by')],
            ['data' => 'date_time', 'name' => 'schedule_ride.schedule_date', 'title' => __('messages.admin.manage_manual_booking_page.table.date')],
            ['data' => 'driver_name', 'name' => 'driver.first_name', 'title' => __('messages.admin.manage_manual_booking_page.table.driver_name')],
        ];
        $other_columns = [
            ['data' => 'rider_name', 'name' => 'rider.first_name', 'title' =>__('messages.admin.manage_manual_booking_page.table.rider_name')],
            ['data' => 'pickup_location', 'name' => 'schedule_ride.pickup_location', 'title' => __('messages.admin.manage_manual_booking_page.table.pickup_location')],
            ['data' => 'drop_location', 'name' => 'schedule_ride.drop_location', 'title' => __('messages.admin.manage_manual_booking_page.table.drop_location')],
            ['data' => 'trip_details', 'name' => 'schedule_ride.id', 'title' => __('messages.admin.manage_manual_booking_page.table.trip_details')],
            ['data' => 'status', 'name' => 'schedule_ride.status', 'title' => __('messages.admin.manage_manual_booking_page.table.status')],
        ];
        return array_merge($columns,$company_columns,$other_columns);
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'vehicles_type_' . date('YmdHis');
    }
}
