<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;
    protected $fillable = [
        'image',
        'username',
        'gender',
        'phone_number',
        'address',
        'country',
        'city',
        'zip_code',
        'state',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
