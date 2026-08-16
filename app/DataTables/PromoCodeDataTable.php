<?php

/**
 * Promo Code DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Promo Code
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\PromoCode;
use Yajra\DataTables\Services\DataTable;
use DB;

class PromoCodeDataTable extends DataTable
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
            ->addColumn('action', function ($promo_code) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="' . url('admin/edit_promo_code/' . $promo_code->id) . '" class="btn btn-success btn-sm" ' . $style . '><i class="bi bi-pencil-square"></i></a>&nbsp;';
                $delete = '<a data-href="' . url('admin/delete_promo_code/' . $promo_code->id) . '" class="btn btn-danger btn-sm" ' . $style . ' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>';

                return $edit . $delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param PromoCode $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(PromoCode $model)
    {
        return $model->all();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                ->setTableId('promo-code-table')
                ->columns($this->getColumns())
                ->minifiedAjax()
                ->dom('lBfrtip')
                ->orderBy(0, 'DESC')
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
            ['data' => 'id', 'name' => 'id', 'title' => __('messages.admin.manage_wallet_and_promo.table.id')],
            ['data' => 'code', 'name' => 'code', 'title' => __('messages.admin.manage_wallet_and_promo.table.code')],
            ['data' => 'original_amount', 'name' => 'original_amount', 'title' => __('messages.admin.manage_wallet_and_promo.table.original_amount')],
            ['data' => 'currency_code', 'name' => 'currency_code', 'title' => __('messages.admin.manage_wallet_and_promo.table.currency_code')],
            ['data' => 'expire_date', 'name' => 'expire_date', 'title' => __('messages.admin.manage_wallet_and_promo.table.expire_date')],
            ['data' => 'status', 'name' => 'status', 'title' => __('messages.admin.manage_wallet_and_promo.table.status')],
            ['data' => 'action', 'name' => 'action', 'title' => __('messages.admin.manage_wallet_and_promo.table.action')],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'promo_code_' . date('YmdHis');
    }
}
