<?php

namespace App\DataTables;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Services\DataTable;

class RiderDataTable extends DataTable
{
    protected bool $canEdit = false;
    protected bool $canDelete = false;

    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('customer_profile', function ($row) {
                $avatar = $row->avatar ?: url('images/user.jpeg');
                $name = trim($row->first_name.' '.$row->last_name);

                return '<div class="d-flex align-items-center gap-2">
                    <img src="'.e($avatar).'" alt="'.e($name).'" class="rounded-circle border" style="width:40px;height:40px;object-fit:cover;">
                    <div>
                        <div class="fw-semibold">'.e($name).'</div>
                        <small class="text-muted">#'.e($row->id).'</small>
                    </div>
                </div>';
            })
            ->addColumn('contact_info', function ($row) {
                return '<div>
                    <div>'.e(($row->country_code ? '+'.$row->country_code.' ' : '').$row->mobile_number).'</div>
                    <small class="text-muted">'.e(protectedString($row->email)).'</small>
                </div>';
            })
            ->editColumn('profile_status', fn ($row) => '<span class="badge bg-warning-subtle text-warning">'.$row->profile_status.'%</span>')
            ->editColumn('level_name', fn ($row) => '<span class="badge bg-info-subtle text-info">'.e($row->level_name ?: 'Unassigned').'</span>')
            ->editColumn('total_trips', fn ($row) => number_format((int) $row->total_trips))
            ->editColumn('status', function ($row) {
                $class = $row->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                return '<span class="badge '.$class.'">'.e($row->status).'</span>';
            })
            ->addColumn('action', function ($row) {
                $style = 'style="width:34px;height:34px;padding:0;display:inline-flex;align-items:center;justify-content:center;"';
                $actions = '<a href="'.url('admin/edit_rider/'.$row->id).'" class="btn btn-info btn-sm" '.$style.' title="View profile"><i class="ri-eye-line"></i></a> ';

                if ($this->canEdit) {
                    $actions .= '<a href="'.url('admin/edit_rider/'.$row->id).'" class="btn btn-success btn-sm" '.$style.' title="Edit rider"><i class="ri-pencil-line"></i></a> ';
                }

                if ($this->canDelete) {
                    $actions .= '<a data-href="'.url('admin/delete_rider/'.$row->id).'" class="btn btn-danger btn-sm" '.$style.' data-toggle="modal" data-target="#confirm-delete" title="Delete rider"><i class="ri-delete-bin-line"></i></a>';
                }

                return $actions;
            })
            ->rawColumns(['customer_profile', 'contact_info', 'profile_status', 'level_name', 'status', 'action']);
    }

    public function query()
    {
        $this->canEdit = auth('admin')->user()->can('update_rider');
        $this->canDelete = auth('admin')->user()->can('delete_rider');
        $hasLevels = Schema::hasTable('user_level_assignments') && Schema::hasTable('user_levels');
        $levelNameSelect = 'NULL as level_name';

        if ($hasLevels) {
            $levelNameSelect = "(
                SELECT ul.name
                FROM user_level_assignments ula
                INNER JOIN user_levels ul ON ul.id = ula.user_level_id
                WHERE ula.user_id = users.id
                  AND ula.level_type = 'rider'
                  AND ula.company_id IS NULL
                ORDER BY ula.id DESC
                LIMIT 1
            ) as level_name";
        }

        $query = DB::table('users')
            ->leftJoin('profile_picture as profile_pictures', 'profile_pictures.user_id', '=', 'users.id')
            ->where('users.user_type', 'Rider');

        if (request('status_scope') === 'active') {
            $query->where('users.status', 'Active');
        } elseif (request('status_scope') === 'inactive') {
            $query->where('users.status', 'Inactive');
        }

        if (request()->filled('level_id') && $hasLevels) {
            $query->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('user_level_assignments as ula')
                    ->whereColumn('ula.user_id', 'users.id')
                    ->where('ula.level_type', 'rider')
                    ->whereNull('ula.company_id')
                    ->where('ula.user_level_id', (int) request('level_id'));
            });
        }

        return $query->select(
            'users.id',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.country_code',
            'users.mobile_number',
            'users.status',
            'profile_pictures.src as avatar',
            DB::raw($levelNameSelect),
            DB::raw('(SELECT COUNT(*) FROM trips WHERE trips.user_id = users.id) as total_trips'),
            DB::raw('ROUND((((CASE WHEN COALESCE(users.first_name, "") != "" THEN 1 ELSE 0 END) + (CASE WHEN COALESCE(users.last_name, "") != "" THEN 1 ELSE 0 END) + (CASE WHEN COALESCE(users.email, "") != "" THEN 1 ELSE 0 END) + (CASE WHEN COALESCE(users.mobile_number, "") != "" THEN 1 ELSE 0 END) + (CASE WHEN COALESCE(users.country_id, "") != "" THEN 1 ELSE 0 END) + (CASE WHEN COALESCE(profile_pictures.src, "") != "" THEN 1 ELSE 0 END)) / 6) * 100, 0) as profile_status')
        );
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('riders-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'function(data){ data.status_scope = $("#rider-status-scope").val(); data.level_id = $("#rider-level-filter").val(); }')
            ->dom('rt<"d-flex justify-content-between align-items-center mt-3"ip>')
            ->orderBy(0)
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'pageLength' => 10,
            ]);
    }

    protected function getColumns()
    {
        return [
            ['data' => 'id', 'name' => 'users.id', 'title' => 'SL'],
            ['data' => 'customer_profile', 'name' => 'users.first_name', 'title' => 'Customer Name', 'orderable' => false],
            ['data' => 'profile_status', 'name' => 'profile_status', 'title' => 'Profile Status', 'searchable' => false, 'orderable' => false],
            ['data' => 'contact_info', 'name' => 'users.email', 'title' => 'Contact Info', 'orderable' => false],
            ['data' => 'level_name', 'name' => 'level_name', 'title' => 'Level', 'searchable' => false],
            ['data' => 'total_trips', 'name' => 'total_trips', 'title' => 'Total Trip', 'searchable' => false, 'orderable' => false],
            ['data' => 'status', 'name' => 'users.status', 'title' => 'Status'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false, 'exportable' => false],
        ];
    }

    protected function filename(): string
    {
        return 'riders_' . date('YmdHis');
    }
}
