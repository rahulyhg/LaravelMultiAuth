<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\File;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

use App\Models\Product;
use App\Models\Common;

use Session;
use DB;


class ProductController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('admin-auth');
    }

	public function index()
    {             
                       
	  $data['products_list'] = DB::table('products As p')
                             ->Join('categories As c', 'p.category_row_id', '=', 'c.category_row_id')                  
                             ->select('p.*', 'c.category_name')       
                             ->orderBy('p.product_row_id', 'desc')
                             ->take(20) 
                             ->get();
      $common_model = new Common();                         
      $data['categories_list'] = $common_model->allCategories(); 
                                   
	  return view('admin.product.index', ['data'=>$data]);
        //   
    }
	
	function create()
	{
      $common_model = new Common();                         
      $data['categories_list'] = $common_model->allCategories();       
	  return view('admin.product.create', ['data'=>$data]);
	   
	}
	
	public function store(Request $request)
    {
      $this->validate($request, [
            'product_name' => 'required',
            'category_row_id' => 'required',
        ]); 
     
        $product_model = new Product();        
        $product_model->product_name = $request->product_name;  
        $product_model->product_price = $request->product_price;  
        $product_model->product_sku = $request->product_sku;  
        $product_model->category_row_id = $request->category_row_id;   
        $product_model->is_featured = $request->is_featured ? 1 : 0;
        $product_model->is_latest = $request->is_latest ? 1 : 0;
        
        $common_model = new Common();       
        $product_model->product_image = $common_model->uploadImage('product_image', 'uploads/products');  
                
        $product_model->save();
        Session::flash('success-message', 'Successfully Performed !');        
        return Redirect::to('/admin/products');
        
	}
    
	public function edit($id)
    {
	    $common_model = new Common();       
        $data['categories_list'] = $common_model->allCategories();     
        $data['single_info'] = DB::table('products')->where('product_row_id', $id)->first(); 
        return view('admin.product.edit', ['data'=>$data]); 
	}
	public function update(Request $request)
    {
        
        $this->validate($request, [
            'product_name' => 'required',
            'category_row_id' => 'required',
        ]); 
     
        $product_model = new Product();        
        
        if( !$request->hidden_row_id ) {
            return false;        
        }                                                                      
        $product_model = $product_model->find($request->hidden_row_id);
        
        
        $product_model->product_name = $request->product_name;  
        $product_model->product_price = $request->product_price;  
        $product_model->product_sku = $request->product_sku;  
        $product_model->category_row_id = $request->category_row_id;   
        $product_model->is_featured = $request->is_featured ? 1 : 0;
        $product_model->is_latest = $request->is_latest ? 1 : 0;
        
        $common_model = new Common();       
        if($request->product_image) {
        $product_model->product_image = $common_model->uploadImage('product_image', 'uploads/products');  
        }
                
        $product_model->save();
        Session::flash('success-message', 'Successfully Performed !');        
        return Redirect::to('/admin/products');
	
	}
    
     public function deleteRecord($id)
    {
       if( !$id ) { 
        return false;
       }
       
       // main category Cannnot be deleted if it has child
       
                                                
       DB::table('products')->where('product_row_id', $id)->delete(); 
       Session::flash('success-message', 'Successfully Performed !');        
       return Redirect::to('/admin/products');
    }
    
    public function deleteImageOnly($product_row_id, $file_name )
    {
        
       
        $product_model = new Product();
      
        if(File::exists(public_path().'/uploads/products/' . $file_name)) 
        {
            File::delete(public_path().'/uploads/products/' . $file_name);                        
        }    
        
        if($product_row_id)
        {
            $product_model = $product_model->find($product_row_id);
            $product_model->product_image = '';        
            $product_model->save();
        }        
        
    }
    
    
}