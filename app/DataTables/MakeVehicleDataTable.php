<?php
/**
 * Make Vehicle DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Make Vehicle
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */


namespace App\DataTables;

use App\Models\MakeVehicle;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use DB;


class MakeVehicleDataTable extends DataTable
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
            ->addColumn('action', function ($make_vehicle) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = (auth('admin')->user()->can('update_vehicle_make')) ? '<a href="'.url('admin/edit-vehicle-make/'.$make_vehicle->id).'" class="btn btn-success btn-sm" '.$style.'><i class="bi bi-pencil-square"></i></a>&nbsp;': '';
                $delete = (auth('admin')->user()->can('delete_vehicle_make')) ? '<a data-href="'.url('admin/delete-vehicle_make/'.$make_vehicle->id).'" class="btn btn-danger btn-sm" '.$style.' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>': '';

                return $edit.$delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\MakeVehicle $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(MakeVehicle $model)
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
                ->setTableId('vehicle-make-table')
                ->columns($this->getColumns())
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
            ['data' => 'id', 'name' => 'id', 'title' => __('messages.admin.manage_vehicle_make.table.id')],
            ['data' => 'make_vehicle_name', 'name' => 'make_vehicle_name', 'title' => __('messages.admin.manage_vehicle_make.table.make')],
            ['data' => 'status', 'name' => 'status', 'title' => __('messages.admin.manage_vehicle_make.table.status')],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'MakeVehicle_' . date('YmdHis');
    }
}
