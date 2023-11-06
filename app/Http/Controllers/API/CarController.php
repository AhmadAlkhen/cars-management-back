<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Car;
use Illuminate\Support\Facades\Cache;

class CarController extends Controller
{

    public function index(Request $request, Car $cars)
    {
        try {
            $currentPage = $request->input('currentPage') ? $request->input('currentPage') : 1;
            $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
    
            // Define a unique cache key based on the request parameters
            $cacheKey = 'car_data_' . $currentPage . '_' . $perPage;
    
            // Check if the data is present in the cache
            if (Cache::has($cacheKey)) {
                $cachedData = Cache::get($cacheKey);
                return response()->json([
                    'data' => $cachedData,
                    'status' => 'true',
                    'isCashing' => 'true'
                ], 200);
            }
    
            $cars = $cars->newQuery();
    
            $cars = $cars->where('status', 1)
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $currentPage);
    
            // Cache the fetched data with an expiration time ( 5 minutes)
            Cache::put($cacheKey, $cars, 300);
    
            return response()->json([
                'data' => $cars,
                'status' => 'true',
                'isCashing' => 'false'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
   
    public function store(Request $request){

            try {
                $request->validate([
                    'make' => 'required',
                    'model' => 'required',
                    'year' => 'required|integer',
                    'vin' => 'required|unique:cars',
                    'shipping_status' => 'required',
                ]);

                $car = new Car;
                $car->make = $request->input('make');
                $car->model = $request->input('model');
                $car->year = $request->input('year');
                $car->vin = $request->input('vin');
                $car->status = $request->input('status');
                $car->shipping_status = $request->input('shipping_status');
                $car->save();


                // Clear the cache entries with the prefix 'car_data_'
                $this->clearCarDataCache();

                return response()->json([
                    'status' => true,
                    'message' => 'Added successfuly'
                ], 200);

            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
    }

    public function clearCarDataCache()
    {
        // Prefix to match cache entries 
        // if we use a redis we can dlete the the keys that start with the $prefix = 'car_data_' 
        $prefix = 'car_data_';
    
       
        Cache::flush(); // This clears all cache entries
    
    }

     public function updateShipping_status(Request $request){
        try {
           
             $car = Car::find($request->id);

             if ($car) {
                $request->validate([
                    'shipping_status' => 'required|in:Pending,In Transit,Delivered',
                ]);
        
                $car->update(['shipping_status'=> $request->input('shipping_status')] );
                
                // Clear the cache entries with the prefix 'car_data_'
                $this->clearCarDataCache();
                
                return response()->json([
                    'status' =>'true' ,
                    '$request->id' =>$request->id ,
                    'message' =>"Updated successfully"
                ], 200);
            }
            
         }catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

}

