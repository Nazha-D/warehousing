<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarBrandController;
use App\Http\Controllers\CarColorController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\TaxationGroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('auth:sanctum')->get('/debug', function (Request $request) {
    return [
        'auth_user' => auth()->user(),
        'token' => $request->bearerToken(),
        'headers' => $request->header(),
    ];
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {




    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1'); //5 attempts in a minute



    Route::middleware(['auth:sanctum','set.permissions.team'])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);

        Route::post('/logout', [AuthController::class, 'logoutCurrent']);


        Route::post('/logout-all', [AuthController::class, 'logoutAllDevices']);

    });

});
//roles*
Route::group(['controller' =>\App\Http\Controllers\RoleController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'roles'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::get('/{role}','show');
    Route::post('/update/{role}','update');
    Route::delete('/{role}','destroy');

});

Route::group(['controller' =>\App\Http\Controllers\RoleController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'permissions'],function () {
    Route::get('/','getPermissions');
});
//taxation_groups
Route::group(['controller' =>TaxationGroupController::class,
        'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'taxation-groups'],function () {
        Route::get('/','index');
        Route::post('/','store');
    Route::post('/tax-rate','addRate');
    Route::post('/tax-rate/update/{taxRate}','updateRate');
    Route::delete('/tax-rate/{taxRate}','deleteRate');
    Route::post('/update/{taxationGroup}','update');
    Route::delete('/{taxationGroup}','destroy');

});
//currencies
Route::group(['controller' =>CurrencyController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'currencies'
   ],function (){
    Route::get('/','index');
    Route::post('/convert',  'convert');
    Route::post('/exchange/manual/update','updateManual');
    Route::delete('/exchange/manual/{exchangeRate}', 'deleteManual');
    Route::get('/exchange/company/{companyId}','listCompanyRates');
    Route::post('/get-rate','getRate');
});
//company currencies
Route::group(['controller' =>\App\Http\Controllers\CompanyCurrencyController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'company-currencies'
],function (){
    Route::post('/','storeCompanyCurrencies');
    Route::post('/manual-rates','storeManualRate');

    Route::get('/','listCompanyCurrencies');

});
//categories
Route::group(['controller' =>\App\Http\Controllers\CategoryController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'categories'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::post('/update/{category}','update');
    Route::delete('/{category}','destroy');

});
//item_groups
Route::group(['controller' =>\App\Http\Controllers\ItemGroupController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'item-groups'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::post('/update/{itemGroup}','update');
    Route::delete('/{itemGroup}','destroy');

});
//items
Route::group(['controller' =>\App\Http\Controllers\ItemController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'items'],function () {
    Route::get('/', 'index');
    Route::get('/create', 'create');
    Route::post('/', 'store');
    Route::get('/edit/{item}', 'edit');
    Route::post('/update/{item}', 'update');
    Route::get('/{item}', 'show');
    Route::delete('/{item}', 'destroy');
});
//items
Route::group(['controller' =>\App\Http\Controllers\ItemImageController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'item-images'],function () {

    Route::post('/{item}', 'store');

    Route::delete('/{image}', 'destroy');
});
//price-lists
Route::group(['controller' =>\App\Http\Controllers\PriceListController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'price-lists'],function () {
    Route::get('','index');
    Route::get('/show/{priceList}','show');
    Route::get('/create','create');
    Route::post('', 'store');
    Route::post('test/{priceList}/preview', 'preview');
    Route::post('/{priceList}', 'destroy');
});
Route::group(['controller' =>\App\Http\Controllers\ClientController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'clients'],function () {
    Route::get('', 'index');
    Route::get('/create', 'create');
    Route::get('/{client}', 'show');

    Route::post('', 'store');
    Route::post('/update/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});
//car colors
Route::group(['controller' => CarColorController::class,
    'prefix' => 'car-colors',
    'middleware'=>['auth:sanctum','set.permissions.team'],
   ], function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::post('/update/{carColor}','update');
    Route::delete('/{carColor}','destroy');

});
//car brands
Route::group(['controller' => CarBrandController::class,
    'prefix' => 'car-brands',
   'middleware'=>['auth:sanctum','set.permissions.team']], function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::post('/update/{carBrand}','update');
    Route::delete('/{carBrand}','destroy');

});

//car models
Route::group(['controller' => \App\Http\Controllers\CarModelController::class,
    'prefix' => 'car-models',
    'middleware'=>['auth:sanctum','set.permissions.team']], function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::post('/update/{carModel}','update');
    Route::delete('/{carModel}','destroy');

});

//car techs
Route::group(['controller' => \App\Http\Controllers\CarTechnicianController::class,
    'prefix' => 'car-techs',
    'middleware'=>['auth:sanctum','set.permissions.team']], function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::post('/update/{carTechnician}','update');
    Route::delete('/{carTechnician}','destroy');

});

//cars
Route::group(['controller' => \App\Http\Controllers\CarController::class,
    'prefix' => 'cars',
    'middleware'=>['auth:sanctum','set.permissions.team']], function () {
    Route::get('/','index');
    Route::get('/create', 'create');
    Route::post('/','store');
    Route::post('/update/{car}','update');
    Route::delete('/{car}','destroy');

});
//users
Route::group(['controller' =>\App\Http\Controllers\UserController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'users'],function () {
    Route::get('', 'index');
    Route::get('/create', 'create');
    Route::get('/{user}', 'show');

    Route::post('', 'store');
    Route::post('/update/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});
//cashing-methods
Route::group(['controller' =>\App\Http\Controllers\CashingMethodController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'cashing-methods'],function () {
    Route::get('', 'index');

    Route::get('/{cashingMethod}', 'show');

    Route::post('', 'store');
    Route::post('/update/{cashingMethod}', 'update');
    Route::delete('/{cashingMethod}', 'destroy');
});
//warehouses
Route::group(['controller' =>\App\Http\Controllers\WarehouseController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'warehouses'],function () {
    Route::get('', 'index');
    Route::get('create', 'create');
    Route::get('/{warehouse}', 'show');
    Route::post('', 'store');
    Route::post('/update/{warehouse}', 'update');
    Route::delete('/{warehouse}', 'destroy');
});
//replenishments
Route::group(['controller' =>\App\Http\Controllers\ReplenishmentController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'replenishments'],function () {
    Route::get('', 'index');
    Route::get('create', 'create');
    Route::get('get-items-for-replenishment', 'getItemsForReplenishment');
    Route::get('/{replenishment}', 'show');
    Route::post('', 'storeWithItems');
    Route::post('/update/{replenishment}', 'update');

});
//transfers
Route::group(['controller' =>\App\Http\Controllers\TransferController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'transfers'],function () {
    Route::get('', 'index');
    Route::get('get-items-for-transfer', 'getItemsForTransfer');
    Route::get('create', 'create');
    Route::get('/{transfer}', 'show');
    Route::post('', 'store');
    Route::post('/transfer-in/{transfer}', 'receive');

});
//Inventory
Route::group(['controller' =>\App\Http\Controllers\InventoryController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'inventory'],function () {
    Route::post('get-inventory-items', 'getInventorySnapshot');
    Route::post('', 'saveInventoryCounts');

});
//Combos
Route::group(['controller' =>\App\Http\Controllers\ComboController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'combos'],function () {
    Route::get('/', 'index');
    Route::get('/create', 'create');
    Route::post('/', 'store');
    Route::get('/edit/{combo}', 'edit');
    Route::post('/update/{combo}', 'update');
    Route::get('/{combo}', 'show');
    Route::delete('/{combo}', 'destroy');

});
//quotations
Route::group(['controller' =>\App\Http\Controllers\QuotationController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'quotations'],function () {
    Route::get('/', 'index');
    Route::get('/create', 'create');
    Route::post('/', 'store');
    Route::get('/c/{combo}', 'edit');
    Route::post('/update/{id}', 'update');
    Route::post('/update-status/{quotation}', 'changeStatus');
    Route::post('/send-mail/{quotation}', 'sendMail');
    Route::get('/{id}', 'show');
//    Route::delete('/{combo}', 'destroy');

});
//CompanyHeaders
Route::group(['controller' =>\App\Http\Controllers\CompanyHeaderController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'company-headers'],function () {
    Route::get('/', 'index');
    Route::get('/create', 'create');
    Route::post('/', 'store');
    Route::post('/update/{id}', 'update');
    Route::get('/{id}', 'show');
    Route::delete('/{id}', 'destroy');

});
//Delivery Terms
Route::group(['controller' =>\App\Http\Controllers\DeliveryTermController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'delivery-terms'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::post('/update/{id}','update');
    Route::delete('/{id}','destroy');

});

//Payment Terms
Route::group(['controller' =>\App\Http\Controllers\PaymentTermController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'payment-terms'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::post('/update/{id}','update');
    Route::delete('/{id}','destroy');

});
//Terms And Conditions
Route::group(['controller' =>\App\Http\Controllers\TermsController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'terms-and-conditions'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::post('/update/{id}','update');
    Route::delete('/{id}','destroy');

});
//Sales Orders
Route::group(['controller' =>\App\Http\Controllers\SalesOrderController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'sales-orders'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::get('/create','create');
    Route::get('/{order}','show');
    Route::get('/cancel/{order}','cancel');
});

//Deliveries
Route::group(['controller' =>\App\Http\Controllers\DeliveryController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'deliveries'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::get('/show/{delivery}','show');
    Route::get('/deliver/{delivery}','deliver');
    Route::get('/fail-or-reject/{delivery}','failOrReject');
    Route::post('/complete/{delivery}','complete');
    Route::get('/cancel/{delivery}','cancel');
    Route::get('/create','create');
    Route::get('/get-sales-orders/{clientId}','getSalesOrdersForDelivery');
});
//Sales Invoice
Route::group(['controller' =>\App\Http\Controllers\SalesInvoiceController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'sales-invoices'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::get('/cancel/{salesInvoice}','cancelInvoice');
    Route::get('/show/{id}','show');
    Route::get('/show/{id}','show');
//    Route::get('/deliver/{delivery}','deliver');
//    Route::get('/fail-or-reject/{delivery}','failOrReject');
//    Route::post('/complete/{delivery}','complete');
//    Route::get('/cancel/{delivery}','cancel');
    Route::get('/create','create');
});
//Pos Terminals
Route::group(['controller' =>\App\Http\Controllers\PosTerminalController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'pos-terminals'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::get('/create','create');
    Route::get('/{posTerminal}','show');
    Route::post('/update/{posTerminal}','update');
    Route::delete('/{id}','destroy');

});
//Discounts
Route::group(['controller' =>\App\Http\Controllers\DiscountController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'discounts'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::post('/update/{discount}','update');
    Route::delete('/{discount}','destroy');

});
//Pos Sessions
Route::group(['controller' =>\App\Http\Controllers\PosSessionController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'pos-sessions'],function () {
    Route::get('/','index');
    Route::post('/','store');
    Route::get('/get-open-session/{posTerminal}','getOpenSession');
    Route::post('/update/{posSession}','update');
    Route::post('/close/{posSession}','close');
    Route::delete('/{posSession}','destroy');

});
//Cash Trays
Route::group(['controller' =>\App\Http\Controllers\PosCashTrayController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'cash-trays'],function () {
    Route::get('/','index');
    Route::post('/{session}','store');
    Route::post('/close/{tray}','close');
    Route::get('/create/{sessionId}','create');
    Route::get('/report/{tray}','closingReport');
    Route::get('/get-open-tray/{sessionId}','getOpenedTray');

});
//Pos Invoices
Route::group(['controller' =>\App\Http\Controllers\PosInvoiceController::class,
    'middleware'=>['auth:sanctum','set.permissions.team'],
    'prefix'=>'pos-invoices'],function () {
    Route::get('/','index');
    Route::get('/show/{posInvoice}','show');
    Route::post('/','store');
    Route::post('/make-payment','addPayment');

});
