<?php

/**
 * VehicleModel DataTable
 *
 * @package     LalaTaxi
 * @subpackage  DataTable
 * @category    VehicleModel
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\VehicleModel;
use Yajra\DataTables\Services\DataTable;
use DB;

class VehicleModelDataTable extends DataTable
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
            ->addColumn('vehicle_make_id', function ($vehicle_model) {
                return $vehicle_model->vehicle_make->make_name;
            })
            ->addColumn('action', function ($vehicle_model) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = (auth('admin')->user()->can('update_vehicle_model')) ?  '<a href="'.url('admin/edit-vehicle_model/'.$vehicle_model->id).'" class="btn btn-success btn-sm" '.$style.'><i class="bi bi-pencil-square"></i></a>&nbsp;' : '';
                $delete = (auth('admin')->user()->can('delete_vehicle_model')) ?  '<a data-href="'.url('admin/delete_vehicle_model/'.$vehicle_model->id).'" class="btn btn-danger btn-sm" '.$style.' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>': '';

                return $edit.$delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param VehicleModel $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(VehicleModel $model)
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
                ->setTableId('vehicle-model-table')
                ->columns($this->getColumns())
                ->addAction()
                ->minifiedAjax()
                ->dom('lBfrtip')
                ->orderBy(0)
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
            ['data' => 'id' , 'title' => __('messages.admin.manage_vehicle_model.table.id')],
            ['data' => 'vehicle_make_id' , 'title' => __('messages.admin.manage_vehicle_model.table.make')],
            ['data' => 'model_name' , 'title' => __('messages.admin.manage_vehicle_model.table.model')],
            ['data' => 'status' , 'title' => __('messages.admin.manage_vehicle_model.table.status')],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'VehicleModel_' . date('YmdHis');
    }
}
