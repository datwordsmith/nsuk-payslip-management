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
            Excel::import(new StaffImport, $this->excelFile);

            session()->flash('message', 'Staff imported successfully!');
            $this->reset('excelFile');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: {$failure->errors()[0]}";
            }

            session()->flash('error', implode('<br>', $errors));
        } catch (\Exception $e) {
            session()->flash('error', 'Error importing staff. Please check your Excel file format.');
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
