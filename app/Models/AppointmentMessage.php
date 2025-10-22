<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'author_id',
        'message',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}