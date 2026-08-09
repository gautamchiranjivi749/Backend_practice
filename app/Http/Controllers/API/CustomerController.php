<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    use ApiResponse;

    /**
     * Get all customers
     */
    public function index(Request $request)
    {
        $customers = User::where('role', 'user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $customers->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $customers->where('status', $request->status);
        }

        $customers = $customers
            ->latest()
            ->paginate(10);

        return $this->success(
            $customers,
            'Customers fetched successfully.'
        );
    }

    /**
     * Get single customer
     */
    public function show($id)
    {
        $customer = User::where('role', 'user')
            ->findOrFail($id);

        return $this->success(
            $customer,
            'Customer fetched successfully.'
        );
    }

    /**
     * Update customer
     */
    public function update(Request $request, $id)
    {
        $customer = User::where('role', 'user')
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',

            'email' => 'sometimes|required|email|unique:users,email,' . $customer->id,

            'password' => 'sometimes|nullable|min:8',

            'status' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make(
                $validated['password']
            );
        }

        $customer->update($validated);

        return $this->success(
            $customer->fresh(),
            'Customer updated successfully.'
        );
    }

    /**
     * Delete customer
     */
    public function destroy($id)
    {
        $customer = User::where('role', 'user')
            ->findOrFail($id);

        $customer->delete();

        return $this->success(
            null,
            'Customer deleted successfully.'
        );
    }

    /**
     * Activate / deactivate customer
     */
    public function status(Request $request, $id)
    {
        $customer = User::where('role', 'user')
            ->findOrFail($id);

        $request->validate([
            'status' => 'required|boolean',
        ]);

        $customer->update([
            'status' => $request->status,
        ]);

        return $this->success(
            $customer->fresh(),
            'Customer status updated successfully.'
        );
    }
}
