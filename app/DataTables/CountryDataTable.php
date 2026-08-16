<?php

/**
 * Country DataTable
 *
 * @package     LalaTaxi
 * @subpackage  DataTable
 * @category    Country
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\Country;
use Yajra\DataTables\Services\DataTable;
use DB;

class CountryDataTable extends DataTable
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
            ->addColumn('action', function ($country) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = '<a href="' . url('admin/edit_country/' . $country->id) . '" class="btn btn-success btn-sm" ' . $style . '><i class="bi bi-pencil-square"></i></a>&nbsp;';
                $delete = '<a data-href="' . url('admin/delete_country/' . $country->id) . '" class="btn btn-danger btn-sm" ' . $style . ' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>';

                return $edit . $delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param Country $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Country $model)
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
                ->setTableId('country-table')
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
            ['data' => 'id', 'name' => 'id', 'title' => __('messages.admin.manage_country.table.id')],
            ['data' => 'short_name', 'name' => 'short_name', 'title' => __('messages.admin.manage_country.table.short_name')],
            ['data' => 'long_name', 'name' => 'long_name', 'title' => __('messages.admin.manage_country.table.long_name')],
            ['data' => 'iso3', 'name' => 'iso3', 'title' => __('messages.admin.manage_country.table.iso3')],
            ['data' => 'num_code', 'name' => 'num_code', 'title' => __('messages.admin.manage_country.table.num_code')],
            ['data' => 'phone_code', 'name' => 'phone_code', 'title' => __('messages.admin.manage_country.table.phone_code')]
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'country_' . date('YmdHis');
    }
}
