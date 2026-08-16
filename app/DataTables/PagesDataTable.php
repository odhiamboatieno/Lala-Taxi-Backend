<?php

/**
 * Pages DataTable
 *
 * @package     LalaTaxi
 * @subpackage  DataTable
 * @category    Pages
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\Pages;
use Yajra\DataTables\Services\DataTable;
use DB;

class PagesDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $user = auth()->user();
        return datatables()
            ->of($query)
            ->addColumn('action', function ($page) use ($user) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="' . url('admin/edit_page/' . $page->id) . '" class="btn btn-success btn-sm" ' . $style . '><i class="bi bi-pencil-square"></i></a>&nbsp;';
                $delete = '<a data-href="' . url('admin/delete_page/' . $page->id) . '" class="btn btn-danger btn-sm" ' . $style . ' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>&nbsp;<a href="' . url($page->url) . '" target="_blank" class="btn btn-info btn-sm" ' . $style . '><i class="bi bi-eye"></i></a>';

                if ($user->can('edit_static_page') || $user->can('delete_static_page')) {
                    return $edit . '' . $delete;
                } else {
                    return '';
                }
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param Pages $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Pages $model)
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
                ->setTableId('pages-table')
                ->columns($this->getColumns())
                ->addAction(['exportable' => false])
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
            ['data' => 'id', 'name' => 'id', 'title' => __('messages.admin.manage_static_pages.table.id')],
            ['data' => 'name', 'name' => 'name', 'title' => __('messages.admin.manage_static_pages.table.name')],
            ['data' => 'url', 'name' => 'url', 'title' => __('messages.admin.manage_static_pages.table.url')],
            ['data' => 'status', 'name' => 'status', 'title' => __('messages.admin.manage_static_pages.table.status')],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => __('messages.admin.manage_static_pages.table.updated_at')]
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'pages_' . date('YmdHis');
    }
}
