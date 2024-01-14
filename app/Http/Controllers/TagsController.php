<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    public function index() {
        $totalActive = Tag::where("status", "active")->count();
        $totalHidden = Tag::where("status", "hidden")->count();
        $total = Tag::count();

        return response()->json([
            "data" => TagResource::collection(Tag::all()),
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
            $tag = Tag::create([
                "name" => $validated["name"]
            ]);
            $tag->status = "active";

            return response()->json([
                "data" => new TagResource($tag),
                "message" => "Added new tag successfully"
            ]);
        }
        catch(UniqueConstraintViolationException $e) {
            return response()->json([
                "error" => $e->getMessage(),
                "message" => "No duplicate tag name allowed"
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
            $tag = Tag::findorFail($id);
            $tag->name = $validated["name"];
            $tag->status = $validated["status"];
            $tag->save();

            $totalActive = Tag::where("status", "active")->count();
            $totalHidden = Tag::where("status", "hidden")->count();
            $total = Tag::count();

            return response()->json([
                "message" => "Tag updated successfully",
                "active" => $totalActive,
                "hidden" => $totalHidden,
                "total" => $total
            ]);
        }
        catch(ModelNotFoundException $e) {
            return response()->json([
                "error" => get_class($e),
                "message" => "Tag doesn't exist"
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
