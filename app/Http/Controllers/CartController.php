<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Surfsidemedia\Shoppingcart\Facades\Cart; 

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::instance('cart')->content();
        return view('cart', compact('items'));
    }

    public function add_to_card(Request $request){
        Cart::instance('cart')->add($request->id, $request->name,$request->quantity,$request->price)->associate('App\Models\Product');
        return redirect()->back();

    }
    public function increase_item_quantity($rowId)
{
    $product = Cart::instance('cart')->get($rowId);
    $qty = $product->qty + 1;
    Cart::instance('cart')->update($rowId,$qty);
    return redirect()->back();
}
public function reduce_item_quantity($rowId){
    $product = Cart::instance('cart')->get($rowId);
    $qty = $product->qty - 1;
    Cart::instance('cart')->update($rowId,$qty);
    return redirect()->back();
}

public function remove_item_from_cart($rowId)
{
    Cart::instance('cart')->remove($rowId);
    return redirect()->back();
}

public function empty_cart()
{
    Cart::instance('cart')->destroy();
    return redirect()->back();
}
public function apply_coupon_code(Request $request)
{        
    $coupon_code = $request->coupon_code;
    if(isset($coupon_code))
    {
        $cart_total = floatval(str_replace(',', '', Cart::instance('cart')->subtotal()));

        $coupon = Coupon::where('code', $coupon_code)
            ->where('expiry_date','>=',Carbon::today())
            ->where('cart_value','<=', $cart_total)
            ->first();

        if(!$coupon)
        {
            return redirect()->back()->with('error','Invalid coupon code!');
        }

        session()->put('coupon',[
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'cart_value' => $coupon->cart_value
        ]);

        $this->calculateDiscounts();
        return redirect()->back()->with('success','Coupon code has been applied!');
    }
    else{
        return redirect()->back()->with('error','Invalid coupon code!');
    }        
}

public function calculateDiscounts()
{
    $discount = 0;
    if(session()->has('coupon'))
    {
        // Convert subtotal to numeric (remove commas)
        $cart_subtotal = floatval(str_replace(',', '', Cart::instance('cart')->subtotal()));

        if(session()->get('coupon')['type'] == 'fixed')
        {
            $discount = session()->get('coupon')['value'];
        }
        else
        {
            $discount = ($cart_subtotal * session()->get('coupon')['value']) / 100;
        }

        $subtotalAfterDiscount = $cart_subtotal - $discount;
        $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax')) / 100;
        $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount; 

        Session::put('discounts', [
            'discount' => number_format(floatval($discount), 2, '.', ''),
            'subtotal' => number_format(floatval($subtotalAfterDiscount), 2, '.', ''),
            'tax' => number_format(floatval($taxAfterDiscount), 2, '.', ''),
            'total' => number_format(floatval($totalAfterDiscount), 2, '.', '')
        ]);            
    }
}


public function remove_coupon_code()
{
    session()->forget('coupon');
    session()->forget('discounts');
    return back()->with('success','Coupon has been removed!');
}
public function checkout()
{
    if(!Auth::check())
    {
        return redirect()->route('login');
    }

    $address = Address::where('user_id', Auth::user()->id)
                      ->where('isdefault', 1)
                      ->first();              

    return view('checkout', compact('address'));
}

public function place_an_order(Request $request){
    $user_id = Auth::user()->id;
    $address = Address::where('user_id',$user_id)->where('isdefault',true)->first();
    if(!$address)
    {
        $request->validate([                
            'name' => 'required|max:100',
            'phone' => 'required|numeric|digits:11',
            'zip' => 'required|numeric|digits:6',
            'state' => 'required',
            'city' => 'required',
            'address' => 'required',
            'locality' => 'required',
            'landmark' => 'required'           
        ]);
        $address = new Address();    
        $address->user_id = $user_id;    
        $address->name = $request->name;
        $address->phone = $request->phone;
        $address->zip = $request->zip;
        $address->state = $request->state;
        $address->city = $request->city;
        $address->address = $request->address;
        $address->locality = $request->locality;
        $address->landmark = $request->landmark;
        $address->country = 'Pakistan';
        $address->isdefault = true;
        $address->save();
    }
   $this->setAmountForCheckout();
   $order = new Order();
    $order->user_id = $user_id;

    // Remove commas before saving to DB
    $order->subtotal = floatval(str_replace(',', '', session('checkout')['subtotal'] ?? 0));
    $order->discount = floatval(str_replace(',', '', session('checkout')['discount'] ?? 0));
    $order->tax = floatval(str_replace(',', '', session('checkout')['tax'] ?? 0));
    $order->total = floatval(str_replace(',', '', session('checkout')['total'] ?? 0));

    $order->name = $address->name;
    $order->phone = $address->phone;
    $order->locality = $address->locality;
    $order->address = $address->address;
    $order->city = $address->city;
    $order->state = $address->state;
    $order->country = $address->country;
    $order->landmark = $address->landmark;
    $order->zip = $address->zip;
    $order->save(); 

    foreach(Cart::instance('cart')->content() as $item)
    {
        $orderitem = new OrderItem();
        $orderitem->product_id = $item->id;
        $orderitem->order_id = $order->id;
        $orderitem->price = $item->price;
        $orderitem->quantity = $item->qty;
        $orderitem->save();                   
    }
    if($request->mode == "card"){
        //
    }
    elseif($request->mode == "paypal"){
        //
    }
    elseif($request->mode == "cod"){
     $transaction = new Transaction();
    $transaction->user_id = $user_id;
    $transaction->order_id = $order->id;
    $transaction->mode = $request->mode;
    $transaction->status = 'pending';
    $transaction->save();
    }
   
    
    Cart::instance('cart')->destroy();
    session()->forget('checkout');
    session()->forget('coupon');
    session()->forget('discounts');
    session()->put('order_id',$order->id);
    return redirect()->route('cart.order-confirmation');
}

public function setAmountForCheckout()
{ 
    if(!Cart::instance('cart')->content()->count() > 0)
    {
        session()->forget('checkout');
        return;
    }   
     
    if(session()->has('coupon'))
    {
        session()->put('checkout',[
            'discount' => session()->get('discounts')['discount'],
            'subtotal' =>  session()->get('discounts')['subtotal'],
            'tax' =>  session()->get('discounts')['tax'],
            'total' =>  session()->get('discounts')['total']
        ]);
    }
    else
    {
        session()->put('checkout',[
            'discount' => 0,
            'subtotal' => Cart::instance('cart')->subtotal(),
            'tax' => Cart::instance('cart')->tax(),
            'total' => Cart::instance('cart')->total()
        ]);
    }
}
public function order_confirmation(){
    if(session::has('order_id')){
        $order=Order::find(session::get('order_id'));
         return view('order-confirmation', compact('order'));
    }
    return redirect()->route('cart.index');
}
}
// public function setAmountforCheckout(){
//     if(!Cart::instance('cart')->content()->count()>0){
//         Session::forget('checkout');
//         return;
//     }
//     if(Session::has('coupon')){
//         Session::put('checkout',[
//             'discount' => Session::get('discounts')['discount'],
//             'subtotal' => Session::get('discounts')['subtotal'],
//             'tax' => Session::get('discounts')['taxt'],
//             'total' => Session::get('discounts')['total'],
//         ]);
//     }
//     else{
//         Session::put('checkout',[
//             'discount' => 0,
//             'subtotal' => Cart::instance('cart')->subtotal(),
//             'tax' => Session::get('discounts')['taxt'],
//             'total' => Session::get('discounts')['total'],
//         ]);
//     }
// }