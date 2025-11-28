<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandsModelsController extends Controller
{
    public function index()
    {
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();

        $result = $brands->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
                'created_at' => $brand->created_at,
                'updated_at' => $brand->updated_at,
                'models' => $brand->models->map(function ($model) {
                    return [
                        'id' => $model->id,
                        'name' => $model->name,
                        'created_at' => $model->created_at,
                        'updated_at' => $model->updated_at,
                    ];
                })->toArray(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
