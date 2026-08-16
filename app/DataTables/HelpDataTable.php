<?php

/**
 * Help DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Help
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\Help;
use Yajra\DataTables\Services\DataTable;
use DB;

class HelpDataTable extends DataTable
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
            ->addColumn('action', function ($help) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="'.url('admin/edit_help/'.$help->help_id).'" class="btn btn-success btn-sm" '.$style.'><i class="bi bi-pencil-square"></i></a>&nbsp;';
                $delete = '<a data-href="'.url('admin/delete_help/'.$help->help_id).'" class="btn btn-danger btn-sm" '.$style.' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>';

                return $edit.$delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param Help $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Help $model)
    {
        $help = $model->join('help_category', function($join) {
                                $join->on('help_category.id', '=', 'help.category_id');
                            })
                    ->leftJoin('help_subcategory', function($join) {
                                $join->on('help_subcategory.id', '=', 'help.subcategory_id');
                            })
                    ->select(['help_category.*', 'help_subcategory.*','help.id as help_id','help.category_id as category_id','help.subcategory_id as subcategory_id','question',DB::raw("concat(substring(answer, 1, 50),'...') as answer"),'help.status as help_status'])
                    ->where('help_category.status','Active');
        return $help;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                ->setTableId('help-table')
                ->columns($this->getColumns())
                ->addAction(["printable" => false])
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
            ['data' => 'help_id', 'name' => 'help.id', 'title' => __('messages.admin.manage_help.table.id')],
            ['data' => 'category_name', 'name' => 'help_category.name', 'title' => __('messages.admin.manage_help.table.category')],
            ['data' => 'subcategory_name', 'name' => 'help_subcategory.name', 'title' => __('messages.admin.manage_help.table.subcategory')],
            ['data' => 'question', 'name' => 'help.question', 'title' => __('messages.admin.manage_help.table.question')],
            ['data' => 'help_status', 'name' => 'help.status', 'title' => __('messages.admin.manage_help.table.status')],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'help_' . date('YmdHis');
    }
}
