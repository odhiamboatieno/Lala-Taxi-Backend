<?php

/**
 * Documents DataTable
 *
 * @package     Witaxi
 * @subpackage  DataTable
 * @category    Documents
 * @author      Source Monster Team
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\DataTables;

use App\Models\Documents;
use Yajra\DataTables\Services\DataTable;
use DB;

class DocumentsDataTable extends DataTable
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
            ->addColumn('document_for', function ($documents) {
                return $documents->document_for;
            })
            ->addColumn('action', function ($documents) {
                $style = 'style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $edit = (auth('admin')->user()->can('update_documents')) ? '<a href="'.url('admin/edit_document/'.$documents->id).'" class="btn btn-success btn-sm" '.$style.'><i class="bi bi-pencil-square"></i></a>&nbsp;' : '' ;
                $delete = (auth('admin')->user()->can('delete_documents')) ? '<a data-href="'.url('admin/delete_document/'.$documents->id).'" class="btn btn-danger btn-sm" '.$style.' data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>' : '';

                return $edit.$delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param Documents $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Documents $model)
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
                ->setTableId('documents-table')
                ->columns($this->getColumns())
                ->addAction()
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
            ['data' => 'id' , 'title' =>  __('messages.admin.manage_documents_page.table.id')],
            ['data' => 'type' , 'title' =>  __('messages.admin.manage_documents_page.table.document_for')],
            ['data' => 'document_for' , 'title' =>  __('messages.admin.manage_documents_page.table.country')],
            ['data' => 'document_name' , 'title' => __('messages.admin.manage_documents_page.table.document_name')],
            ['data' => 'status' , 'title' =>  __('messages.admin.manage_documents_page.table.status')],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'documents_' . date('YmdHis');
    }
}
