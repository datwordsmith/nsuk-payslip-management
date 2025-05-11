<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayslipDispatch extends Model
{
    use HasFactory;
    protected $fillable = [
        'staff_id',
        'email',
        'month',
        'year',
        'status',
        'sent_at',
        'sent_by'
    ];

    protected $casts = [
        'sent_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
