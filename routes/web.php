<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/shop',[ShopController::class,'index'])->name('shop.index');
Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.product.details');
Route::get('/cart',[CartController::class,'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add_to_card'])->name('cart.add');

Route::put('/cart/increase-qunatity/{rowId}',[CartController::class,'increase_item_quantity'])->name('cart.increase.qty');
Route::put('/cart/reduce-qunatity/{rowId}',[CartController::class,'reduce_item_quantity'])->name('cart.decrease.qty');

Route::delete('/cart/remove/{rowId}',[CartController::class,'remove_item_from_cart'])->name('cart.item.remove');

Route::delete('/cart/clear',[CartController::class,'empty_cart'])->name('cart.empty');

Route::post('/wishlist/add',[WishlistController::class,'add_to_wishlist'])->name('wishlist.add');

Route::get('/wishlist',[WishlistController::class,'index'])->name('wishlist.index');

Route::delete('/wishlist/remove/{rowId}',[WishlistController::class,'remove_item_from_wishlist'])->name('wishlist.item.remove');

Route::post('/wishlist/move-to-cart/{rowId}',[WishlistController::class,'move_to_cart'])->name('wishlist.move.to.cart');

Route::delete('/wishlist/clear',[WishlistController::class,'empty_wishlist'])->name('wishlist.items.clear');

Route::post('/cart/apply-coupon',[CartController::class,'apply_coupon_code'])->name('cart.coupon.apply');
Route::delete('/cart/remove-coupon', [CartController::class, 'remove_coupon_code'])->name('cart.coupon.remove');
Route::get('/checkout',[CartController::class,'checkout'])->name('cart.checkout');
Route::post('/place-an-order',[CartController::class,'place_an_order'])->name('cart.place.an.order');
Route::get('/order_confirmation',[CartController::class, 'order_confirmation'])->name('cart.order-confirmation');
Route::middleware(['auth'])->group(function(){
   Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
Route::get('/account-orders',[UserController::class,'account_orders'])->name('user.orders');
Route::get('/account-order-detials/{order_id}',[UserController::class,'account_order_details'])->name('user.acccount.order.details');
Route::put('/account-order/cancel-order',[UserController::class,'account_cancel_order'])->name('user.account_cancel_order');
});
Route::get('/contact-us',[HomeController::class, 'contact'])->name('home.contact');
Route::post('/contact/store',[HomeController::class, 'contact_store'])->name('home.contact.store');
Route::get('/search', [HomeController::class, 'search'])->name('home.search');
Route::middleware(['auth', AuthAdmin::class])->group(function(){
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/brands', [AdminController::class, 'brands'])->name('admin.brands');
    Route::get('/admin/brands/addBrand', [AdminController::class, 'add_brand'])->name('admin.brand.add');
    Route::post('/admin/brands/store', [AdminController::class, 'brand_store'])->name('admin.brand.store');
     Route::get('/admin/brands/edit/{id}', [AdminController::class, 'brand_edit'])->name('admin.brand.edit');
     Route::put('/admin/brands/update', [AdminController::class, 'brand_update'])->name('admin.brand.update');
     Route::delete('/admin/brands/{id}/delete', [AdminController::class, 'brand_delete'])->name('admin.brand.delete');

     Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
     Route::get('/admin/brands/addCategory', [AdminController::class, 'add_category'])->name('admin.category.add');

     Route::post('/admin/categories/store', [AdminController::class, 'category_store'])->name('admin.category.store');
     
      Route::get('/admin/categories/edit/{id}', [AdminController::class, 'category_edit'])->name('admin.category.edit');

      Route::put('/admin/categories/update', [AdminController::class, 'category_update'])->name('admin.category.update');

       Route::delete('/admin/categories/{id}/delete', [AdminController::class, 'category_delete'])->name('admin.category.delete');

       Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');

       Route::get('/admin/products/addProduct', [AdminController::class, 'product_add'])->name('admin.product.add');

       Route::post('/admin/products/store', [AdminController::class, 'product_store'])->name('admin.product.store');

       Route::get('/admin/products/{id}/edit',[AdminController::class,'edit_product'])->name('admin.product.edit');

       Route::put('/admin/products/update',[AdminController::class,'update_product'])->name('admin.product.update');

       Route::delete('/admin/product/{id}/delete',[AdminController::class,'delete_product'])->name('admin.product.delete');

       Route::get('admin/coupons',[AdminController::class, 'coupon'])->name('admin.coupons');
       Route::get('/admin/coupon/add',[AdminController::class,'add_coupon'])->name('admin.coupon.add');
       
      Route::post('/admin/coupon/store',[AdminController::class,'add_coupon_store'])->name('admin.coupon.store');
      Route::get('/admin/coupon/{id}/edit',[AdminController::class,'edit_coupon'])->name('admin.coupon.edit');
      
      Route::put('/admin/coupon/update',[AdminController::class,'update_coupon'])->name('admin.coupon.update');
      Route::delete('/admin/coupon/{id}/delete',[AdminController::class,'delete_coupon'])->name('admin.coupon.delete');
      Route::get('/admin/orders',[AdminController::class, 'orders'])->name('admin.orders');
      Route::get('/admin/order/{order_id}/details',[AdminController::class, 'order_details'])->name('admin.order.details');
      
      Route::put('/admin/order/update-status',[AdminController::class,'update_order_status'])->name('admin.order.status.update');
      Route::get('/admin/slides', [AdminController::class, 'slides'])->name('admin.slides');
      Route::get('/admin/slide/add', [AdminController::class, 'slide_add'])->name('admin.slide.add');
      Route::post('/admin/slide/store', [AdminController::class, 'slide_store'])->name('admin.slide.store');
      Route::get('/admin/slide/{id}/edit',[AdminController::class, 'slide_edit'])->name('admin.slide.edit');
      Route::put('/admin/slide/update',[AdminController::class, 'slide_update'])->name('admin.slide.update');
      Route::delete('/admin/slide/{id}/delete', [AdminController::class, 'slide_delete'])->name('admin.slide.delete');
      Route::get('/admin/contact', [AdminController::class, 'contacts'])->name('admin.contacts');
      Route::delete('/admin/contact/{id}/delete', [AdminController::class, 'contact_delete'])->name('admin.contact.delete');
      Route::get('/admin/search',[AdminController::class, 'search'])->name('admin.search');
});

