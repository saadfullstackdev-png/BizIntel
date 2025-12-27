<?php

namespace App\Http\Controllers\Api\App;

use App\Models\Appointments;
use App\Models\Services;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServicesController extends Controller
{
    public function index(Request $request)
    {
        $serivces = array();
        $count = 0;
        if ($request->has('is_app') && $request->is_app == 1) {
            $parent_services = Services::where('active', 1)->where('end_node', '!=',0)->orderby('id', 'asc')->get();
        }else{
            $parent_services = Services::where('active', 1)->where('end_node', 0)->orderby('id', 'asc')->get();
        }

        foreach ($parent_services as $serivce) {
            $c = $count++;
            if ($request->has('is_app') && $request->is_app == 1) {
                $serivces[$c] = array(
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
                $serivces[$c]['child'][] = array(
                    'id' => $serivce['id'],
                    'name' => $serivce['name'],
                    'duration' => $serivce['duration'],
                    'price' => $serivce['price'],
                    'color' => $serivce['color'],
                    'category_id' => $serivce['category_id'],
                    'description' => $serivce['description'] ? $serivce['description'] : '',
                    'image_src' => $serivce['image_src'] ? '/service_images/' . $serivce['image_src'] : '/default_image/db338af1e027b6b0d0f57e5ceb0b9bda.jpg',
                    'consultancy_type' => $serivce['consultancy_type'],
                    'active' => $serivce['active'],
                );
            }else{
                $serivces[$c]['child'][] = array(
                    'id' => $serivce['id'],
                    'name' => $serivce['name'],
                    'duration' => $serivce['duration'],
                    'price' => $serivce['price'],
                    'color' => $serivce['color'],
                    'category_id' => $serivce['category_id'],
                    'description' => $serivce['description'] ? $serivce['description'] : '',
                    'image_src' => $serivce['image_src'] ? '/service_images/' . $serivce['image_src'] : '/default_image/db338af1e027b6b0d0f57e5ceb0b9bda.jpg',
                    'consultancy_type' => $serivce['consultancy_type'],
                    'active' => $serivce['active'],
                );
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Services list',
            'data' => $serivces,
            'status_code' => 200,
        ]);
    }


    public function getServiceDetail($id)
    {
        // All services those  are parent this will you treatments
        $serivces = array();
        $count = 0;
        $parent_services = Services::where('active', 1)->where('end_node', 0)->where('id', $id)->orderby('id', 'asc')->get();
        foreach ($parent_services as $serivce) {
            $c = $count++;
            $serivces[$c] = array(
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
            $serivces[$c]['child'][] = array(
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
        return response()->json([
            'status' => true,
            'message' => 'Services Detail',
            'data' => $serivces,
            'status_code' => 200,
        ]);
    }


    /*
     * Search Services
     */
    public function getsearchservices(Request $request)
    {
        $services = array();

        $searchservices = Services::where([
            ['name', 'LIKE', "%{$request->name}%"],
            ['active', '=', 1]
        ])->orderby('id', 'asc')->get()->toArray();

        foreach ($searchservices as $key => $service) {
            if ($service['end_node'] == 0 && $service['parent_id'] == 0) {
                $services[$key] = $service;
            } else {
                $result = self::findParentRootService($service);
                $services[$key] = $result;
            }
        }
        $ids = array();
        $unique_services = array();
        foreach ($services as $service) {
            if (!in_array($service['id'], $ids, True)) {
                $ids[] = $service['id'];
                $result = $service;
                $result['image_src'] = $service['image_src'] ? '/service_images/' . $service['image_src'] : '/default_image/db338af1e027b6b0d0f57e5ceb0b9bda.jpg';
                $unique_services[] = $result;
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Desire Service',
            'data' => $unique_services,
            'status_code' => 200,
        ]);
    }

    /*
     * Function that define root service against child
     */
    function findParentRootService($service)
    {
        if ($service['parent_id'] == 0 && $service['end_node'] == 0) {
            return $service;
        } else {
            $service = Services::where('id', '=', $service['parent_id'])->first()->toArray();
            if ($service['parent_id'] == 0 && $service['end_node'] == 0) {
                return $service;
            } else {
                $this->findParentRootService($service);
            }
        }
    }

    /*
     * Function that define child services
     */
    function getchildservices()
    {
        $serivces = array();
        $count = 0;
        $parent_services = Services::where('active', 1)->where('end_node', 0)->orderby('id', 'asc')->get();

        foreach ($parent_services as $serivce) {
            $c = $count++;
            $serivces[$c] = array(
                'id' => $serivce['id'],
                'name' => $serivce['name'],
                'duration' => $serivce['duration'],
                'price' => $serivce['price'],
                'color' => $serivce['color'],
                'description' => $serivce['description'] ? $serivce['description'] : '',
                'image_src' => $serivce['image_src'] ? '/service_images/' . $serivce['image_src'] : '/default_image/db338af1e027b6b0d0f57e5ceb0b9bda.jpg',
                'active' => $serivce['active'],
                'consultancy_type' => $serivce['consultancy_type'],
                'child' => array(),
            );
            $child = Appointments::getNodeServices($serivce['id'], 1, false, true);
            if(count($child)){
                foreach ($child as $childservice) {
                    $serivces[$c]['child'][] = array(
                        'id' => $childservice['id'],
                        'name' => $childservice['name'],
                        'duration' => $childservice['duration'],
                        'price' => $childservice['price'],
                        'color' => $childservice['color'],
                        'description' => $childservice['description'] ? $childservice['description'] : '',
                        'image_src' => $childservice['image_src'] ? '/service_images/' . $childservice['image_src'] : '/default_image/db338af1e027b6b0d0f57e5ceb0b9bda.jpg',
                        'active' => $childservice['active'],
                    );
                }
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Services list',
            'data' => $serivces,
            'status_code' => 200,
        ]);
    }
}
