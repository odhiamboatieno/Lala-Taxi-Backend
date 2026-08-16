<?php

/**
 * Role DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Role
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;
use App\Models\Role;

class RoleDataTable extends DataTable
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
            ->addColumn('action', function ($role) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="'.url('admin/edit_role/'.$role->id).'" class="btn btn-success btn-sm" '.$style.'><i class="bi bi-pencil-square"></i></a>&nbsp;';
                $delete = '<a data-href="'.url('admin/delete_role/'.$role->id).'" class="btn btn-danger btn-sm" '.$style.' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>';

                return $edit.$delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param Role $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Role $model)
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
                ->setTableId('roles-table')
                ->columns($this->getColumns())
                ->minifiedAjax()
                ->dom('lBfrtip')
                ->orderBy(0,'ASC')
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
            ['data' => 'id', 'name' => 'id', 'title' => __('messages.admin.manage_admin_page.table.id')],
            ['data' => 'name', 'name' => 'name', 'title' =>__('messages.admin.manage_admin_page.table.name')],
            ['data' => 'display_name', 'name' => 'display_name', 'title' => __('messages.admin.manage_admin_page.table.display_name')],
            ['data' => 'description', 'name' => 'description', 'title' =>  __('messages.admin.manage_admin_page.table.description')],
            ['data' => 'action', 'name' => 'action', 'title' =>  __('messages.admin.manage_admin_page.table.action'), 'orderable' => false, 'searchable' => false, 'exportable' => false],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'roles_' . date('YmdHis');
    }
}
