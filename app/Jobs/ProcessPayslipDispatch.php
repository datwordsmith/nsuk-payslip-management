<?php

namespace App\Jobs;

use App\Models\Staff;
use App\Models\FileUpload;
use App\Models\PayslipDispatch;
use App\Notifications\PayslipNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPayslipDispatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileId;
    protected $month;
    protected $year;
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($fileId, $month, $year, $userId)
    {
        $this->fileId = $fileId;
        $this->month = $month;
        $this->year = $year;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = FileUpload::find($this->fileId);
        if (!$file) {
            Log::error('File not found for payslip dispatch', [
                'file_id' => $this->fileId
            ]);
            return;
        }

        $staff = Staff::where('staff_id', $file->staff_id)->first();
        if (!$staff) {
            Log::info('Staff not found for payslip dispatch', [
                'staff_id' => $file->staff_id
            ]);
            return;
        }

        // Check if already dispatched
        $alreadyDispatched = PayslipDispatch::where('staff_id', $staff->staff_id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->exists();

        if ($alreadyDispatched) {
            Log::info('Payslip already dispatched', [
                'staff_id' => $staff->staff_id,
                'month' => $this->month,
                'year' => $this->year
            ]);
            return;
        }

        try {
            // Send notification
            $staff->notify(new PayslipNotification($file, $this->month, $this->year));

            // Record successful dispatch
            PayslipDispatch::create([
                'staff_id' => $staff->staff_id,
                'email' => $staff->email,
                'month' => $this->month,
                'year' => $this->year,
                'status' => 'sent',
                'sent_at' => now(),
                'sent_by' => $this->userId
            ]);

            Log::info('Payslip dispatched successfully', [
                'staff_id' => $staff->staff_id
            ]);
        } catch (\Exception $e) {
            // Record failed dispatch
            PayslipDispatch::create([
                'staff_id' => $staff->staff_id,
                'email' => $staff->email,
                'month' => $this->month,
                'year' => $this->year,
                'status' => 'failed',
                'sent_by' => $this->userId
            ]);

            Log::error('Payslip dispatch failed', [
                'staff_id' => $staff->staff_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
