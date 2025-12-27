<?php

namespace App\Http\Controllers\Api\App;

use App\Models\Banner;
use App\Models\Services;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboadController extends Controller
{
    public function dashboad()
    {

        $bannerArray = array();
        $serviceArray = array();
        $exportArray = array();

        $count = 0;
        $images = Banner::where('active', '=', '1')->get();
        foreach ($images as $key => $image) {
            $bannerArray[$count++] = array(
                'id' => $image->id,
                'active' => $image->active,
                'image' => $image->image_src ? '/banners_images/' . $image->image_src : '/default_image/ae877a93f4983269a8c9520ad011a46f.png',
                'banner_type' => $image->banner_type,
                'banner_value' => $image->banner_value,
                'account_id' => $image->account_id
            );
        }
        $count = 0;
        $services = Services::where('active', 1)->where([['end_node', 0],['is_mobile','=',1]])->orderby('id', 'asc')->take(7)->get();
        foreach ($services as $serivce) {
            $c = $count++;
            $serviceArray[$c] = array(
                'id' => $serivce['id'],
                'name' => $serivce['name'],
                'duration' => $serivce['duration'],
                'price' => $serivce['price'],
                'color' => $serivce['color'],
                'description' => $serivce['description'] ? $serivce['description'] : '',
                'image_src' => $serivce['image_src'] ? '/service_images/' . $serivce['image_src'] : '/default_image/db338af1e027b6b0d0f57e5ceb0b9bda.jpg',
                'active' => $serivce['active'],
                'consultancy_type' => $serivce['consultancy_type'],
                'child' => [],
            );
            $serviceArray[$c]['child'][] = array(
                'id' => $serivce['id'],
                'name' => $serivce['name'],
                'duration' => $serivce['duration'],
                'price' => $serivce['price'],
                'color' => $serivce['color'],
                'description' => $serivce['description'] ? $serivce['description'] : '',
                'image_src' => $serivce['image_src'] ? '/service_images/' . $serivce['image_src'] : '/default_image/db338af1e027b6b0d0f57e5ceb0b9bda.jpg',
                'active' => $serivce['active'],
                'consultancy_type' => $serivce['consultancy_type'],
            );
        }

        $count = 0;
        $practitioners = User::where([
            ['active', '=', '1'],
            ['user_type_id', '=', '5'],
            ['is_mobile','=',1]
        ])->take(10)->get();
        foreach ($practitioners as $key => $practitioner) {
            $exportArray[$count++] = array(
                'id' => $practitioner->id,
                'name' => $practitioner->name,
                'active' => $practitioner->active,
                'image' => $practitioner->image_src ? '/doctor_image/' . $practitioner->image_src : '/default_image/4f190f4a7c149ad0024886a7f098052e.jpg'
            );
        }
        return response()->json([
            'status' => true,
            'message' => "Profile update successful",
            'bannerArray' => $bannerArray,
            'serviceArray' => $serviceArray,
            'exportArray' => $exportArray,
            'status_code' => 200,
        ]);
    }
}
