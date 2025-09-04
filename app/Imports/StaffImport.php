<?php

namespace App\Imports;

use App\Models\Staff;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class StaffImport implements ToModel, WithStartRow, WithValidation, SkipsOnFailure
{
    public $importedCount = 0;
    public $skippedCount = 0;
    public $skippedRows = [];

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row[1]) || empty($row[2])) {
            return null;
        }

        return new Staff([
            'staff_id' => $row[1], // Column B
            'email' => $row[2],    // Column C
        ]);
    }

    // Start from row 2 (skip header)
    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            '1' => 'required|unique:staff,staff_id', // Column B
            '2' => 'required|email|unique:staff,email', // Column C
        ];
    }

    public function customValidationMessages()
    {
        return [
            '1.required' => 'Staff ID is required',
            '1.unique' => 'Staff ID already exists',
            '2.required' => 'Email is required',
            '2.email' => 'Invalid email format',
            '2.unique' => 'Email already exists',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->skippedCount++;
            $this->skippedRows[] = [
                'row' => $failure->row(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ];
        }
    }
}
