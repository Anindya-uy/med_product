@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>

        {{-- {{ dd($productVariants) }} --}}
        
    <div class="card">
        <form action="{{route('search')}}" method="get" class="card-header">
            @csrf
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="select" class="form-control">
                        <option value="" selected>Select variant</option>
                    @foreach($productVariants as $data)
                        <option value="{{$data->id}}">{{$data->variant}}</option>
                    @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From" class="form-control">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th width="150px">Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th >Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($products as $key=>$product)
                        {{-- dd($product->prices) --}}
                        <tr>
                            <td>{{ $key +1 }}</td>
                            <td>{{ $product->title }} <br> {{ date('d-M-Y', strtotime($product->created_at)) }}</td>
                            <td>{{ Str::limit($product->description,'50','...') }}</td>
                            <td>
                                
                                <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant">
                                    @foreach($product->prices as $key=>$price)
                                     <dt class="col-sm-3 pb-0">
                                        {{ $price->product_variant_one }}/
                                        {{ $price->product_variant_two }}/
                                        {{ $price->product_variant_three }}
                                    </dt>
                                    <dd class="col-sm-9">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-4 pb-0">Price : {{ number_format($price->price, 2) }}</dt>
                                            <dd class="col-sm-8 pb-0">InStock : {{ number_format($price->stock,2) }}</dd>
                                        </dl>
                                    
                                    </dd>
                                    @endforeach
                                </dl>
                                
                                <button onclick="$('#variant').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>

                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('product.edit', 1) }}" class="btn btn-success">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>

                </table>
            </div>
        </div>

        
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <div>
                    @php
                        $show = $products->perPage() * ($products->currentPage()-1) + 1;
                        $to = $products->perPage() * $products->currentPage();
                        $sum = $products->total();
                    @endphp
                    <p>
                        Showing {{$show > $sum ? $sum : $show}}
                        to 
                        {{$to > $sum ? $sum : $to}}
                        out of 
                        {{$sum}}
                    </p>
                </div>
                <div>
                    {{ $products->links()}}
                </div>
            </div>
        </div>
    </div>
    

@endsection
