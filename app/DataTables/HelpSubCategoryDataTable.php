<?php

/**
 * Help Sub Category DataTable
 *
 * @package     LalaTaxi
 * @subpackage  DataTable
 * @category    Help Sub Category
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\HelpSubCategory;
use Yajra\DataTables\Services\DataTable;
use DB;

class HelpSubCategoryDataTable extends DataTable
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
            ->addColumn('action', function ($help_subcategory) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="'.url('admin/edit_help_subcategory/'.$help_subcategory->subcategory_id).'" class="btn btn-success btn-sm" '.$style.'><i class="bi bi-pencil-square"></i></a>&nbsp;';
                $delete = '<a data-href="'.url('admin/delete_help_subcategory/'.$help_subcategory->subcategory_id).'" class="btn btn-danger btn-sm" '.$style.' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>';

                return $edit.$delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param HelpSubCategory $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(HelpSubCategory $model)
    {
        $help_subcategory = $model->join('help_category', function($join) {
                                $join->on('help_category.id', '=', 'help_subcategory.category_id');
                            })
                            ->select(['help_subcategory.id as subcategory_id', 'help_subcategory.name as subcategory_name', 'help_subcategory.description as subcategory_description', 'help_subcategory.status as subcategory_status', 'help_subcategory.category_id', 'help_category.*']);
        return $help_subcategory;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                ->setTableId('help-subcategory-table')
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
            ['data' => 'subcategory_id', 'name' => 'help_subcategory.id', 'title' => __('messages.admin.manage_help.table.id')],
            ['data' => 'category_name', 'name' => 'help_category.name', 'title' => __('messages.admin.manage_help.table.category')],
            ['data' => 'subcategory_name', 'name' => 'help_subcategory.name', 'title' => __('messages.admin.manage_help.table.name')],
            ['data' => 'subcategory_description', 'name' => 'help_subcategory.description', 'title' => __('messages.admin.manage_help.table.description')],
            ['data' => 'subcategory_status', 'name' => 'help_subcategory.status', 'title' => __('messages.admin.manage_help.table.status')],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename():string
    {
        return 'help_sub_category_' . date('YmdHis');
    }
}
