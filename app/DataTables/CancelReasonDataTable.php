<?php

/**
 * Cancel Reason DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Cancel Reason
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\CancelReason;
use Yajra\DataTables\Services\DataTable;
use DB;
use Auth;

class CancelReasonDataTable extends DataTable
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
            ->addColumn('action', function ($cancel_reason) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '';
                if(Auth::guard('admin')->user()->can('update_manage_reason'))
                    $edit = '<a href="'.url('admin/edit-cancel-reason/'.$cancel_reason->id).'" class="btn btn-success btn-sm" '.$style.'><i class="bi bi-pencil-square"></i></a>&nbsp;';

                return $edit;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param CancelReason $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(CancelReason $model)
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
                ->setTableId('cancel-reason-table')
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
            ['data' => 'id', 'name' => 'id', 'title' =>  __('messages.admin.cancel_reasons.table.id') ],
            ['data' => 'reason', 'name' => 'reason', 'title' =>  __('messages.admin.cancel_reasons.table.reason') ],
            ['data' => 'cancelled_by', 'name' => 'cancelled_by', 'title' =>  __('messages.admin.cancel_reasons.table.cancelled_by') ],
            ['data' => 'status', 'name' => 'status', 'title' =>  __('messages.admin.cancel_reasons.table.status') ],
        ];
    }


    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'cancel_reasons_' . date('YmdHis');
    }
}
