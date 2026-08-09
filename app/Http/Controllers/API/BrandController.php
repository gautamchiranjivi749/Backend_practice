<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
      public function index()
    {
        $brands = Brand::latest()->paginate(10);

        return $this->success(
            BrandResource::collection($brands),
            'Brands fetched successfully.'
        );
    }

    /**
     * Store brand.
     */
    public function store(StoreBrandRequest $request)
    {
        $validated = $request->validated();

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {

            $logo = $request->file('logo');

            $logoName = time() . '_' . uniqid()
                . '.' . $logo->getClientOriginalExtension();

            $logo->storeAs(
                'brands',
                $logoName,
                'public'
            );

            $validated['logo'] = 'brands/' . $logoName;
        }

        $brand = Brand::create($validated);

        return $this->success(
            new BrandResource($brand),
            'Brand created successfully.'
        );
    }

    /**
     * Display single brand.
     */
    public function show(Brand $brand)
    {
        return $this->success(
            new BrandResource($brand),
            'Brand fetched successfully.'
        );
    }

    /**
     * Update brand.
     */
    public function update(
        UpdateBrandRequest $request,
        Brand $brand
    ) {
        $validated = $request->validated();

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('logo')) {

            if (
                $brand->logo &&
                Storage::disk('public')->exists($brand->logo)
            ) {
                Storage::disk('public')->delete($brand->logo);
            }

            $logo = $request->file('logo');

            $logoName = time() . '_' . uniqid()
                . '.' . $logo->getClientOriginalExtension();

            $logo->storeAs(
                'brands',
                $logoName,
                'public'
            );

            $validated['logo'] = 'brands/' . $logoName;
        }

        $brand->update($validated);

        return $this->success(
            new BrandResource($brand->fresh()),
            'Brand updated successfully.'
        );
    }

    /**
     * Delete brand.
     */
    public function destroy(Brand $brand)
    {
        if (
            $brand->logo &&
            Storage::disk('public')->exists($brand->logo)
        ) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        return $this->success(
            null,
            'Brand deleted successfully.'
        );
    }
}
