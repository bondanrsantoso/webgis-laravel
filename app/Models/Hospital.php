<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    public static function getAllType()
    {
        return Hospital::distinct()->get("tipefaskes")->pluck("tipefaskes")->all();
    }

    public static function getAllProvince()
    {
        return Hospital::distinct()->get("provinsi")->pluck("provinsi")->all();
    }

    public static function getAllCities()
    {
        return Hospital::distinct()->get("kotakab")->pluck("kotakab")->all();
    }
}
