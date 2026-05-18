<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('name')->paginate(20);
        $totalProducts = Product::count();

        return view('products.index', compact('products', 'totalProducts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // 'reference' => 'nullable|string|max:100',
            'prix_ttc' => 'required|numeric|min:0.01',
            'unite' => 'required|string|max:50'
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')
            ->with('success', 'Produit ajouté avec succès!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produit supprimé avec succès!');
    }
}
