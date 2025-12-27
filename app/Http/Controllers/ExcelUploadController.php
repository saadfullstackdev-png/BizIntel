<?php

namespace App\Http\Controllers;

use App\Models\Cities;
use App\Models\Towns;
use App\Temporary;

class ExcelUploadController extends Controller
{
    public function towns()
    {
        $temporary = Temporary::all();
        foreach ($temporary as $temp) {
            $town = ucfirst(trim($temp->name));
            $city = trim($temp->city_id);
            $town_record = Towns::where('name', $town)->where('city_id', $city)->get()->first();
            if (!$town_record) {
                $record = new Towns([
                    'name' => $town,
                    'city_id' => $city,
                    'active' => true,
                    'account_id' => 1
                ]);
                $record->save();
                echo "**************** Record With Id " . $temp->id . " is save <br>";
            } else {
                echo "Record With Id " . $temp->id . " is already exists ****************  <br>";
            }

            // Temporary::destroy($temp->id);

        }
    }
}
