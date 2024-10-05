<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Appointment extends Model
{
    use HasFactory;
    protected $fillable = [
        'appointment_id',
        'customer_id', //booked by
        'appointment_date',
        'appointment_time',
        'number_of_people',
        'status',
        'total_amount'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class , 'customer_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'appointment_services');
    }

    protected function appointmentDate(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('d-m-Y'),
            set: fn(string $value) =>  Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d')
        );
    }

    protected function appointmentTime(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('H:i'),
            set: fn(string $value) => Carbon::createFromFormat('H:i', $value)->format('H:i:s')
        );
    }
}
