<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    public function role(){
        return $this->belongsTo(AdminRole::class,'role_id');
    }

    protected $appends = ['image_fullpath'];
    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $path = asset('public/assets/admin/img/160x160/img1.jpg');
        if (!is_null($image) && Storage::disk('public')->exists('admin/' . $image)) {
            $path = asset('storage/app/public/admin/' . $image);
        }
        return $path;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];
}
