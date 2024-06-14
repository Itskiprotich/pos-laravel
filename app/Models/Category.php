<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'position',
        'image',
        'status',
        'company_id',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['image_fullpath'];
    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $path = asset('public/assets/admin/img/160x160/img1.jpg');
        if (!is_null($image) && Storage::disk('public')->exists('category/' . $image)) {
            $path = asset('storage/app/public/category/' . $image);
        }
        return $path;
    }


    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function childes()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }
    public function scopePosition($query)
    {
        return $query->where('position', '=', 0);
    }
    public function products()
    {
        return $this->hasMany(Product::class,'category_id');
    }
}
