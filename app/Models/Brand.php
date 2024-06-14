<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use HasFactory;


    protected $appends = ['image_fullpath'];
    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $path = asset('public/assets/admin/img/160x160/img1.jpg');
        if (!is_null($image) && Storage::disk('public')->exists('brand/' . $image)) {
            $path = asset('storage/app/public/brand/' . $image);
        }
        return $path;
    }

    public function products()
    {
        return $this->hasMany(Product::class,'brand_id');
    }

}
