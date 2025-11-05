<?php

namespace App\Imports;

use App\Models\Staff;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class StaffImport implements OnEachRow, WithStartRow, SkipsOnFailure
{
    public $importedCount = 0; // created + updated
    public $createdCount = 0;
    public $updatedCount = 0;
    public $skippedCount = 0;
    public $skippedRows = [];

    /**
     * Handle each row in the sheet: upsert by staff_id and update email.
     * Columns: [0 => A (ignored), 1 => staff_id (B), 2 => email (C)]
     */
    public function onRow(\Maatwebsite\Excel\Row $row): void
    {
        $data = $row->toArray();
        $rowNumber = $row->getIndex();

        $staffId = isset($data[1]) ? trim((string) $data[1]) : '';
        $email   = isset($data[2]) ? strtolower(trim((string) $data[2])) : '';

        // Basic emptiness checks
        if ($staffId === '' || $email === '') {
            $this->skipRow($rowNumber, $data, ['Staff ID and Email are required.']);
            return;
        }

        // Email format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->skipRow($rowNumber, $data, ['Invalid email format.']);
            return;
        }

        // Find existing records
        $staff = Staff::where('staff_id', $staffId)->first();
        $emailOwner = Staff::where('email', $email)->first();

        // Prevent assigning an email already used by a different staff
        if ($emailOwner && (!$staff || $emailOwner->id !== $staff->id)) {
            $this->skipRow($rowNumber, $data, ['Email already exists for a different staff.']);
            return;
        }

        if ($staff) {
            // Update if changed
            if ($staff->email !== $email) {
                $staff->email = $email;
                $staff->save();
                $this->updatedCount++;
                $this->importedCount++;
            } else {
                // No change
                $this->skipRow($rowNumber, $data, ['Unchanged: email is the same.']);
            }
        } else {
            // Create new staff record
            Staff::create([
                'staff_id' => $staffId,
                'email'    => $email,
            ]);
            $this->createdCount++;
            $this->importedCount++;
        }
    }

    // Start from row 2 (skip header)
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Collect validation failures when using WithValidation (kept for compatibility).
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->skipRow($failure->row(), $failure->values(), $failure->errors());
        }
    }

    protected function skipRow(int $rowNumber, $values, array $errors): void
    {
        $this->skippedCount++;
        $this->skippedRows[] = [
            'row' => $rowNumber,
            'errors' => $errors,
            'values' => $values,
        ];
        // Optional: log for debugging
        if (function_exists('logger')) {
            \Illuminate\Support\Facades\Log::warning('Staff import skipped', [
                'row' => $rowNumber,
                'errors' => $errors,
                'values' => $values,
            ]);
        }
    }
}
