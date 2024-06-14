<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::group(['namespace'=>'Admin', 'as' => 'admin.', 'prefix'=>'admin'] ,function(){
    Route::group(['namespace' => 'Auth', 'prefix' => 'auth', 'as' => 'auth.'], function(){
        Route::get('login', 'LoginController@login')->name('login');
        Route::post('login', 'LoginController@submit');
        Route::get('logout', 'LoginController@logout')->name('logout');
    });

    Route::group(['middleware' => ['admin']], function(){
        Route::get('/', 'DashboardController@dashboard')->name('dashboard');
        Route::post('account-status','DashboardController@accountStats')->name('account-status');
        Route::get('settings', 'SystemController@settings')->name('settings');
        Route::post('settings', 'SystemController@settingsUpdate');
        Route::get('settings-password', 'SystemController@settings')->name('settings.password');
        Route::post('settings-password', 'SystemController@settingsPasswordUpdate')->name('settings-password');

        Route::group(['prefix' => 'custom-role', 'as' => 'custom-role.', 'middleware' => ['module:employee_role_section']], function () {
            Route::get('create', 'CustomRoleController@create')->name('create');
            Route::post('create', 'CustomRoleController@store');
            Route::get('edit/{id}', 'CustomRoleController@edit')->name('edit');
            Route::post('update/{id}', 'CustomRoleController@update')->name('update');
            Route::delete('delete/{id}', 'CustomRoleController@distroy')->name('delete');
            Route::post('search', 'CustomRoleController@search')->name('search');
            Route::get('status/{id}/{status}', 'CustomRoleController@status')->name('status');
            Route::get('export-employee-role', 'CustomRoleController@employee_role_export')->name('export-employee-role');
        });

        Route::group(['prefix' => 'employee', 'as' => 'employee.', 'middleware' => ['module:employee_section']], function () {
            Route::get('add-new', 'EmployeeController@add_new')->name('add-new');
            Route::post('add-new', 'EmployeeController@store');
            Route::get('list', 'EmployeeController@list')->name('list');
            Route::get('update/{id}', 'EmployeeController@edit')->name('edit');
            Route::post('update/{id}', 'EmployeeController@update')->name('update');
            Route::delete('delete/{id}', 'EmployeeController@distroy')->name('delete');
            Route::get('export-employee', 'EmployeeController@employee_list_export')->name('export-employee');
        });

        Route::group(['prefix' => 'category', 'as' => 'category.', 'middleware' => ['module:category_section']], function () {
            Route::get('add', 'CategoryController@index')->name('add');
            Route::get('add-sub-category', 'CategoryController@subIndex')->name('add-sub-category');
            Route::post('store', 'CategoryController@store')->name('store');
            Route::get('edit/{id}', 'CategoryController@edit')->name('edit');
            Route::get('sub-edit/{id}', 'CategoryController@editSub')->name('sub-edit');
            Route::post('update/{id}', 'CategoryController@update')->name('update');
            Route::post('update-sub/{id}', 'CategoryController@updateSub')->name('update-sub');
            Route::post('store', 'CategoryController@store')->name('store');
            Route::get('status/{id}/{status}', 'CategoryController@status')->name('status');
            Route::delete('delete/{id}', 'CategoryController@delete')->name('delete');
        });

        Route::group(['prefix' => 'brand', 'as' => 'brand.', 'middleware' => ['module:brand_section']], function () {
            Route::get('add', 'BrandController@index')->name('add');
            Route::post('store','BrandController@store')->name('store');
            Route::get('edit/{id}', 'BrandController@edit')->name('edit');
            Route::post('update/{id}', 'BrandController@update')->name('update');
            Route::delete('delete/{id}', 'BrandController@delete')->name('delete');
        });
        //unit
        Route::group(['prefix' => 'unit', 'as' => 'unit.', 'middleware' => ['module:unit_section']], function () {
            Route::get('index', 'UnitController@index')->name('index');
            Route::post('store', 'UnitController@store')->name('store');
            Route::get('edit/{id}', 'UnitController@edit')->name('edit');
            Route::post('update/{id}', 'UnitController@update')->name('update');
             Route::delete('delete/{id}', 'UnitController@delete')->name('delete');
        });

        Route::group(['prefix' => 'product', 'as' => 'product.', 'middleware' => ['module:product_section']], function () {
            Route::get('add', 'ProductController@index')->name('add');
            Route::post('store', 'ProductController@store')->name('store');
            Route::get('list', 'ProductController@list')->name('list');
            Route::get('edit/{id}', 'ProductController@edit')->name('edit');
            Route::post('update/{id}', 'ProductController@update')->name('update');
            Route::delete('delete/{id}', 'ProductController@delete')->name('delete');
            Route::get('barcode-generate/{id}', 'ProductController@barcodeGenerate')->name('barcode-generate');
            Route::get('barcode/{id}', 'ProductController@barcode')->name('barcode');
            Route::get('bulk-import', 'ProductController@bulkImportIndex')->name('bulk-import');
            Route::post('bulk-import', 'ProductController@bulkImportData');
            Route::get('bulk-export', 'ProductController@bulkExportData')->name('bulk-export');

            //ajax request
            Route::get('get-categories', 'ProductController@getCategories')->name('get-categories');
            Route::get('remove-image/{id}/{name}', 'ProductController@remove_image')->name('remove-image');
        });

        Route::group(['prefix' => 'pos', 'as' => 'pos.', 'middleware' => ['module:pos_section']], function () {
            Route::get('/', 'POSController@index')->name('index');
            Route::get('quick-view', 'POSController@quickView')->name('quick-view');
            Route::post('variant_price', 'POSController@variant_price')->name('variant_price');
            Route::post('add-to-cart', 'POSController@addToCart')->name('add-to-cart');
            Route::post('remove-from-cart', 'POSController@removeFromCart')->name('remove-from-cart');
            Route::post('cart-items', 'POSController@cartItems')->name('cart_items');
            Route::post('update-quantity', 'POSController@updateQuantity')->name('updateQuantity');
            Route::post('empty-cart', 'POSController@emptyCart')->name('emptyCart');
            Route::post('tax', 'POSController@updateTax')->name('tax');
            Route::post('discount', 'POSController@updateDiscount')->name('discount');
            Route::get('customers', 'POSController@getCustomers')->name('customers');
            Route::get('customer-balance', 'POSController@customerBalance')->name('customer-balance');
            Route::post('order', 'POSController@placeOrder')->name('order');
            Route::get('orders', 'POSController@orderList')->name('orders');
            Route::get('order-details/{id}', 'POSController@order_details')->name('order-details');
            Route::get('invoice/{id}', 'POSController@generateInvoice');
            Route::get('search-products','POSController@searchProduct')->name('search-products');
            Route::get('search-by-add','POSController@searchByAddProduct')->name('search-by-add');

            Route::post('coupon-discount', 'POSController@couponDiscount')->name('coupon-discount');
            Route::post('remove-coupon','POSController@removeCoupon')->name('remove-coupon');
            Route::get('change-cart','POSController@changeCart')->name('change-cart');
            Route::get('new-cart-id','POSController@newCartId')->name('new-cart-id');
            Route::get('clear-cart-ids','POSController@clearCartIds')->name('clear-cart-ids');
            Route::get('get-cart-ids','POSController@getCartIds')->name('get-cart-ids');
        });

        //account
        Route::group(['prefix' => 'account', 'as' => 'account.', 'middleware' => ['module:account_section']], function () {
            Route::get('add','AccountController@add')->name('add');
            Route::post('store', 'AccountController@store')->name('store');
            Route::get('list', 'AccountController@list')->name('list');
            Route::get('view/{id}', 'AccountController@view')->name('view');
            Route::get('edit/{id}', 'AccountController@edit')->name('edit');
            Route::post('update/{id}', 'AccountController@update')->name('update');
            Route::delete('delete/{id}', 'AccountController@delete')->name('delete');

            //expense
            Route::get('add-expense','ExpenseController@add')->name('add-expense');
            Route::post('store-expense', 'ExpenseController@store')->name('store-expense');

            //income
            Route::get('add-income', 'IncomeController@add')->name('add-income');
            Route::post('store-income', 'IncomeController@store')->name('store-income');
            //transfer
            Route::get('add-transfer', 'TransferController@add')->name('add-transfer');
            Route::post('store-transfer', 'TransferController@store')->name('store-transfer');
            //transection
            Route::get('list-transection', 'TransectionController@list')->name('list-transection');
            Route::get('transection-export', 'TransectionController@export')->name('transection-export');

            //payable
            Route::get('add-payable', 'PayableController@add')->name('add-payable');
            Route::post('store-payable', 'PayableController@store')->name('store-payable');
            Route::post('payable-transfer','PayableController@transfer')->name('payable-transfer');

            //receivable
            Route::get('add-receivable', 'ReceivableController@add')->name('add-receivable');
            Route::post('store-receivable', 'ReceivableController@store')->name('store-receivable');
            Route::post('receivable-transfer','ReceivableController@transfer')->name('receivable-transfer');
        });

        //customer
        Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['module:customer_section']], function () {
            Route::get('add','CustomerController@index')->name('add');
            Route::post('store', 'CustomerController@store')->name('store');
            Route::get('list', 'CustomerController@list')->name('list');
            Route::get('view/{id}', 'CustomerController@view')->name('view');
            Route::get('edit/{id}', 'CustomerController@edit')->name('edit');
            Route::post('update/{id}', 'CustomerController@update')->name('update');
            Route::delete('delete/{id}', 'CustomerController@delete')->name('delete');
            Route::post('update-balance','CustomerController@updateBalance')->name('update-balance');
            Route::get('transaction-list/{id}', 'CustomerController@transactionList')->name('transaction-list');
        });
        //supplier
        Route::group(['prefix' => 'supplier', 'as' => 'supplier.', 'middleware' => ['module:supplier_section']], function () {
            Route::get('add','SupplierController@index')->name('add');
            Route::post('store', 'SupplierController@store')->name('store');
            Route::get('list', 'SupplierController@list')->name('list');
            Route::get('view/{id}', 'SupplierController@view')->name('view');
            Route::get('edit/{id}', 'SupplierController@edit')->name('edit');
            Route::post('update/{id}', 'SupplierController@update')->name('update');
            Route::delete('delete/{id}', 'SupplierController@delete')->name('delete');
            Route::get('products/{id}', 'SupplierController@productList')->name('products');
            Route::get('transaction-list/{id}', 'SupplierController@transactionList')->name('transaction-list');
            Route::post('add-new-purchase','SupplierController@addNewPurchase')->name('add-new-purchase');
            Route::post('pay-due','SupplierController@payDue')->name('pay-due');
        });
        //stock limit
        Route::group(['prefix' => 'stock', 'as' => 'stock.', 'middleware' => ['module:stock_section']], function () {
            Route::get('stock-limit', 'StocklimitController@stockLimit')->name('stock-limit');
            Route::post('update-quantity', 'StocklimitController@updateQuantity')->name('update-quantity');
        });
        //business settings
        Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.','middleware'=>['actch','module:setting_section']], function () {
            Route::get('shop-setup', 'BusinessSettingsController@shopIndex')->name('shop-setup');
            Route::post('update-setup', 'BusinessSettingsController@shopSetup')->name('update-setup');
            Route::get('shortcut-keys', 'BusinessSettingsController@shortcutKey')->name('shortcut-keys');
        });

        //coupon
        Route::group(['prefix' => 'coupon', 'as' => 'coupon.', 'middleware' => ['module:coupon_section']], function () {
            Route::get('add-new', 'CouponController@addNew')->name('add-new');
            Route::post('store', 'CouponController@store')->name('store');
            Route::get('edit/{id}', 'CouponController@edit')->name('edit');
            Route::post('update/{id}', 'CouponController@update')->name('update');
            Route::get('status/{id}/{status}', 'CouponController@status')->name('status');
            Route::delete('delete/{id}', 'CouponController@delete')->name('delete');
        });
    });
});
