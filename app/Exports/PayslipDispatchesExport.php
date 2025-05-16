<?php

namespace App\Exports;

use App\Models\PayslipDispatch;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayslipDispatchesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $search;

    public function __construct($search = '')
    {
        $this->search = $search;
    }

    public function query()
    {
        return PayslipDispatch::query()
            ->when($this->search, function($query) {
                $query->where('staff_id', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('status', 'like', '%'.$this->search.'%');
            })
            ->with('user')
            ->latest();
    }

    public function headings(): array
    {
        return [
            'Staff ID',
            'Email',
            'Month/Year',
            'Status',
            'Sent At',
            'Sent By'
        ];
    }

    public function map($dispatch): array
    {
        return [
            $dispatch->staff_id,
            $dispatch->email,
            date('F Y', mktime(0, 0, 0, $dispatch->month, 1, $dispatch->year)),
            ucfirst($dispatch->status),
            $dispatch->sent_at ? $dispatch->sent_at->format('d M, Y H:i') : '-',
            $dispatch->user->name ?? 'Unknown'
        ];
    }
}
