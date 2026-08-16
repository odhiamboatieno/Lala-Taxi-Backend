<?php

/**
 * Metas DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Metas
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\Metas;
use Yajra\DataTables\Services\DataTable;
use DB;

class MetasDataTable extends DataTable
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
            ->addColumn('action', function ($meta) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="' . url('admin/edit_meta/' . $meta->id) . '" class="btn btn-success btn-sm" ' . $style . '><i class="bi bi-pencil-square"></i></a>';

                return $edit;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param Metas $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Metas $model)
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
                ->setTableId('metas-table')
                ->columns($this->getColumns())
                ->addAction()
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
            ['data' => 'id', 'name' => 'id', 'title' => __('messages.admin.manage_metas.table.id')],
            ['data' => 'url', 'name' => 'url', 'title' => __('messages.admin.manage_metas.table.url')],
            ['data' => 'title', 'name' => 'title', 'title' => __('messages.admin.manage_metas.table.title')],
            ['data' => 'description', 'name' => 'description', 'title' => __('messages.admin.manage_metas.table.description')],
            ['data' => 'keywords', 'name' => 'keywords', 'title' => __('messages.admin.manage_metas.table.keywords')],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'metas_' . date('YmdHis');
    }
}
