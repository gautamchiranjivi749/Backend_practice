<?php

namespace App\Http\Controllers\API;



use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{
    use ApiResponse;

    /**
     * Get authenticated user's addresses
     */
    public function index(Request $request)
    {
        $addresses = UserAddress::where(
            'user_id',
            $request->user()->id
        )
        ->latest()
        ->get();

        return $this->success(
            $addresses,
            'Addresses fetched successfully.'
        );
    }

    /**
     * Create address
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',

            'phone' => 'required|string|max:20',

            'province' => 'required|string|max:100',

            'city' => 'required|string|max:100',

            'area' => 'required|string|max:150',

            'street_address' => 'required|string|max:255',

            'postal_code' => 'nullable|string|max:20',

            'is_default' => 'sometimes|boolean',
        ]);

        $user = $request->user();

        return DB::transaction(function () use (
            $validated,
            $user
        ) {

            /*
             * If this address is default,
             * remove default from existing addresses.
             */
            if (
                isset($validated['is_default']) &&
                $validated['is_default'] === true
            ) {
                UserAddress::where('user_id', $user->id)
                    ->update([
                        'is_default' => false
                    ]);
            }

            /*
             * If this is the user's first address,
             * automatically make it default.
             */
            if (
                !UserAddress::where(
                    'user_id',
                    $user->id
                )->exists()
            ) {
                $validated['is_default'] = true;
            }

            $validated['user_id'] = $user->id;

            $address = UserAddress::create($validated);

            return $this->success(
                $address,
                'Address created successfully.'
            );
        });
    }

    /**
     * Show address
     */
    public function show(Request $request, $id)
    {
        $address = UserAddress::where(
            'user_id',
            $request->user()->id
        )
        ->findOrFail($id);

        return $this->success(
            $address,
            'Address fetched successfully.'
        );
    }

    /**
     * Update address
     */
    public function update(
        Request $request,
        $id
    ) {
        $address = UserAddress::where(
            'user_id',
            $request->user()->id
        )
        ->findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'sometimes|required|string|max:255',

            'phone' => 'sometimes|required|string|max:20',

            'province' => 'sometimes|required|string|max:100',

            'city' => 'sometimes|required|string|max:100',

            'area' => 'sometimes|required|string|max:150',

            'street_address' => 'sometimes|required|string|max:255',

            'postal_code' => 'nullable|string|max:20',

            'is_default' => 'sometimes|boolean',
        ]);

        return DB::transaction(function () use (
            $validated,
            $address,
            $request
        ) {

            if (
                isset($validated['is_default']) &&
                $validated['is_default'] === true
            ) {
                UserAddress::where(
                    'user_id',
                    $request->user()->id
                )
                ->where('id', '!=', $address->id)
                ->update([
                    'is_default' => false
                ]);
            }

            $address->update($validated);

            return $this->success(
                $address->fresh(),
                'Address updated successfully.'
            );
        });
    }

    /**
     * Delete address
     */
    public function destroy(
        Request $request,
        $id
    ) {
        $address = UserAddress::where(
            'user_id',
            $request->user()->id
        )
        ->findOrFail($id);

        $wasDefault = $address->is_default;

        $address->delete();

        /*
         * If default address was deleted,
         * make another address default.
         */
        if ($wasDefault) {

            $newDefault = UserAddress::where(
                'user_id',
                $request->user()->id
            )
            ->latest()
            ->first();

            if ($newDefault) {
                $newDefault->update([
                    'is_default' => true
                ]);
            }
        }

        return $this->success(
            null,
            'Address deleted successfully.'
        );
    }

    /**
     * Set address as default
     */
    public function setDefault(
        Request $request,
        $id
    ) {
        $address = UserAddress::where(
            'user_id',
            $request->user()->id
        )
        ->findOrFail($id);

        DB::transaction(function () use (
            $request,
            $address
        ) {

            UserAddress::where(
                'user_id',
                $request->user()->id
            )
            ->update([
                'is_default' => false
            ]);

            $address->update([
                'is_default' => true
            ]);
        });

        return $this->success(
            $address->fresh(),
            'Default address updated successfully.'
        );
    }
}