<?php

namespace App\Models;

use App\Models\Unit;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $appends = ['image_fullpath'];
    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $path = asset('public/assets/admin/img/160x160/img2.jpg');
        if (!is_null($image) && Storage::disk('public')->exists('product/' . $image)) {
            $path = asset('storage/app/public/product/' . $image);
        }
        return $path;
    }
    /**
     * Get the user that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_type')->select(['id', 'unit_type']);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_ids');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function scopeActive($query)
    {
        $categories_id = Category::where('status', 1)->where('position', 0)->pluck('id')->toArray();
        if (count($categories_id) > 0) {
            return $query->where(function ($query_cat) use ($categories_id) {
                foreach ($categories_id as $value) {
                    $query_cat->orWhereJsonContains('category_ids', ["id" => (string) $value]);
                }
            });
        } else {
            return $query->where('category_ids', "no_active");
        }
    }
}
