<?php

namespace App\Exports;

use App\Models\Access;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AccessesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected Carbon $fromStart;
    protected Carbon $toEnd;
    protected ?int $branchId;
    protected string $tz;
    protected bool $isAdmin;
    protected ?int $userBranchId;

    public function __construct(Carbon $fromStart, Carbon $toEnd, bool $isAdmin, ?int $userBranchId, ?int $branchId, string $tz = 'America/Asuncion')
    {
        $this->fromStart    = $fromStart;
        $this->toEnd        = $toEnd;
        $this->branchId     = $branchId;
        $this->tz           = $tz;
        $this->isAdmin      = $isAdmin;
        $this->userBranchId = $userBranchId;
    }

    public function query()
    {
        return Access::query()
            ->with(['branch','user'])
            ->when(!$this->isAdmin, fn(Builder $q) => $q->where('branch_id', $this->userBranchId))
            ->when($this->branchId, fn(Builder $q) => $q->where('branch_id', $this->branchId))
            ->where(function ($q) {
                $q->whereBetween('entry_at', [$this->fromStart, $this->toEnd])
                  ->orWhereBetween('exit_at',  [$this->fromStart, $this->toEnd]);
            })
            ->orderByDesc('entry_at');
    }

    public function headings(): array
    {
        return [
            'ID', 'Sucursal', 'Tipo', 'Placa', 'Nombre', 'Documento',
            'Entrada', 'Salida', 'Observación Entrada', 'Observación Salida', 'Registró',
        ];
    }

    public function map($a): array
    {
        $in  = optional($a->entry_at)->timezone($this->tz)?->format('Y-m-d H:i');
        $out = optional($a->exit_at)->timezone($this->tz)?->format('Y-m-d H:i');

        return [
            $a->id,
            optional($a->branch)->name ?? '—',
            $a->type === 'vehicle' ? 'Vehículo' : 'A pie',
            $a->plate ?? '—',
            $a->full_name,
            $a->document,
            $in ?? '—',
            $out ?? '—',
            $a->entry_note ?? '',
            $a->exit_note ?? '',
            optional($a->user)->name ?? '—',
        ];
    }
}
