<?php

namespace App\Http\Livewire\Admin\Staff;

use App\Models\Staff;
use Livewire\Component;
use App\Imports\StaffImport;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $staff_id;
    public $email;
    public $excelFile;
    public $importedCount = null;
    public $createdCount = null;
    public $updatedCount = null;
    public $skippedCount = null;
    public $search = '';

    protected $rules = [
        'staff_id' => 'required|unique:staff,staff_id',
        'email' => 'required|email|unique:staff,email',
        'excelFile' => 'required|mimes:xlsx,xls'
    ];

    public function addStaff()
    {
        $this->validate([
            'staff_id' => 'required|unique:staff,staff_id',
            'email' => 'required|email|unique:staff,email',
        ]);

        Staff::create([
            'staff_id' => $this->staff_id,
            'email' => $this->email,
        ]);

        $this->reset(['staff_id', 'email']);
        session()->flash('message', 'Staff added successfully!');
    }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $import = new StaffImport;
            Excel::import($import, $this->excelFile);

            // Pull counters from the import
            $this->importedCount = $import->importedCount; // created + updated
            $this->createdCount  = $import->createdCount;
            $this->updatedCount  = $import->updatedCount;
            $this->skippedCount  = $import->skippedCount;

            // Build a friendly message
            $parts = [];
            if ($this->createdCount) $parts[] = $this->createdCount.' created';
            if ($this->updatedCount) $parts[] = $this->updatedCount.' updated';
            if (empty($parts)) $parts[] = '0 changes';

            $message = implode(', ', $parts) . '.';
            if ($this->skippedCount > 0) {
                $message .= ' '.$this->skippedCount.' rows skipped (validation/duplicates/unchanged).';
            }

            session()->flash('message', $message);
            $this->reset('excelFile');
        } catch (\Exception $e) {
            session()->flash('error', 'Error importing staff: ' . $e->getMessage());
        }
    }


    public function delete($id)
    {
        Staff::find($id)->delete();
        session()->flash('message', 'Staff deleted successfully!');
    }

    public function render()
    {
        $staff = Staff::when($this->search, function($query) {
            $query->where('staff_id', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%');
        })->paginate(10);

        return view('livewire.admin.staff.index', [
            'staffList' => $staff
        ])->extends('layouts.admin')->section('content');
    }
}
