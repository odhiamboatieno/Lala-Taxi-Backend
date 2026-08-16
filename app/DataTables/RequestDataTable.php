<?php

/**
 * Rider DataTable
 *
 * @package     LalaTaxi
 * @subpackage  DataTable
 * @category    Rider
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\Request as RideRequest;
use Yajra\DataTables\Services\DataTable;
use DB;

class RequestDataTable extends DataTable
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
        ->addColumn('date_time',function($get_request) {
            $now = new \DateTime;
            $ago = new \DateTime($get_request->updated_at);
            $diff = $now->diff($ago);

            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;

            $string = ['y' => 'year','m' => 'month','w' => 'week','d' => 'day','h' => 'hour','i' => 'minute','s' => 'second'];
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }
            $string = array_slice($string, 0, 1);

            return $string ? implode(', ', $string) . ' ago' : 'just now';
        })
        ->addColumn('request_status', function ($get_request) {
            $request_status=DB::table('request')->where('group_id',$get_request->group_id)->where('status','Accepted');
            $pending_request_status=DB::table('request')->where('group_id',$get_request->group_id)->where('status','Pending');
            if($request_status->count()) {
                $req_id=$request_status->first()->id;
                $trip_status=@DB::table('trips')->where('request_id',$req_id)->first()->status;
                return $trip_status;
            }
            elseif($pending_request_status->count()) {
                return "Searching";
            }
            else {
                return "No one accepted";
            }
        })
        ->addColumn('payment_status', function ($get_request) {
            return ($get_request->payment_status != null ) ? $get_request->payment_status : "Not Paid";
        })
        ->addColumn('total_amount', function ($get_request) {
            return ($get_request->total_fare!= null ) ? html_entity_decode($get_request->currency_symbol)." ".$get_request->total_fare : "N/A";
        })
        ->addColumn('action', function ($get_request) {
            $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
            return '<a href="'.url(LOGIN_USER_TYPE.'/detail_request/'.$get_request->id).'" class="btn btn-info btn-sm" '.$style.'><i class="bi bi-eye"></i></a>&nbsp;';
        });
    }

    /**
     * Get query source of request dataTable .
     *
     * @param Request $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(RideRequest $model)
    {
        $get_request = DB::Table('request')
        ->where(function($query)  {
            if(LOGIN_USER_TYPE=='company') {
                $query->join('users as drivers', function($join) {
                    $join->on('drivers.id', '=', 'request.driver_id')
                    ->where('drivers.company_id',auth('company')->id());
                });
            }
        })
        ->leftJoin('trips', function($join) {
            $join->on('trips.request_id', '=', 'request.id');
        })
        ->leftJoin('currency', function($join) {
            $join->on('currency.code', '=', 'trips.currency_code');
        })
        ->join('users', function($join) {
            $join->on('users.id', '=', 'request.user_id');
        })
        ->join('car_type', function($join) {
            $join->on('car_type.id', '=', 'request.car_id');
        })                        
        ->groupBy('group_id')
        ->select(['request.id as id', 'users.first_name as first_name',DB::raw('CONCAT(currency.symbol, trips.total_fare) AS total_amount'),'request.group_id','request.payment_mode as payment_mode','trips.payment_status','request.updated_at','trips.total_fare','currency.symbol AS currency_symbol']);

        if(LOGIN_USER_TYPE=='company') {
            $get_request = $get_request
            ->join('users as drivers', function($join) {
                $join->on('drivers.id', '=', 'request.driver_id')
                ->where('drivers.company_id',auth('company')->id());
            });
        }
        return $get_request;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                ->setTableId('ride-requests-table')
                ->columns($this->getColumns())
                ->addAction()
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
            ['data' => 'id', 'name' => 'id', 'title' =>  __('messages.admin.manage_trip.table.request_id')],
            ['data' => 'first_name', 'name' => 'users.first_name', 'title' => __('messages.admin.manage_trip.table.rider_name')],
            ['data' => 'date_time', 'name' => 'date_time', 'title' =>  __('messages.admin.manage_trip.table.date_and_time') ,'orderable' => false, 'searchable' => false],
            ['data' => 'request_status', 'name' => 'request_status', 'title' =>  __('messages.admin.manage_trip.table.status'),'orderable' => false, 'searchable' => false],
        ];
        if(LOGIN_USER_TYPE != 'company') {
            $columns[] = ['data' => 'total_amount', 'name' => 'trips.total_fare', 'title' =>  __('messages.admin.manage_trip.table.amount')];
        }
        return array_merge($columns,[
            ['data' => 'payment_mode', 'name' => 'payment_mode', 'title' =>  __('messages.admin.manage_trip.table.payment_mode')],
            ['data' => 'payment_status', 'name' => 'payment_status', 'title' =>  __('messages.admin.manage_trip.table.payment_status')],
        ]);
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'requests_' . date('YmdHis');
    }
}
