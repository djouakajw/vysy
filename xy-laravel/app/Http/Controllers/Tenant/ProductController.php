<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $tenant = $request->user()->primaryTenant ?? $request->user()->tenants()->first();
        $products = $tenant->products()->orderBy('sort_order')->get();

        return view('tenant.products', compact('tenant', 'products'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'category' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tenant = Tenant::where('id', $request->tenant_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $data = [
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'currency' => $request->currency ?? 'XOF',
            'category' => $request->category,
            'sort_order' => $tenant->products()->max('sort_order') + 1,
            'is_active' => true,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/products/' . $tenant->id, 'public');
            $data['image_url'] = Storage::url($path);
        }

        $product = $tenant->products()->create($data);

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'category' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $product->update($request->only([
            'name', 'description', 'price', 'currency', 'category', 'is_active', 'sort_order'
        ]));

        return response()->json(['success' => true, 'product' => $product]);
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        return response()->json(['success' => true]);
    }
}
