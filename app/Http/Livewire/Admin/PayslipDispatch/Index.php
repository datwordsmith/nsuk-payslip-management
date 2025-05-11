<?php

namespace App\Http\Livewire\Admin\PayslipDispatch;

use App\Models\Staff;
use Livewire\Component;
use App\Models\FileUpload;
use Livewire\WithPagination;
use App\Models\PayslipDispatch;
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

        $sent = 0;
        $failed = 0;
        $skipped = 0;
        $skippedStaff = [];

        foreach ($files as $file) {
            $staff = Staff::where('staff_id', $file->staff_id)->first();

            if (!$staff) {
                $skipped++;
                $skippedStaff[] = $file->staff_id;
                continue;
            }

            try {
                $staff->notify(new PayslipNotification($file, $this->month, $this->year));

                PayslipDispatch::create([
                    'staff_id' => $staff->staff_id,
                    'email' => $staff->email,
                    'month' => $this->month,
                    'year' => $this->year,
                    'status' => 'sent',
                    'sent_at' => now(),
                    'sent_by' => auth()->id()
                ]);
                $sent++;
            } catch (\Exception $e) {
                PayslipDispatch::create([
                    'staff_id' => $staff->staff_id,
                    'email' => $staff->email,
                    'month' => $this->month,
                    'year' => $this->year,
                    'status' => 'failed',
                    'sent_by' => auth()->id()
                ]);
                \Log::error('Payslip email failed for '.$staff->staff_id.': '.$e->getMessage());
                $failed++;
            }
        }

        $this->processing = false;

        $msg = "{$sent} payslip(s) sent.";
        if ($failed > 0) {
            $msg .= " {$failed} failed.";
        }
        if ($skipped > 0) {
            $msg .= " {$skipped} skipped (no staff record for: " . implode(', ', $skippedStaff) . ").";
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

        try {
            $staff->notify(new PayslipNotification($file, $dispatch->month, $dispatch->year));

            PayslipDispatch::create([
                'staff_id' => $staff->staff_id,
                'email' => $staff->email,
                'month' => $dispatch->month,
                'year' => $dispatch->year,
                'status' => 'sent',
                'sent_at' => now(),
                'sent_by' => auth()->id()
            ]);
            session()->flash('message', 'Payslip resent successfully!');
        } catch (\Exception $e) {
            PayslipDispatch::create([
                'staff_id' => $staff->staff_id,
                'email' => $staff->email,
                'month' => $dispatch->month,
                'year' => $dispatch->year,
                'status' => 'failed',
                'sent_by' => auth()->id()
            ]);
            \Log::error('Payslip resend failed for '.$staff->staff_id.': '.$e->getMessage());
            session()->flash('error', 'Payslip resend failed.');
        }
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
