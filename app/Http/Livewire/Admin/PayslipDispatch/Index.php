<?php

namespace App\Http\Livewire\Admin\PayslipDispatch;

use Carbon\Carbon;
use App\Models\Staff;
use Livewire\Component;
use App\Models\FileUpload;
use Livewire\WithPagination;
use App\Models\PayslipDispatch;
use App\Jobs\ProcessPayslipDispatch;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayslipDispatchesExport;
use App\Notifications\PayslipNotification;

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
                ->where('status', 'sent')
                ->exists();

            if ($alreadyDispatched) {
                $skipped++;
                $skippedStaff[] = $file->staff_id;
                continue;
            }

            // Add to list of files to process
            $toProcess[] = $file;
        }

        // Process the payslips directly or queue them
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

        // Mark the dispatch as failed temporarily so the job can proceed
        PayslipDispatch::where('staff_id', $dispatch->staff_id)
            ->where('month', $dispatch->month)
            ->where('year', $dispatch->year)
            ->update(['status' => 'resending']);

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
            // Mark as resending first
            $dispatch->update(['status' => 'resending']);

            // Find the file for this dispatch
            $file = FileUpload::where('staff_id', $dispatch->staff_id)
                ->where('month', $dispatch->month)
                ->where('year', $dispatch->year)
                ->first();

            if ($file) {
                ProcessPayslipDispatch::dispatch(
                    $file->id,
                    $dispatch->month,
                    $dispatch->year,
                    auth()->id()
                );
            }
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
            $search = trim($this->search);
            $matched = false;

            // Explicit status search
            if (in_array(strtolower($search), ['sent', 'failed', 'resending'])) {
                $query->where('status', strtolower($search));
                $matched = true;
            }

            // Month-Year pattern (e.g., "June 2025")
            elseif (preg_match('/^(\w+)\s+(\d{4})$/i', $search, $matches)) {
                $monthName = $matches[1];
                $year = $matches[2];
                $monthNumber = date('n', strtotime($monthName . ' 1'));
                if ($monthNumber) {
                    $query->where('month', $monthNumber)
                        ->where('year', $year);
                    $matched = true;
                }
            }

            // Year only
            elseif (preg_match('/^\d{4}$/', $search)) {
                $query->where('year', $search);
                $matched = true;
            }

            // Month only (full month name)
            elseif (preg_match('/^(january|february|march|april|may|june|july|august|september|october|november|december)$/i', $search)) {
                $monthNumber = date('n', strtotime($search . ' 1'));
                if ($monthNumber) {
                    $query->where('month', $monthNumber);
                    $matched = true;
                }
            }

            // If no specific pattern matched, do general search
            if (!$matched) {
                $query->where(function($q) use ($search) {
                    $q->where('staff_id', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%');
                });
            }
        })
        ->latest()
        ->paginate(10);

        return view('livewire.admin.payslip-dispatch.index', [
            'dispatches' => $dispatches
        ])->extends('layouts.admin');
    }
}
