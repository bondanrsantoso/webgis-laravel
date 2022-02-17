<?php

namespace Database\Seeders;

use App\Models\Hospital;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\ConsoleOutput;

class HospitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datasource = Storage::disk("local")->get("faskes.json");
        $hospitals = json_decode($datasource, true);
        $con = new ConsoleOutput();

        $i = 1;
        foreach ($hospitals as $hospitalItem) {
            try {
                // $con->write("\rWriting row $i...");
                $hospital = new Hospital($hospitalItem);
                $hospital->save();
            } catch (\Illuminate\Database\QueryException $e) {
                // $con->writeln("");
                $con->writeln("failed to write $i ... skipping");
            }
            $i++;
        }
    }
}
