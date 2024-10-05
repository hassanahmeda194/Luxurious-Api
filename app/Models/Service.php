<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'vendor_id' //service owner
    ];

    public function appointments()
    {
        return $this->belongsToMany(Appointment::class, 'appointment_services');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, "vendor_id");
    }

    public function service_reviews(): HasMany
    {
        return $this->hasMany(ServiceReview::class, 'service_id');
    }
}
