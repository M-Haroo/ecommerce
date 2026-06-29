<?php

namespace App\Http\Controllers;


use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Slide;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Intervention\Image\Laravel\Facades\Image;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class AdminController extends Controller
{
     public function index(){
    $orders = Order::orderBy('created_at', 'DESC')->take(10)->get();

    $dashboardDatas = DB::select("
        SELECT 
            SUM(total) AS TotalAmount,
            SUM(IF(status='ordered', total, 0)) AS TotalOrderedAmount,
            SUM(IF(status='delivered', total, 0)) AS TotalDeliveredAmount,
            SUM(IF(status='canceled', total, 0)) AS TotalCanceledAmount,
            COUNT(*) AS Total,
            SUM(IF(status='ordered', 1, 0)) AS TotalOrdered,
            SUM(IF(status='delivered', 1, 0)) AS TotalDelivered,
            SUM(IF(status='canceled', 1, 0)) AS TotalCanceled
        FROM orders
    ");
   $monthlyDatas = DB::select("
            SELECT 
                M.id AS MonthNo,
                M.name AS MonthName,
                IFNULL(D.TotalAmount, 0) AS TotalAmount,
                IFNULL(D.TotalOrderedAmount, 0) AS TotalOrderedAmount,
                IFNULL(D.TotalDeliveredAmount, 0) AS TotalDeliveredAmount,
                IFNULL(D.TotalCanceledAmount, 0) AS TotalCanceledAmount
            FROM month_names M
            LEFT JOIN (
                SELECT 
                    DATE_FORMAT(created_at, '%b') AS MonthName,
                    MONTH(created_at) AS MonthNo,
                    SUM(total) AS TotalAmount,
                    SUM(IF(status='ordered', total, 0)) AS TotalOrderedAmount,
                    SUM(IF(status='delivered', total, 0)) AS TotalDeliveredAmount,
                    SUM(IF(status='canceled', total, 0)) AS TotalCanceledAmount
                FROM Orders 
                WHERE YEAR(created_at) = YEAR(NOW())
                GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
                ORDER BY MONTH(created_at)
            ) AS D ON D.MonthNo = M.id
");
$AmountM=implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
$OrderedAmountM=implode(',', collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
$DeliveredAmountM=implode(',', collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
$CanceledAmountM=implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

$TotalAmount= collect($monthlyDatas)->sum('TotalAmount');
$TotalOrderedAmount= collect($monthlyDatas)->sum('TotalOrderedAmount');
$TotalDeliveredAmount= collect($monthlyDatas)->sum('TotalDeliveredAmount');
$TotalCanceledAmount= collect($monthlyDatas)->sum('TotalCanceledAmount');
    return view('admin.index', compact('orders', 'dashboardDatas','AmountM','OrderedAmountM','DeliveredAmountM','CanceledAmountM','TotalAmount','TotalOrderedAmount','TotalDeliveredAmount','TotalCanceledAmount'));
}

    public function brands(){
        $brands=Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }
    public function add_brand(){
        return view('admin.brand-add');
    }
    public function brand_store(Request $request){
        $request->validate([
            'name'=> 'required',
            'slug'=> 'required|unique:brands,slug',
            'image'=> 'mimes:jpg,png,jpeg|max:2048'
        ]);
        $brands= new Brand();
        $brands->name= $request->name;
        $brands->slug= Str::slug($request->name);
        $image= $request->file('image');
        $file_extension=$request->file('image')->extension();
        $file_name=Carbon::now()->timestamp. '.'. $file_extension;
        $brands->image =$file_name;
        $image->move(public_path('uploads/brands'), $file_name);

        $brands->save();
        return redirect()->route('admin.brands')->with('status', 'Brand has been added successfully');
    
    }
    public function brand_edit($id){
        $brand=Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }
    public function brand_update(Request $request){
        $request->validate([
            'name'=> 'required',
            'slug'=> 'required|unique:brands,slug,'.$request->id,
            'image'=> 'mimes:jpg,png,jpeg|max:2048'
        ]);
        $brands=Brand::find($request->id);
        $brands->name= $request->name;
        $brands->slug= Str::slug($request->name);
        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/brands').'/'.$brands->image)){
                File::delete(public_path('uploads/brands').'/'.$brands->image);
            }
        $image= $request->file('image');
        $file_extension=$request->file('image')->extension();
        $file_name=Carbon::now()->timestamp. '.'. $file_extension;
        $brands->image =$file_name;
        $image->move(public_path('uploads/brands'), $file_name);
        }
        $brands->save();
        return redirect()->route('admin.brands')->with('status', 'Brand has been Updated successfully');
    }
    public function GenerateBrandThumbailsImage($image, $imageName){
        $destinationPath=public_path('uploads/brands');
        $img=Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->aspectRation();
        })->save($destinationPath. '/'.$imageName);
    }
    public function brand_delete($id){
        $brand=Brand::find($id);
        if(File::exists(public_path('uploads/brands').'/'.$brand->image)){
                File::delete(public_path('uploads/brands').'/'.$brand->image);
            }
            $brand->delete();
            return redirect()->route('admin.brands')->with('status','Brand has been deleted Successfully');
    }
    public function categories(){
        $categories=Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }
    public function add_category(){
        return view('admin.category-add');
    }
    public function category_store(Request $request){
        $request->validate([
            'name'=> 'required',
            'slug'=> 'required|unique:categories,slug',
            'image'=> 'mimes:jpg,png,jpeg|max:2048'
        ]);
        $category= new Category();
       $category->name= $request->name;
       $category->slug= Str::slug($request->name);
        $image= $request->file('image');
        $file_extension=$request->file('image')->extension();
        $file_name=Carbon::now()->timestamp. '.'. $file_extension;
       $category->image =$file_name;
        $image->move(public_path('uploads/categories'), $file_name);

       $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has been added successfully');
    
    }
    public function GenerateCategoryThumbailsImage($image, $imageName){
        $destinationPath=public_path('uploads/categories');
        $img=Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->aspectRation();
        })->save($destinationPath. '/'.$imageName);
    }
    public function category_edit($id){
        $category=Category::find($id);
        return view('admin.category-edit', compact('category'));
    }
    public function category_update(Request $request){
        $request->validate([
            'name'=> 'required',
            'slug'=> 'required|unique:categories,slug,'.$request->id,
            'image'=> 'mimes:jpg,png,jpeg|max:2048'
        ]);
        $category=Category::find($request->id);
        $category->name= $request->name;
        $category->slug= Str::slug($request->name);
        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/categories').'/'.$category->image)){
                File::delete(public_path('uploads/categories').'/'.$category->image);
            }
        $image= $request->file('image');
        $file_extension=$request->file('image')->extension();
        $file_name=Carbon::now()->timestamp. '.'. $file_extension;
        $category->image =$file_name;
        $image->move(public_path('uploads/categories'), $file_name);
        }
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Brand has been Updated successfully');
    }
    public function category_delete($id){
        $category=Category::find($id);
        if(File::exists(public_path('uploads/categories').'/'.$category->image)){
                File::delete(public_path('uploads/categories').'/'.$category->image);
            }
            $category->delete();
            return redirect()->route('admin.categories')->with('status','Brand has been deleted Successfully');
    }

    public function products()
{
    $products = Product::orderBy('created_at', 'DESC')->paginate(10);
    return view('admin.product', compact('products'));
}
    public function product_add(){
        $categories=Category::select('id', 'name')->orderBy('name')->get();
        $brands=Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories', 'brands'));
    }
    
//     public function product_store(Request $request){
//     $request->validate([
//         'name'=>'required',
//         'slug'=>'required|unique:products,slug',
//         'category_id'=>'required',
//         'brand_id'=>'required',            
//         'short_description'=>'required',
//         'description'=>'required',
//         'regular_price'=>'required',
//         'sale_price'=>'required',
//         'SKU'=>'required',
//         'stock_status'=>'required',
//         'featured'=>'required',
//         'quantity'=>'required',
//         'image'=>'required|mimes:png,jpg,jpeg|max:2048'            
//     ]);

//     $product = new Product();
//     $product->name = $request->name;
//     $product->slug = Str::slug($request->name);
//     $product->short_description = $request->short_description;
//     $product->description = $request->description;
//     $product->regular_price = $request->regular_price;
//     $product->sale_price = $request->sale_price;
//     $product->SKU = $request->SKU;
//     $product->stock_status = $request->stock_status;
//     $product->featured = $request->featured;
//     $product->quantity = $request->quantity;

//     $current_timestamp = Carbon::now()->timestamp;

//     if($request->hasFile('image')) {        
//         if (File::exists(public_path('uploads/products').'/'.$product->image)) {
//             File::delete(public_path('uploads/products').'/'.$product->image);
//         }
//         if (File::exists(public_path('uploads/products/thumbnails').'/'.$product->image)) {
//             File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
//         }            

//         $image = $request->file('image');
//         $imageName = $current_timestamp.'.'.$image->extension();
//         $this->GenerateProductThumbailsImage($image,$imageName);            
//         $product->image = $imageName;
//     }

//     $gallery_arr = array();
//     $gallery_images = '';
//     $counter = 1;

//     if($request->hasFile('images')) {
//         $oldGImages = explode(',', $product->images ?? '');
//         foreach($oldGImages as $gimage) {
//             if (File::exists(public_path('uploads/products').'/'.trim($gimage))) {
//                 File::delete(public_path('uploads/products').'/'.trim($gimage));
//             }
//             if (File::exists(public_path('uploads/products/thumbnails').'/'.trim($gimage))) {
//                 File::delete(public_path('uploads/products/thumbnails').'/'.trim($gimage));
//             }
//         }

//         $allowedfileExtension = ['jpg','png','jpeg'];
//         $files = $request->file('images');
//         foreach($files as $file){                
//             $gextension = $file->getClientOriginalExtension();                                
//             $check = in_array($gextension,$allowedfileExtension);            
//             if($check) {
//                 $gfilename = $current_timestamp . '-' . $counter . '.' . $gextension;   
//                 $this->GenerateProductThumbailsImage($file,$gfilename);                    
//                 array_push($gallery_arr,$gfilename);
//                 $counter = $counter + 1;
//             }
//         }
//         $gallery_images = implode(',', $gallery_arr);
//     }

//     $product->images = $gallery_images;
//     $product->category_id = $request->category_id;
//     $product->brand_id = $request->brand_id;
//     $product->save();

//     return redirect()->route('admin.products')->with('status','Product has been added successfully !');
// }

public function product_store(Request $request)
{
    $request->validate([
        'name'=>'required',
        'slug'=>'required|unique:products,slug',
        'category_id'=>'required',
        'brand_id'=>'required',
        'short_description'=>'required',
        'description'=>'required',
        'regular_price'=>'required|numeric',
        'sale_price'=>'required|numeric',
        'SKU'=>'required',
        'stock_status'=>'required',
        'featured'=>'required',
        'quantity'=>'required|integer',
        'image'=>'required|mimes:png,jpg,jpeg|max:2048',
        'images.*'=>'mimes:png,jpg,jpeg|max:2048'
    ]);

    $product = new Product();
    $product->name = $request->name;
    $product->slug = Str::slug($request->name);
    $product->short_description = $request->short_description;
    $product->description = $request->description;
    $product->regular_price = $request->regular_price;
    $product->sale_price = $request->sale_price;
    $product->SKU = $request->SKU;
    $product->stock_status = $request->stock_status;
    $product->featured = $request->featured;
    $product->quantity = $request->quantity;

    $current_timestamp = Carbon::now()->timestamp;

    // --- Ensure folders exist ---
    if(!File::exists(public_path('uploads/products'))){
        File::makeDirectory(public_path('uploads/products'), 0755, true);
    }
    if(!File::exists(public_path('uploads/products/thumbnails'))){
        File::makeDirectory(public_path('uploads/products/thumbnails'), 0755, true);
    }

    // --- Single main image ---
    if($request->hasFile('image')) {        
        $image = $request->file('image');
        $imageName = $current_timestamp.'.'.$image->extension();
        $this->GenerateProductThumbailsImage($image, $imageName);
        $product->image = $imageName;
    }

    // --- Gallery images ---
    $gallery_arr = [];
    if($request->hasFile('images')) {
        $counter = 1;
        foreach($request->file('images') as $file){
            $gextension = $file->getClientOriginalExtension();
            $gfilename = $current_timestamp . '-' . $counter . '.' . $gextension;   
            $this->GenerateProductThumbailsImage($file, $gfilename);
            $gallery_arr[] = $gfilename;
            $counter++;
        }
    }

    $product->images = implode(',', $gallery_arr);
    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;
    $product->save();

    return redirect()->route('admin.products')->with('status','Product has been added successfully !');
}

public function GenerateProductThumbailsImage($image, $imageName){
    $destinationPathThumbnail = public_path('uploads/products/thumbnails');
    $destinationPath = public_path('uploads/products');
    $img = Image::read($image->path());
    $img->cover(540,689,"top");
    $img->resize(540,689,function($constraint){
        $constraint->aspectRatio();
    })->save($destinationPath. '/'.$imageName);

    $img->resize(104,104,function($constraint){
        $constraint->aspectRatio();
    })->save($destinationPathThumbnail . '/' . $imageName);
}

public function edit_product($id)
{
    $product = Product::find($id);
    $categories = Category::Select('id','name')->orderBy('name')->get();
    $brands = Brand::Select('id','name')->orderBy('name')->get();
    return view('admin.product-edit',compact('product','categories','brands'));
}
public function update_product(Request $request)
{
    $request->validate([
        'name'=>'required',
        'slug'=>'required|unique:products,slug,'.$request->id,
        'category_id'=>'required',
        'brand_id'=>'required',            
        'short_description'=>'required',
        'description'=>'required',
        'regular_price'=>'required',
        'sale_price'=>'required',
        'SKU'=>'required',
        'stock_status'=>'required',
        'featured'=>'required',
        'quantity'=>'required',
        'image'=>'nullable|mimes:png,jpg,jpeg|max:2048',  // <-- updated
    ]);
    
    $product = Product::find($request->id);
    $product->name = $request->name;
    $product->slug = Str::slug($request->name);
    $product->short_description = $request->short_description;
    $product->description = $request->description;
    $product->regular_price = $request->regular_price;
    $product->sale_price = $request->sale_price;
    $product->SKU = $request->SKU;
    $product->stock_status = $request->stock_status;
    $product->featured = $request->featured;
    $product->quantity = $request->quantity;
    $current_timestamp = Carbon::now()->timestamp;
    
    // --- Single main image ---
    if($request->hasFile('image')) {        
        $image = $request->file('image');
        $imageName = $current_timestamp.'.'.$image->extension();
        $this->GenerateProductThumbailsImage($image, $imageName);
        $product->image = $imageName;
    }

    // --- Gallery images ---
    if($request->hasFile('images')) {
        $gallery_arr = [];
        $counter = 1;
        foreach($request->file('images') as $file){
            $gextension = $file->getClientOriginalExtension();
            $gfilename = $current_timestamp . '-' . $counter . '.' . $gextension;   
            $this->GenerateProductThumbailsImage($file, $gfilename);
            $gallery_arr[] = $gfilename;
            $counter++;
        }
        $product->images = implode(',', $gallery_arr);
    }

    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;
    $product->save();       
    return redirect()->route('admin.products')->with('status','Product has been updated successfully!');
}

    public function delete_product($id)
    {
        $product = Product::find($id);        
        $product->delete();
        return redirect()->route('admin.products')->with('status','Product has been deleted successfully !');
    } 

    public function coupon() {
    $coupons = Coupon::orderBy('expiry_date', 'DESC')->paginate(12);
    return view('admin.coupon', compact('coupons'));
}

public function add_coupon()
{        
    return view('admin.coupon-add');
}
public function add_coupon_store(Request $request)
{
    $request->validate([
        'code' => 'required',
        'type' => 'required',
        'value' => 'required|numeric',
        'cart_value' => 'required|numeric',
        'expiry_date' => 'required|date'
    ]);

    $coupon = new Coupon();
    $coupon->code = $request->code;
    $coupon->type = $request->type;
    $coupon->value = $request->value;
    $coupon->cart_value = $request->cart_value;
    $coupon->expiry_date = $request->expiry_date;
    $coupon->save();

    return redirect()->route('admin.coupons')->with('status', 'New Coupon has been added successfully !');
}

public function edit_coupon($id)
{
       $coupon = Coupon::find($id);
       return view('admin.coupon-edit',compact('coupon'));
}

public function update_coupon(Request $request)
{
       $request->validate([
       'code' => 'required',
       'type' => 'required',
       'value' => 'required|numeric',
       'cart_value' => 'required|numeric',
       'expiry_date' => 'required|date'
       ]);
       $coupon = Coupon::find($request->id);
       $coupon->code = $request->code;
       $coupon->type = $request->type;
       $coupon->value = $request->value;
       $coupon->cart_value = $request->cart_value;
       $coupon->expiry_date = $request->expiry_date;               
       $coupon->save();           
       return redirect()->route('admin.coupons')->with('status','Coupon has been updated successfully !');
}
public function delete_coupon($id)
{
        $coupon = Coupon::find($id);        
        $coupon->delete();
        return redirect()->route('admin.coupons')->with('status','Coupon has been deleted successfully !');
}
public function orders(){
    $orders=Order::orderBy('created_at', 'DESC')->paginate(12);
    return view('admin.order', compact('orders'));
}
public function order_details($order_id){
    $order = Order::find($order_id);
      $orderitems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
      $transaction = Transaction::where('order_id',$order_id)->first();
      return view('admin.order-details',compact('order','orderitems','transaction'));
}
public function update_order_status(Request $request)
{        
    $order = Order::find($request->order_id);
    if (!$order) {
        return back()->with('status', 'Order not found!');
    }

    $order->status = $request->order_status;

    if ($request->order_status == 'delivered') {
        $order->delivered_date = Carbon::now();
    } elseif ($request->order_status == 'canceled') {
        $order->canceled_date = Carbon::now();
    }        

    $order->save();

    if ($request->order_status == 'delivered') {
        $transaction = Transaction::where('order_id', $request->order_id)->first();
        if ($transaction) {
            $transaction->status = 'approved';
            $transaction->save();
        }
    }

    return back()->with('status', 'Status changed successfully!');
}
public function slides(){
    $slides=Slide::orderBy('id', 'DESC')->paginate(12);
    return view ('admin.slides', compact('slides'));
}
public function slide_add(){
    return view('admin.slide-add');
}
public function slide_store(Request $request)
{
    $request->validate([
        'tagline'   => 'required',
        'title'     => 'required',
        'subtitle'  => 'required',
        'link'      => 'required',
        'status'    => 'required',
        'image'     => 'required|mimes:png,jpg,jpeg|max:2048'
    ]);

    $slide = new Slide();
    $slide->tagline  = $request->tagline;
    $slide->title    = $request->title;
    $slide->subtitle = $request->subtitle;
    $slide->link     = $request->link;
    $slide->status   = $request->status;
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateSlideThumbnailsImage($image, $file_name);
        $slide->image = $file_name;
    }
    $slide->save();
    return redirect()->route('admin.slides')->with('status', 'Slide added successfully!');
}
public function GenerateSlideThumbnailsImage($image, $imageName)
{
    $destinationPath = public_path('uploads/slides');
    if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0777, true);
    }
    $img = Image::read($image->path());
    $img->cover(400, 690, 'top');
    $img->resize(400, 690, function ($constraint) {
        $constraint->aspectRatio();
    });
    $img->save($destinationPath . '/' . $imageName);
}
public function slide_edit($id){
    $slide=Slide::find($id);
    return view('admin.slide-edit',compact('slide'));
}
public function slide_update(Request $request)
{
    $request->validate([
        'tagline'   => 'required',
        'title'     => 'required',
        'subtitle'  => 'required',
        'link'      => 'required',
        'status'    => 'required',
        'image'     => 'mimes:png,jpg,jpeg|max:2048'
    ]);

    $slide = Slide::find($request->id);
    $slide->tagline  = $request->tagline;
    $slide->title    = $request->title;
    $slide->subtitle = $request->subtitle;
    $slide->link     = $request->link;
    $slide->status   = $request->status;
    if ($request->hasFile('image')) {
       if (File::exists(public_path('uploads/slids/' . $slide->image))) {
            File::delete(public_path('uploads/slids/' . $slide->image));
        }

        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateSlideThumbnailsImage($image, $file_name);
        $slide->image = $file_name;
    }
    $slide->save();
    return redirect()->route('admin.slides')->with('status', 'Slide Updated successfully!');
}
public function slide_delete($id){
    $slide=Slide::find($id);
    if (File::exists(public_path('uploads/slids/' . $slide->image))) {
            File::delete(public_path('uploads/slids/' . $slide->image));
        }
        $slide->delete();
         return redirect()->route('admin.slides')->with('status', 'Slide Delete successfully!');
}
public function contacts(){
    $contacts=Contact::orderBy('created_at', 'DESC')->paginate(10);
    return view('admin.contacts', compact('contacts'));
}
public function contact_delete($id){
    $contact=Contact::find($id);
    $contact->delete();
    return redirect()->route('admin.contacts')->with('status', 'Contact delete Successfully!');
}
 public function search(Request $request){
        $query=$request->input('query');
        $resutls=Product::where('name', 'LIKE', "%{$query}%")->get()->take(8);
        return response()->json($resutls);
    }
} 
