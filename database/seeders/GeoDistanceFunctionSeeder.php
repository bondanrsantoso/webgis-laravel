<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeoDistanceFunctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $earthRadius = 6366.71;

        DB::statement("DROP FUNCTION IF EXISTS geoDistance");

        $SQLFunction = "
            CREATE FUNCTION geoDistance (
                src_lat DECIMAL(8,5), 
                src_lng DECIMAL(8,5), 
                dest_lat DECIMAL(8,5),
                dest_lng DECIMAL(8,5)
            )
            RETURNS DECIMAL(19,2) DETERMINISTIC
            BEGIN
                DECLARE distance_rad DECIMAL(8,5);
                DECLARE src_lat_rad DECIMAL(8,5);
                DECLARE src_lng_rad DECIMAL(8,5);
                DECLARE dest_lat_rad DECIMAL(8,5);
                DECLARE dest_lng_rad DECIMAL(8,5);

                DECLARE distance_km DECIMAL(19,2);
                
                SELECT src_lat * PI() / 180 INTO src_lat_rad;
                SELECT src_lng * PI() / 180 INTO src_lng_rad;
                SELECT dest_lat * PI() / 180 INTO dest_lat_rad;
                SELECT dest_lng * PI() / 180 INTO dest_lng_rad;

                SELECT 
                    2 * 
                    ASIN(
                        SQRT(
                            POW(
                                SIN((dest_lat_rad - src_lat_rad) / 2),
                                2
                            ) + 
                            COS(src_lat_rad) *
                            COS(dest_lat_rad) *
                            POW(
                                SIN((dest_lng_rad - src_lng_rad) / 2),
                                2
                            )
                        )
                    )
                INTO distance_rad;

                SELECT distance_rad * $earthRadius INTO distance_km;

                RETURN distance_km;
            END
        ";

        DB::statement($SQLFunction);
    }
}
