<?php

namespace App\Http\Livewire\Admin\PayslipDispatch;

use App\Models\Staff;
use Livewire\Component;
use App\Models\FileUpload;
use Livewire\WithPagination;
use App\Models\PayslipDispatch;
use App\Notifications\PayslipNotification;
use App\Jobs\ProcessPayslipDispatch;
use App\Exports\PayslipDispatchesExport;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $month = null;
    public $year = null;
    public $resendId = null;
    public $search = '';
    public $processing = false;

    public function mount()
    {

    }

    public function dispatchPayslips()
    {
        if (!$this->month || !$this->year) {
            session()->flash('error', 'Please select both month and year.');
            return;
        }
        $this->processing = true;

        $files = FileUpload::where('month', $this->month)
            ->where('year', $this->year)
            ->get();

        if ($files->isEmpty()) {
            session()->flash('error', 'No payslip files found for this month and year.');
            $this->processing = false;
            return;
        }

        /* $sent = 0;
        $failed = 0;
        $skipped = 0;
        $skippedStaff = []; */

        $skipped = 0;
        $skippedStaff = [];
        $toProcess = [];

        foreach ($files as $file) {
            $staff = Staff::where('staff_id', $file->staff_id)->first();

            if (!$staff) {
                $skipped++;
                $skippedStaff[] = $file->staff_id;
                continue;
            }

            // Skip if already dispatched for this staff/month/year
            $alreadyDispatched = PayslipDispatch::where('staff_id', $staff->staff_id)
                ->where('month', $this->month)
                ->where('year', $this->year)
                ->exists();

            if ($alreadyDispatched) {
                $skipped++;
                $skippedStaff[] = $file->staff_id;
                continue;
            }

            // Add to list of files to process
            $toProcess[] = $file;
        }

        // Queue the payslips that need processing
        $total = count($toProcess);
        foreach ($toProcess as $file) {
            ProcessPayslipDispatch::dispatch(
                $file->id,
                $this->month,
                $this->year,
                auth()->id()
            );
        }

        $this->processing = false;

        $msg = "{$total} payslip(s) queued for sending.";
        if ($skipped > 0) {
            $msg .= " {$skipped} skipped (already sent or no staff record";
            if (!empty($skippedStaff)) {
                // Limit the list if there are too many
                if (count($skippedStaff) > 5) {
                    $displayStaff = array_slice($skippedStaff, 0, 5);
                    $msg .= " for: " . implode(', ', $displayStaff) . " and " . (count($skippedStaff) - 5) . " others";
                } else {
                    $msg .= " for: " . implode(', ', $skippedStaff);
                }
            }
            $msg .= ").";
        }

        session()->flash('message', $msg);

        $this->reset(['month', 'year']);
    }

    public function setResendId($id)
    {
        $this->resendId = $id;
    }

    public function confirmResend($id)
    {
        $this->resend($id);
        $this->resendId = null;
    }

    public function resend($dispatchId)
    {
        $dispatch = PayslipDispatch::find($dispatchId);
        if (!$dispatch) {
            session()->flash('error', 'Dispatch record not found.');
            return;
        }

        $staff = Staff::where('staff_id', $dispatch->staff_id)->first();
        $file = FileUpload::where('staff_id', $dispatch->staff_id)
            ->where('month', $dispatch->month)
            ->where('year', $dispatch->year)
            ->first();

        if (!$staff || !$file) {
            session()->flash('error', 'Staff or file not found for resend.');
            return;
        }

        ProcessPayslipDispatch::dispatch(
            $file->id,
            $dispatch->month,
            $dispatch->year,
            auth()->id()
        );
        session()->flash('message', 'Payslip resent queued successfully!');
    }

    public function resendFailedDispatches()
    {
        if (!$this->month || !$this->year) {
            session()->flash('error', 'Please select both month and year.');
            return;
        }

        $failedDispatches = PayslipDispatch::where('month', $this->month)
            ->where('year', $this->year)
            ->where('status', 'failed')
            ->get();

        if ($failedDispatches->isEmpty()) {
            session()->flash('info', 'No failed dispatches found for the selected month and year.');
            return;
        }

        foreach ($failedDispatches as $dispatch) {
            // Dispatch the resend job for each failed dispatch
            ProcessPayslipDispatch::dispatch(
                $dispatch->file_id, // Assuming you have file_id in PayslipDispatch
                $dispatch->month,
                $dispatch->year,
                auth()->id()
            );
        }

        session()->flash('message', count($failedDispatches) . ' failed dispatches have been queued for resending.');
    }

    public function exportToExcel()
    {
        return Excel::download(
            new PayslipDispatchesExport($this->search),
            'payslip-dispatches-' . now()->format('Y-m-d') . '.xlsx'
        );
    }


    public function render()
    {
        $dispatches = PayslipDispatch::when($this->search, function($query) {
            $query->where('staff_id', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')
                ->orWhere('status', 'like', '%'.$this->search.'%');
        })
        ->latest()
        ->paginate(10);

        return view('livewire.admin.payslip-dispatch.index', [
            'dispatches' => $dispatches
        ])->extends('layouts.admin')->section('content');
    }
}
