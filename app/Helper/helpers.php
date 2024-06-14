<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('displayImage')) {
    function displayImage($image, $isAvatar = false)
    {
        if (file_exists($image) && is_file($image)) {
            return asset($image);
        } elseif ($isAvatar) {
            return asset('public/assets/admin/img/160x160/img1.jpg');
        } else {
            return asset('public/assets/admin/img/160x160/img1.jpg');
        }
    }
}

if (!function_exists('onErrorImage')) {
    function onErrorImage($data, $src, $error_src ,$path)
    {
        if(isset($data) && strlen($data) >1 && Storage::disk('public')->exists($path.$data)){
            return $src;
        }
        return $error_src;
    }
}


