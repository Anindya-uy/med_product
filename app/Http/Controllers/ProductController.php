<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $products = Product::with('prices')->paginate(2);
        $productVariants = ProductVariant::all();
        return view('products.index',compact(['products','productVariants']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    //Searching

    public function search(Request $request)
    {
        // can be filtered by 1 or more field
        $title = $request->title;
        $variant = $request->variant;
        $price_from = $request->price_from;
        $price_to = $request->price_to;
        $date = $request->date;

        $range = [$price_from, $price_to, $variant];

        $productVariants = ProductVariant::all();

            $products = Product::with('prices')
                ->when($title, function ($query, $title) {
                    return $query->where('title', 'like', '%'.$title.'%');
                })
                ->when($date, function ($query, $date) {
                    return $query->whereDate('created_at', $date);
                })->whereHas('prices', function($q) use($range){

                    $price_from = $range[0] ;
                    $price_to = $range[1] ;
                    $variant = $range[2] ;

                    $q->when($price_from, function ($query, $price_from) {
                        return $query->where('price', '>=', intval($price_from));
                    })->when($price_to, function ($query, $price_to) {
                        return $query->where('price', '<=', intval($price_to));
                    })->when($variant, function ($query, $variant) {
                        return $query->whereRaw("(product_variant_1 = $variant or product_variant_2 = $variant or product_variant_3 = $variant)");
                    });
                })->paginate(2);
            $products->appends($request->all());

        return view('products.index', compact('products', 'productVariants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        $product = Product::create(['title' => $request->title, 'sku' => $request->sku, 'description' =>$request->description]);

            $product_image = new ProductImage();
            if($request->hasFile('product_image')){
                foreach($request->file('product_image') as $img){
                    $file = $img;
                    $filename = time().'-'.uniqid().'.'.$file->getClientOriginalExtension();
                    $file->move(public_path('uploads/products'), $filename);
                    // save filename to database
                    $product_image->create(['product_id' => $product->id, 'file_path' => $filename]);
                }
            }

            $product_variant = new ProductVariant();
                foreach($request->product_variant as $variant){
                    $variant = json_decode($variant);
                    foreach($variant->tags as $tag){
                        $product_variant->create(['variant'=>$tag, 'variant_id'=>$variant->option, 'product_id'=>$product->id]);
                    }
                }

            foreach($request->product_variant_prices as $price){
                $pv_prices = new ProductVariantPrice();
                $price = json_decode($price);
                $attrs = explode("/", $price->title);

                $product_variant_ids= [];
                    for( $i=0; $i<count($attrs)-1; $i++){
                        $product_variant_ids[] = ProductVariant::select('id')->where('variant', $attrs[$i])->latest()->first()->id;
                    }

                    for( $i=1; $i<=count($product_variant_ids); $i++){
                        $pv_prices->{'product_variant_'.$i} = $product_variant_ids[$i-1];
                    }
                $pv_prices->price = $price->price;
                $pv_prices->stock = $price->stock;
                $pv_prices->product_id = $product->id;
                $pv_prices->save();
            }
        
        return response('product added successfully!');

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
    private function removeImage($p_id)
    {
        $product_image = ProductImage::where('product_id', $p_id)->get();
        foreach($product_image as $img){
            if($img->file_path != "" && \File::exists('uploads/products/' . $img->file_path)) {
                @unlink(public_path('uploads/products/' . $img->file_path));
            }
        }
        ProductImage::where('product_id', $p_id)->delete();
    }
}
