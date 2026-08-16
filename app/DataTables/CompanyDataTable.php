<?php

/**
 * Company DataTable
 *
 * @package     LalaTaxi
 * @subpackage  DataTable
 * @category    Company
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\Company;
use Yajra\DataTables\Services\DataTable;
use DB;

class CompanyDataTable extends DataTable
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
            ->eloquent($query)
            ->addColumn('email', function ($companies) {
                return protectedString($companies->email);
            })
            ->addColumn('drivers', function($companies) {
                return '<p class="company_driver_list">'.$companies->driver_id.'</p>';
            })
            ->addColumn('action', function ($companies) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = (auth('admin')->user()->can('update_company')) ? '<a href="'.url('admin/edit_company/'.$companies->id).'" class="btn btn-success btn-sm" '.$style.'><i class="bi bi-pencil-square"></i></a>&nbsp;' : '';
                $delete = (auth('admin')->user()->can('delete_company')) ? '<a data-href="'.url('admin/delete_company/'.$companies->id).'" class="btn btn-danger btn-sm" '.$style.' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>':'';

                $action = $edit;
                if($companies->id != 1) {
                    $action .= $delete;
                }
                
                return $action;

            })
            ->rawColumns(['drivers','action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Company $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Company $model)
    {
        $companies = Company::select('companies.id', 'companies.name','companies.email','companies.country_code','companies.mobile_number', 'companies.status',DB::raw('group_concat(users.id) as driver_id'));
        return $companies->leftJoin('users', function($join) {
                            $join->on('users.company_id', '=', 'companies.id');
                        })->groupBy('companies.id');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                ->setTableId('companies-table')
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
            ['data' => 'id', 'name' => 'companies.id', 'title' => __('messages.admin.manage_company_page.table.id') ],
            ['data' => 'name', 'name' => 'companies.name', 'title' => __('messages.admin.manage_company_page.table.name') ],
            ['data' => 'drivers', 'name' => 'drivers', 'title' => __('messages.admin.manage_company_page.table.drivers'),'width' => "10%"],
            ['data' => 'email', 'name' => 'companies.email', 'title' => __('messages.admin.manage_company_page.table.email')],
            ['data' => 'mobile_number', 'name' => 'companies.mobile_number', 'title' => 'Mobile Number'],
            ['data' => 'status', 'name' => 'companies.status', 'title' => __('messages.admin.manage_company_page.table.status')],
            ['data' => 'action', 'name' => 'action', 'title' => __('messages.admin.manage_company_page.table.action'), 'orderable' => false, 'searchable' => false, 'exportable' => false],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'companies_' . date('YmdHis');
    }
}
