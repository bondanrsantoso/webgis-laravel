<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HospitalController extends Controller
{
    public function index(Request $req)
    {
        $tipeFaskes = Hospital::getAllType();
        $provinsi = Hospital::getAllProvince();
        $kotakab = Hospital::getAllCities();

        $valid = $req->validate([
            "lat" => "numeric|nullable",
            "lng" => "numeric|nullable",
            "lat_north" => "numeric|nullable",
            "lat_south" => "numeric|nullable",
            "lng_west" => "numeric|nullable",
            "lng_east" => "numeric|nullable",
            "q" => "nullable",
            "tipefaskes" => "nullable|in:" . implode(",", $tipeFaskes),
            "provinsi" => "nullable|in:" . implode(",", $provinsi),
            "kotakab" => "nullable|in:" . implode(",", $kotakab),
        ]);

        $defaults = [
            "lat" => -6.920164290834744,
            "lng" => 107.63331413269044,
            "lat_north" => -6.8824593930942894,
            "lat_south" => -6.957866177296302,
            "lng_west" => 107.56954193115236,
            "lng_east" => 107.66979217529297,
        ];

        /**
         * @var \Illuminate\Database\Query\Builder
         */
        $hospitalQuery =
            Hospital::where("lat", "<=", $req->input("lat_north", $defaults["lat_north"]))
            ->where("lat", ">=", $req->input("lat_south", $defaults["lat_south"]))
            ->where("lng", "<=", $req->input("lng_east", $defaults["lng_east"]))
            ->where("lng", ">=", $req->input("lng_west", $defaults["lng_west"]))
            ->where("namafaskes", "like", "%" . $req->input("q", "") . "%");

        $hospitalQuery = $hospitalQuery->orderByRaw(
            "geoDistance(lat, lng, ?, ?) asc",
            [
                $req->input("lat", $defaults["lat"]),
                $req->input("lng", $defaults["lng"]),
            ]
        );
        $hospitalQuery = $hospitalQuery->selectRaw(
            "*, geoDistance(lat, lng, ?, ?) as distance",
            [
                $req->input("lat", $defaults["lat"]),
                $req->input("lng", $defaults["lng"]),
            ]
        )->limit(500);

        foreach (["tipefaskes", "provinsi", "kotakab"] as $filterField) {
            if ($req->has($filterField)) {
                $hospitalQuery = $hospitalQuery->where($filterField, $req->input($filterField));
            }
        }

        $hospitals = $hospitalQuery->get();

        return response()->json($hospitals);
    }
}
