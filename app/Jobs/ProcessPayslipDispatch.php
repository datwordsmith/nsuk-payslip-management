<?php

namespace App\Jobs;

use App\Models\Staff;
use App\Models\FileUpload;
use App\Models\PayslipDispatch;
use App\Notifications\PayslipNotification;
use Illuminate\Bus\Queueable;
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
     */
    public function handle(): void
    {
        $file = FileUpload::find($this->fileId);
        if (!$file) {
            Log::error('File not found for payslip dispatch', ['file_id' => $this->fileId]);
            return;
        }

        $staff = Staff::where('staff_id', $file->staff_id)->first();
        if (!$staff) {
            Log::error('Staff not found for payslip dispatch', ['staff_id' => $file->staff_id]);
            return;
        }

        // Double-check that it hasn't been sent during the queue wait time
        $alreadyDispatched = PayslipDispatch::where('staff_id', $staff->staff_id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->exists();

        if ($alreadyDispatched) {
            Log::info('Payslip already dispatched while in queue', [
                'staff_id' => $staff->staff_id,
                'month' => $this->month,
                'year' => $this->year
            ]);
            return;
        }

        try {
            $staff->notify(new PayslipNotification($file, $this->month, $this->year));

            // Record successful dispatch
            PayslipDispatch::create([
                'staff_id' => $staff->staff_id,
                'email' => $staff->email,
                'file_id' => $this->fileId, // Set the file_id here
                'month' => $this->month,
                'year' => $this->year,
                'status' => 'sent',
                'sent_at' => now(),
                'sent_by' => $this->userId,
            ]);

            Log::info('Payslip sent successfully via queue', [
                'staff_id' => $staff->staff_id,
                'month' => $this->month,
                'year' => $this->year,
            ]);
        } catch (\Exception $e) {
            // Construct the filename
            $filename = $staff->staff_id . '_' . str_pad($this->month, 2, '0', STR_PAD_LEFT) . $this->year . '.pdf';

            // Record failed dispatch
            PayslipDispatch::create([
                'staff_id' => $staff->staff_id,
                'email' => $staff->email,
                'file_id' => $this->fileId, // Set the file_id here
                'month' => $this->month,
                'year' => $this->year,
                'status' => 'failed',
                'sent_by' => $this->userId,
            ]);

            Log::error('Payslip sending failed in queue', [
                'staff_id' => $staff->staff_id,
                'filename' => $filename, // Include the filename in the log
                'error' => $e->getMessage(),
            ]);
        }
    }
}
