<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
{
    $query = Category::query();

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('slug', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    return CategoryResource::collection(
        $query->latest()->get()
    );
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
{
    $category = Category::create([
        'name' => $request->name,
        'slug' => $request->slug ?? Str::slug($request->name),
        'description' => $request->description,
        'status' => $request->status,
        'parent_id' => $request->parent_id,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Category created successfully.',
        'data' => $category,
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
          return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request,
    Category $category)
    {
         $category->update([
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'description' => $request->description,
        'status' => $request->status
    ]);

    return response()->json([
        'message' => 'Category updated successfully',
        'data' => new CategoryResource($category)
    ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
         $category->delete();

    return response()->json([
        'message' => 'Category deleted successfully'
    ]);
    }
}
