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
            $totalBefore = Staff::count();
            $import = new StaffImport;
            Excel::import($import, $this->excelFile);
            $totalAfter = Staff::count();

            $this->importedCount = $totalAfter - $totalBefore;
            $this->skippedCount = $import->skippedCount;

            $message = "{$this->importedCount} staff records imported successfully!";
            if ($this->skippedCount > 0) {
                $message .= " {$this->skippedCount} records skipped due to validation errors or duplicates.";
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
