<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'size',
        'type',
        'uploaded_by',
        'staff_id',
        'month',
        'year'
    ];


    protected $casts = [
        'month' => 'integer',
        'year' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
