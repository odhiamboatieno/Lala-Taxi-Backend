<?php

/**
 * Locations DataTable
 *
 * @package     LalaTaxi
 * @subpackage  DataTable
 * @category    Locations
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\AppVersion;
use Yajra\DataTables\Services\DataTable;
use DB;

class AppVersionDataTables extends DataTable
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
           ->addColumn('action', function ($version) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="'.url('admin/edit_app_version/'.$version->id).'" class="btn btn-success btn-sm" '.$style.'><i class="bi bi-pencil-square"></i></a>&nbsp;';

                $delete = '<a data-href="'.url('admin/delete_app_version/'.$version->id).'" class="btn btn-danger btn-sm" '.$style.' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>&nbsp;';

                return $edit.$delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param Location $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AppVersion $model)
    {
        $version = AppVersion::get();
        return $version;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                ->setTableId('app-version-table')
                ->columns($this->getColumns())
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
            ['data' => 'id', 'name' => 'id', 'title' =>  __('messages.admin.manage_mobile_app_version.table.id')],
            ['data' => 'version', 'name' => 'version', 'title' => __('messages.admin.manage_mobile_app_version.table.version')],
            ['data' => 'add_device_type', 'name' => 'add_device_type', 'title' => __('messages.admin.manage_mobile_app_version.table.device_type')],
            ['data' => 'add_user_type', 'name' => 'add_user_type', 'title' =>__('messages.admin.manage_mobile_app_version.table.user_type')],
            ['data' => 'add_force_update', 'name' => 'add_force_update', 'title' =>__('messages.admin.manage_mobile_app_version.table.force_update')],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => __('messages.admin.manage_mobile_app_version.table.created_at')],
            ['data' => 'updated_at', 'name' => 'updated_at', 'title' => __('messages.admin.manage_mobile_app_version.table.updated_at')],
            ['data' => 'action', 'name' => 'action', 'title' => __('messages.admin.manage_mobile_app_version.table.action'), 'orderable' => false, 'searchable' => false, 'exportable' => false],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'AppVersion_' . date('YmdHis');
    }
}
