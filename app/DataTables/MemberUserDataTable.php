<?php

namespace App\DataTables;

use App\Models\Members;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Builder as HtmlBuilder;

class MemberUserDataTable extends DataTable
{
    protected ?int $userId   = null;
    protected ?string $userName = null;
    protected ?string $teamName = null;

    public function setUserContext($userId, $userName, $teamName): self
    {
        $this->userId   = $userId;
        $this->userName = $userName;
        $this->teamName = $teamName;
        return $this;
    }

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('marketing_id', fn($member) => $member->marketing?->name ?? '—')
            ->editColumn('team_id', fn($member) => $member->team?->name ?? '—')
            ->setRowId('id');
    }

    public function query(Members $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['marketing', 'team'])
            ->where('marketing_id', $this->userId);

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('members-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('user-management.users.members.data', $this->userId))
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>",)
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0);
    }

    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Member Name'),
            Column::make('nama_rekening')->title('Rekening Name'),
            Column::make('username'),
            Column::make('phone'),
            Column::make('marketing_id')->title('Marketing'),
            Column::make('team_id')->title('Team'),
        ];
    }

    protected function filename(): string
    {
        return "Members_{$this->teamName}_{$this->userName}_" . date('YmdHis');
    }
}
