<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

class ProductController extends Controller
{
    //public function store(Request $request)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $imagePath = $request->file('image')->store('product_images', 'public');

        $product = new Product([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'image' => $imagePath,
        ]);

        $product->save();

        return response()->json(['message' => 'Product created successfully'], 201);
    }
    public function index()
    {
        $products = Product::all();
        return response()->json($products, 200);
    }
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product, 200);
    }
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'price' => 'numeric',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($product->image);

            $imagePath = $request->file('image')->store('product_images', 'public');
            $product->image = $imagePath;
        }
        print_r($request->all());

        $product->name = $request->input('name', $product->name);
        $product->price = $request->input('price', $product->price);
        $product->save();

        return response()->json(['message' => 'Product updated successfully'], 200);
    }
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        Storage::disk('public')->delete($product->image);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
    public function getAllProducts(Request $request)
    {
        try {
            $keyword = $request->input('keyword');
            $sortField = $request->input('sort', 'id');
            $sortOrder = $request->input('order', 'asc');
            $page = $request->input('page', 0);
            $size = $request->input('size', 10);
            $query = Product::query();

            if (!empty($keyword)) {
                $query->where('name', 'like', "%{$keyword}%");
            }

            $query->orderBy($sortField, $sortOrder);


            $totalCount = $query->count();
            $products =
                $query->skip($page * $size)->take($size)->get();

            return response()->json([
                'items' => $products,
                'page' => $page,
                'pageSize' => $size,
                'totalCount' => $totalCount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
