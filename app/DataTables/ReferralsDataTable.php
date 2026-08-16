<?php

/**
 * Referrals Users DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Referrals Users
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\ReferralUser;
use Yajra\DataTables\Services\DataTable;
use DB;

class ReferralsDataTable extends DataTable
{
    protected $user_type;

    // Set the value for User Type 
    public function setUserType($user_type){
        $this->user_type = $user_type;
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
            ->addColumn('rider_name', function ($referral_user) {
                return $referral_user->user->first_name;
            })
            ->addColumn('referrer_name', function ($referral_user) {
                return $referral_user->referred_user_name;
            })
            ->addColumn('earned_amount', function ($referral_user) {
                return $referral_user->where('user_id',$referral_user->user_id)->where('payment_status','Completed')->where('amount','>',0)->sum('amount');
            })
            ->addColumn('pending_amount', function ($referral_user) {
                return $referral_user->where('user_id',$referral_user->user_id)->where('payment_status','Pending')->where('pending_amount','>',0)->sum('pending_amount');
            })
            ->addColumn('action', function ($referral_user) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $detail = '<a href="'.url('admin/referrals/'.$referral_user->user_id).'" class="btn btn-info btn-sm" '.$style.'><i class="bi bi-eye"></i></a>';

                return $detail;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param ReferralUser $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ReferralUser $model)
    {
        $referrals = $model->with('user','referral_user')->where('user_type',$this->user_type)->where('payment_status','!=','Expired')->groupBy('user_id')->get();
        return $referrals;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                ->setTableId('referrals-table')
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
        return [
            ['data' => 'user.id', 'name' => 'user.id', 'title' =>  __('messages.admin.referrals.table.id') ],
            ['data' => 'rider_name', 'name' => 'rider_name', 'title' => $this->user_type.' '.__('messages.admin.referrals.table.name')],
            ['data' => 'referrer_name', 'name' => 'referrer_name', 'title' => __('messages.admin.referrals.table.referral_name')],
            ['data' => 'currency_code', 'name' => 'currency_code', 'title' => __('messages.admin.referrals.table.currency_code')],
            ['data' => 'earned_amount', 'name' => 'amount', 'title' => __('messages.admin.referrals.table.earned_amount')],
            ['data' => 'pending_amount', 'name' => 'amount', 'title' => __('messages.admin.referrals.table.pending_amount')],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'referrals_' . date('YmdHis');
    }
}
