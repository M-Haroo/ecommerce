<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
  public function index(Request $request)
{     
    $size = $request->query('size') ? $request->query('size') : 12;
    $o_column = "";
    $o_order = "";
    $order = $request->query('order') ? $request->query('order') : -1;
    $f_brands = $request->query('brands');
    $f_categories = $request->query('categories');

    switch ($order) {
        case 1:
            $o_column = "created_at";
            $o_order = "DESC";
            break;
        case 2:
            $o_column = "created_at";
            $o_order = "ASC";
            break;
        case 3:
            $o_column = "sale_price";
            $o_order = "ASC";
            break;
        case 4: // previously duplicate 1 removed
            $o_column = "sale_price";
            $o_order = "DESC";
            break;
        default:
            $o_column = 'id';
            $o_order = 'DESC';
    }

    $brands = Brand::orderBy('name', 'ASC')->get();
    $categories = Category::orderBy('name','ASC')->get();

    // Ensure min/max prices are within allowed range
    $min_price = $request->query('min') ? $request->query('min') : 1;
    $max_price = $request->query('max') ? $request->query('max') : 2500;
    $max_price = min($max_price, 2500); // force maximum 2500

    $products = Product::where(function($qeury) use($f_brands){
        if (!empty($f_brands)) {
            $qeury->whereIn('brand_id', explode(',', $f_brands));
        }
    })
    ->where(function($qeury) use($f_categories){
        if (!empty($f_categories)) {
            $qeury->whereIn('category_id', explode(',', $f_categories));
        }
    })
    ->where(function($qeury) use ($min_price, $max_price){
        // Properly group OR conditions to fix max price issue
        $qeury->where(function($q) use($min_price, $max_price){
            $q->whereBetween('regular_price', [$min_price, $max_price])
              ->orWhereBetween('sale_price', [$min_price, $max_price]);
        });
    })
    ->orderBy($o_column, $o_order)
    ->paginate($size);

    return view('shop', compact(
        'products', 'size', 'order', 'brands', 'f_brands',
        'categories', 'f_categories', 'min_price', 'max_price'
    ));
}

public function product_details($product_slug)
{
    $product = Product::where('slug', $product_slug)->first();
    $rproducts = Product::where('slug', '<>', $product_slug)->take(8)->get();
    return view('details', compact('product', 'rproducts'));
    
}



}
