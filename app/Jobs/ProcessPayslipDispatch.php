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
    private $isResend;

    /**
     * Create a new job instance.
     */
    public function __construct($fileId, $month, $year, $userId, $isResend = false)
    {
        $this->fileId = $fileId;
        $this->month = $month;
        $this->year = $year;
        $this->userId = $userId;
        $this->isResend = $isResend;
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

        // Only check for 'sent' status to allow resending failed ones
        $alreadyDispatched = PayslipDispatch::where('staff_id', $staff->staff_id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->where('status', 'sent')
            ->exists();

        if ($alreadyDispatched) {
            Log::info('Payslip already dispatched successfully; skipping.', [
                'staff_id' => $staff->staff_id,
                'month' => $this->month,
                'year' => $this->year
            ]);
            return;
        }

        try {
            Log::info('About to send payslip notification', [
                'staff_id' => $staff->staff_id,
                'email' => $staff->email,
                'month' => $this->month,
                'year' => $this->year
            ]);

            $staff->notify(new PayslipNotification($file, $this->month, $this->year));

            // Create or update dispatch record
            PayslipDispatch::updateOrCreate(
                [
                    'staff_id' => $staff->staff_id,
                    'month' => $this->month,
                    'year' => $this->year,
                ],
                [
                    'email' => $staff->email,
                    'status' => 'sent',
                    'sent_at' => now(),
                    'sent_by' => $this->userId,
                ]
            );

            Log::info('Payslip sent successfully', [
                'staff_id' => $staff->staff_id,
                'email' => $staff->email
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send payslip', [
                'staff_id' => $staff->staff_id,
                'email' => $staff->email,
                'error' => $e->getMessage()
            ]);

            // Create or update dispatch record with failed status
            PayslipDispatch::updateOrCreate(
                [
                    'staff_id' => $staff->staff_id,
                    'month' => $this->month,
                    'year' => $this->year,
                ],
                [
                    'email' => $staff->email,
                    'status' => 'failed',
                    'sent_by' => $this->userId,
                ]
            );

            throw $e;
        }
    }
}
