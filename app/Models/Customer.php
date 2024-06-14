<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    use HasFactory;

    protected $appends = ['image_fullpath'];
    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $path = asset('public/assets/admin/img/160x160/img1.jpg');
        if (!is_null($image) && Storage::disk('public')->exists('customer/' . $image)) {
            $path = asset('storage/app/public/customer/' . $image);
        }
        return $path;
    }
    public function orders()
    {
        return $this->hasMany(Order::class,'user_id');
    }
}
