<?php

/**
 * Company Payout Reports DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Company Payout Reports
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\Trips;
use Yajra\DataTables\Services\DataTable;
use DB;

class CompanyPayoutReportsDataTable extends DataTable
{
    protected $filter_type,$from,$to,$date;

    // Set the Type of Filter applied to Payout
    public function setFilter($filter_type)
    {
        $this->filter_type = $filter_type;
        return $this;
    }

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
            ->addColumn('day', function ($trips) {
                return date('l', strtotime($trips->created_at));
            })
            ->addColumn('company_payout', function ($trips) {
                $payment_pending_trips = Trips::CompanyPayoutTripsOnly()
                    ->select('trips.*','users.company_id as company_id','companies.name as company_name')
                    ->whereHas('driver',function($q) use ($trips){
                        $q->where('company_id',request()->company_id);
                    })
                    ->whereDate('trips.created_at', date('Y-m-d',strtotime($trips->created_at)));

                if($this->filter_type == 'day_report') {
                    $payment_pending_trips = $payment_pending_trips->where('trips.created_at', $trips->created_at);
                }
                $total_payout = $payment_pending_trips->get()->sum('driver_payout');
                return html_entity_decode(@$trips->currency->symbol).$total_payout;
            })
            ->addColumn('driver_name', function ($trips) {
                return @$trips->driver->first_name.' '.@$trips->driver->last_name;
            })
            ->addColumn('total_fare', function ($trips) {
                return currency_symbol().$trips->admin_total_amount;
            })
            ->addColumn('action', function ($trips) {
                $payout_credentials = $trips->driver->company->default_payout_credentials;
                $payout_text = (LOGIN_USER_TYPE == 'company') ? 'Paid' : 'Make Payout';
                
                $payout_data['has_payout_data'] = true;
                if($payout_credentials == '') {
                    $payout_data['has_payout_data'] = false;
                    $payout_data['payout_message'] = "Yet, Company does not enter his Payout details.";
                }
                else if($payout_credentials->type == 'BankTransfer') {
                    $payout_data['Payout Account Number'] = $payout_credentials->payout_id;
                    $payout_data['Account Holder Name'] = $payout_credentials->company_payout_preference->holder_name;
                    $payout_data['Bank Name'] = $payout_credentials->company_payout_preference->bank_name;
                    $payout_data['Bank Location'] = $payout_credentials->company_payout_preference->bank_location;
                    $payout_data['Bank Code'] = $payout_credentials->company_payout_preference->branch_code;
                }
                else if($payout_credentials->type == 'Stripe') {
                    $payout_data['Payout Account Number'] = $payout_credentials->payout_id;
                }
                else if($payout_credentials->type == 'Paypal') {
                    $payout_data['Paypal Email'] = $payout_credentials->payout_id;
                }

                $company_payout = '<a data-href="#" class="btn btn-info btn-sm" style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;" data-toggle="modal" data-target="#payout-details" data-payout_details=\''.json_encode($payout_data).'\'><i class="bi bi-list-ul"></i></a>';

                if($this->filter_type == 'day_report') {
                    $action_url = url('admin/view_trips/'.$trips->id).'?source=reports';
                    $payment_action = '<form action="'.url('admin/make_payout/company').'" method="post" name="payout_form" style="display:inline-block">
                        <input type="hidden" name="type" value="company_trip">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <input type="hidden" name="trip_id" value="'.$trips->id.'">
                        <input type="hidden" name="company_id" value="'.$trips->company_id.'">
                        <input type="hidden" name="redirect_url" value="admin/per_day_report/company/'.$trips->company_id.'/'.request()->date.'">
                        <button type="submit" class="btn btn-success btn-sm" style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;" name="submit" value="submit"> <i class="bi bi-check-circle"></i> </button>

                        </form>';
                }
                else {
                    $action_url = url('admin/per_day_report/company/'.$trips->company_id).'/'.date('Y-m-d',strtotime($trips->created_at));
                    $payment_action = '<form action="'.url('admin/make_payout/company').'" method="post" name="payout_form" style="display:inline-block">
                        <input type="hidden" name="type" value="company_day">
                        <input type="hidden" name="_token" value="'.csrf_token().'">
                        <input type="hidden" name="company_id" value="'.$trips->company_id.'">
                        <input type="hidden" name="day" value="'.date('Y-m-d',strtotime($trips->created_at)).'">
                        <input type="hidden" name="redirect_url" value="admin/per_week_report/company/'.$trips->company_id.'/'.request()->start_date.'/'.request()->end_date.'">
                        <button type="submit" class="btn btn-success btn-sm" style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;" name="submit" value="submit"> <i class="bi bi-check-circle"></i> </button>
                        
                        </form>';
                }

                if($payout_credentials == '') {
                    $payment_action = '<button type="button" class="btn btn-secondary btn-sm" style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;" disabled> <i class="bi bi-x-circle"></i> </button>';
                }

                return '<div>'.'<a href="'.$action_url.'" class="btn btn-info btn-sm" style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"><i class="bi bi-eye"></i></a> '.$company_payout.'&nbsp;'.$payment_action.'<div>';
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param Trips $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Trips $model)
    {
        $this->from = date('Y-m-d' . ' 00:00:00', strtotime(request()->start_date));
        $this->to = date('Y-m-d' . ' 23:59:59', strtotime(request()->end_date));
        $this->date = date('Y-m-d', strtotime(request()->date));

        $company_id = request()->company_id;

        $trips = $model->with(['currency','driver.company'])->CompanyPayoutTripsOnly()
        ->select('trips.*','users.company_id as company_id','companies.name as company_name')
        ->WhereHas('driver',function($q) use ( $company_id){
            $q->where('company_id',$company_id);
        });

        if($this->filter_type == 'day_report') {
            $trips->whereDate('trips.created_at', $this->date);
        }
        else {
            $trips->whereBetween('trips.created_at', [$this->from, $this->to])->groupBy(DB::raw('DATE(trips.created_at)'));
        }

        return $trips->get();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                ->setTableId('company-payouts-table')
                ->columns($this->getColumns())
                ->addAction()
                ->minifiedAjax()
                ->dom('lBfrtip')
                ->orderBy(0,'DESC')
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
        if($this->filter_type == 'day_report') {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => 'Trip Id'],
                ['data' => 'driver_name', 'name' => 'driver_name', 'title' => 'Driver Name'],
                ['data' => 'total_fare', 'name' => 'total_fare', 'title' => 'Total Fare'],
                ['data' => 'company_payout', 'name' => 'company_payout', 'title' => 'Payout Amount'],
                ['data' => 'payment_status', 'name' => 'payment_status', 'title' => 'Payment Status']
            ];
        }
        return [
            ['data' => 'day', 'name' => 'created_at', 'title' => 'Day'],
            ['data' => 'company_payout', 'name' => 'company_payout', 'title' => 'Payout Amount'],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'company_payouts_' . date('YmdHis');
    }
}
