<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index() {
        $totalActive = Category::where("status", "active")->count();
        $totalHidden = Category::where("status", "hidden")->count();
        $total = Category::count();

        return response()->json([
            "data" => CategoryResource::collection(Category::all()),
            "active" => $totalActive,
            "hidden" => $totalHidden,
            "total" => $total
        ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            "name" => "required|regex:/^[a-zA-Z ]+$/"
        ]);

        try {
            $category = Category::create([
                "name" => $validated["name"]
            ]);
            $category->status = "active";

            return response()->json([
                "data" => new CategoryResource($category),
                "message" => "Added new category successfully"
            ]);
        }
        catch(UniqueConstraintViolationException $e) {
            return response()->json([
                "error" => $e->getMessage(),
                "message" => "No duplicate category name allowed"
            ], 500);
        }
        catch(\Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Something wrong happened"
            ], 500);
        }
    }

    public function update(Request $request, int $id) {
        $validated = $request->validate([
            "name" => "required",
            "status" => "required"
        ]);

        try {
            $category = Category::findorFail($id);
            $category->name = $validated["name"];
            $category->status = $validated["status"];
            $category->save();

            $totalActive = Category::where("status", "active")->count();
            $totalHidden = Category::where("status", "hidden")->count();
            $total = Category::count();

            return response()->json([
                "message" => "Category updated successfully",
                "active" => $totalActive,
                "hidden" => $totalHidden,
                "total" => $total
            ]);
        }
        catch(ModelNotFoundException $e) {
            return response()->json([
                "error" => get_class($e),
                "message" => "Category doesn't exist"
            ], 500);
        }
        catch(\Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
                "message" => "Something wrong happened"
            ], 500);
        }
    }
}
