<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'prix_ttc' => 'required|numeric|min:0',
            'unite' => 'required|string'
        ]);

        Product::create($request->all());

        return redirect()->route('invoices.index')
            ->with('success', 'Produit ajouté avec succès!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Produit supprimé avec succès!');
    }
}