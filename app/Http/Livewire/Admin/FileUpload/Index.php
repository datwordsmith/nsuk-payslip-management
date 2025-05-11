<?php

namespace App\Http\Livewire\Admin\FileUpload;

use Livewire\Component;
use App\Models\FileUpload;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $files = [];
    public $uploads = [];

    public $search;

    protected $rules = [
        'files.*' => [
            'required',
            'file',
            'max:10240',
            'mimes:pdf'
        ],
    ];

    public $messages = [
        'files.*.regex' => 'Invalid filename format. Must be in format: JS0000_MMYYYY.pdf'
    ];

    public function updatedFiles()
    {
        if (!empty($this->files)) {
            foreach ($this->files as $key => $file) {
                $validator = Validator::make(
                    ['file' => $file],
                    [
                        'file' => [
                            'required',
                            'file',
                            'max:10240',
                            'mimes:pdf',
                            function ($attribute, $value, $fail) {
                                $filename = $value->getClientOriginalName();
                                if (!preg_match('/^[A-Z]{2}\d{4}_(?:0[1-9]|1[0-2])2025\.pdf$/', $filename)) {
                                    $fail("File must be named like: AA0000_MM2025.pdf (e.g., JS0008_052025.pdf)");
                                }
                            }
                        ]
                    ]
                );

                if ($validator->fails()) {
                    $this->reset('files');
                    session()->flash('error', $validator->errors()->first());
                    return;
                }
            }
        }
    }

    private function extractFileInfo($filename)
    {
        // Remove .pdf extension
        $filename = str_replace('.pdf', '', $filename);

        // Split by underscore
        $parts = explode('_', $filename);

        if (count($parts) !== 2) {
            throw new \Exception('Invalid filename format');
        }

        $staffId = $parts[0];
        $dateString = $parts[1];

        // Extract month and year
        $month = intval(substr($dateString, 0, 2));
        $year = intval(substr($dateString, 2));

        return [
            'staff_id' => $staffId,
            'month' => $month,
            'year' => $year
        ];
    }

    public function upload()
    {
        foreach ($this->files as $key => $file) {
            $filename = $file->getClientOriginalName();
            if (!preg_match('/^[A-Z]{2}\d{4}_(?:0[1-9]|1[0-2])2025\.pdf$/', $filename)) {
                session()->flash('error', "Invalid filename format: $filename. Must be like AA0000_MM2025.pdf");
                $this->reset('files');
                return;
            }
        }

        $successCount = 0;
        $errors = [];

        try {
            foreach ($this->files as $file) {
                $filename = $file->getClientOriginalName();
                $fileInfo = $this->extractFileInfo($filename);

                // Check for duplicate
                $exists = FileUpload::where('staff_id', $fileInfo['staff_id'])
                    ->where('month', $fileInfo['month'])
                    ->where('year', $fileInfo['year'])
                    ->exists();

                if ($exists) {
                    $errors[] = "Payslip for staff {$fileInfo['staff_id']} ({$fileInfo['month']}/{$fileInfo['year']}) already exists";
                    continue; // Skip this file but continue with others
                }

                $path = $file->store('uploads');

                FileUpload::create([
                    'name' => $filename,
                    'path' => $path,
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                    'staff_id' => $fileInfo['staff_id'],
                    'month' => $fileInfo['month'],
                    'year' => $fileInfo['year']
                ]);

                $successCount++;
            }

            $this->files = [];

            // Show appropriate messages
            if ($successCount > 0) {
                session()->flash('message', "$successCount file(s) uploaded successfully!");
            }
            if (!empty($errors)) {
                session()->flash('error', implode("\n", $errors));
            }

            $this->emit('filesUploaded');

        } catch (\Exception $e) {
            session()->flash('error', 'Error uploading files. Please ensure filenames are in the correct format (e.g., JS0031_042025.pdf)');
        }
    }


    public function delete($id)
    {
        $file = FileUpload::find($id);
        if ($file) {
            Storage::delete($file->path);
            $file->delete();
        }
        session()->flash('message', 'File deleted successfully!');
    }

    public function render()
    {

        $fileUploads = FileUpload::where(function($query) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('staff_id', 'like', '%'.$this->search.'%')
                ->orWhere('year', 'like', '%'.$this->search.'%');
        })
        ->paginate(10);

        return view('livewire.admin.file-upload.index', [
            'fileUploads' => $fileUploads,
        ])->extends('layouts.admin')->section('content');

    }
}
