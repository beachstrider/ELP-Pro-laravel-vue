<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Api', 'middleware' => ['throttle:60,1']], function () {
    Route::group(['prefix' => 'a/auth', 'namespace' => 'Auth'], function () {
        Route::post('sign/in', 'AuthController@postSignIn')->name('login');
        Route::post('password/forgot', 'AuthController@postForgotPassword');
        Route::post('password/reset', 'AuthController@postResetPassword');
        Route::get('user', 'AuthController@getUser');
    });

    Route::group(['middleware' => ['auth:api', 'verified']], function () {
        Route::group(['namespace' => 'Auth', 'prefix' => 'a/auth'], function () {
            Route::get('logout', 'AuthController@getLogout');
            Route::get('user', 'AuthController@getUser');
        });

        Route::group(['prefix' => 'dropdowns'], function () {
            Route::post('roles', 'DropdownController@postRoles');
            Route::post('permissions', 'DropdownController@postPermissions');

            Route::post('brands', 'DropdownController@postBrands')->middleware('has.permission:brandsview|clientsview|manufacturersview|dealersview');
            Route::post('location/types', 'DropdownController@postLocationTypes')->middleware('has.permission:contactsview|manufacturersview|dealersview');

            Route::post('countries', 'DropdownController@postCountries')->middleware('has.permission:contactsview');
            Route::post('suppliers', 'DropdownController@postSuppliers')->middleware('has.permission:contractsview|manufacturersview');
            Route::post('suppliers/{slug}', 'DropdownController@postSuppliersByType')->middleware('has.permission:contractsview|manufacturersview');
            Route::post('transport/vehicles/types', 'DropdownController@postTransportVehiclesTypes')->middleware('has.permission:transportvehiclesview');
            Route::post('dealers', 'DropdownController@postDealers')->middleware('has.permission:clientsview');
            Route::post('locations', 'DropdownController@postLocations')->middleware('has.permission:clientsview|manufacturersview|dealersview');
            Route::post('contacts', 'DropdownController@postContacts')->middleware('has.permission:clientsview|manufacturersview|dealersview');
            Route::post('models', 'DropdownController@postModels')->middleware('has.permission:clientsview|manufacturersview|dealersview');
            Route::post('logistic/types', 'DropdownController@postLogisticTypes')->middleware('has.permission:pricesview|suppliersview|dealersview');
            Route::post('routes', 'DropdownController@postRoutes')->middleware('has.permission:pricesview');
            Route::post('contracts', 'DropdownController@postContracts')->middleware('has.permission:suppliersview');
            Route::post('supplier/types', 'DropdownController@postSupplierTypes')->middleware('has.permission:suppliersview');
            Route::post('routes', 'DropdownController@postRoutes')->middleware('has.permission:pricesview');

        });

        Route::group([], function () {
            Route::group(['prefix' => 'users'], function () {
                Route::get('/', 'UserController@getList')->middleware('has.permission:usersview');
                Route::get('detail/{id}', 'UserController@getDetailByGuid')->middleware('has.permission:usersview');
                Route::post('create', 'UserController@postCreate')->middleware('has.permission:usersstore');
                Route::post('update', 'UserController@postUpdateByGuid')->middleware('has.permission:usersupdate');
                Route::post('delete', 'UserController@postDeleteByGuid')->middleware('has.permission:usersdestroy');
                Route::post('profile', 'UserController@postProfileByGuid');
                Route::post('suspend', 'UserController@postSuspendUser')->middleware('has.permission:usersupdate');
                Route::post('activate', 'UserController@postActivateUser')->middleware('has.permission:usersupdate');
            });

            Route::group(['prefix' => 'roles'], function () {
                Route::get('/', 'RoleController@getList')->middleware('has.permission:rolesview');
                Route::get('detail/{id}', 'RoleController@getDetail')->middleware('has.permission:rolesview');
                Route::post('create', 'RoleController@postCreate')->middleware('has.permission:rolesstore');
                Route::post('update', 'RoleController@postUpdate')->middleware('has.permission:rolesupdate');
                Route::post('delete', 'RoleController@postDelete')->middleware('has.permission:rolesdestroy');
            });

            Route::group(['prefix' => 'brands'], function () {
                Route::get('/', 'BrandController@getList')->middleware('has.permission:brandsview');
                Route::get('detail/{id}', 'BrandController@getDetailByGuid')->middleware('has.permission:brandsview');
                Route::post('create', 'BrandController@postCreate')->middleware('has.permission:brandsstore');
                Route::post('update', 'BrandController@postUpdateByGuid')->middleware('has.permission:brandsupdate');
                Route::post('delete', 'BrandController@postDeleteByGuid')->middleware('has.permission:brandsdestroy');
            });

            Route::group(['prefix' => 'models'], function () {
                Route::get('/', 'ModelController@getList')->middleware('has.permission:modelsview');
                Route::get('detail/{id}', 'ModelController@getDetailByGuid')->middleware('has.permission:modelsview');
                Route::post('create', 'ModelController@postCreate')->middleware('has.permission:modelsstore');
                Route::post('update', 'ModelController@postUpdateByGuid')->middleware('has.permission:modelsupdate');
                Route::post('delete', 'ModelController@postDeleteByGuid')->middleware('has.permission:modelsdestroy');
            });


            Route::group(['prefix' => 'location/types'], function () {
                Route::get('/', 'LocationTypeController@getList')->middleware('has.permission:locationtypesview');
                Route::get('detail/{id}', 'LocationTypeController@getDetailByGuid')->middleware('has.permission:locationtypesview');
                Route::post('create', 'LocationTypeController@postCreate')->middleware('has.permission:locationtypesstore');
                Route::post('update', 'LocationTypeController@postUpdateByGuid')->middleware('has.permission:locationtypesupdate');
                Route::post('delete', 'LocationTypeController@postDeleteByGuid')->middleware('has.permission:locationtypesdestroy');
            });

            Route::group(['prefix' => 'contacts'], function () {
                Route::get('/', 'ContactController@getList')->middleware('has.permission:contactsview');
                Route::get('detail/{id}', 'ContactController@getDetailByGuid')->middleware('has.permission:contactsview');
                Route::post('create', 'ContactController@postCreate')->middleware('has.permission:contactsstore');
                Route::post('update', 'ContactController@postUpdateByGuid')->middleware('has.permission:contactsupdate');
                Route::post('delete', 'ContactController@postDeleteByGuid')->middleware('has.permission:contactsdestroy');
            });

            Route::group(['prefix' => 'locations'], function () {
                Route::get('/', 'LocationController@getList')->middleware('has.permission:locationsview');
                Route::get('detail/{id}', 'LocationController@getDetailByGuid')->middleware('has.permission:locationsview');
                Route::post('create', 'LocationController@postCreate')->middleware('has.permission:locationsstore');
                Route::post('update', 'LocationController@postUpdateByGuid')->middleware('has.permission:locationsupdate');
                Route::post('delete', 'LocationController@postDeleteByGuid')->middleware('has.permission:locationsdestroy');
            });

            Route::group(['prefix' => 'logistic/types'], function () {
                Route::get('/', 'LogisticTypeController@getList')->middleware('has.permission:logistictypesview');
                Route::get('detail/{id}', 'LogisticTypeController@getDetailByGuid')->middleware('has.permission:logistictypesview');
                Route::post('create', 'LogisticTypeController@postCreate')->middleware('has.permission:logistictypesstore');
                Route::post('update', 'LogisticTypeController@postUpdateByGuid')->middleware('has.permission:logistictypesupdate');
                Route::post('delete', 'LogisticTypeController@postDeleteByGuid')->middleware('has.permission:logistictypesdestroy');
            });

            Route::group(['prefix' => 'contracts'], function () {
                Route::get('/', 'ContractController@getList')->middleware('has.permission:contractsview');
                Route::get('detail/{id}', 'ContractController@getDetailByGuid')->middleware('has.permission:contractsview');
                Route::post('create', 'ContractController@postCreate')->middleware('has.permission:contractsstore');
                Route::post('update', 'ContractController@postUpdateByGuid')->middleware('has.permission:contractsupdate');
                Route::post('delete', 'ContractController@postDeleteByGuid')->middleware('has.permission:contractsdestroy');
            });

            Route::group(['prefix' => 'transport/vehicles'], function () {
                Route::get('/', 'TransportVehicleController@getList')->middleware('has.permission:transportvehiclesview');
                Route::get('detail/{id}', 'TransportVehicleController@getDetailByGuid')->middleware('has.permission:transportvehiclesview');
                Route::post('create', 'TransportVehicleController@postCreate')->middleware('has.permission:transportvehiclesstore');
                Route::post('update', 'TransportVehicleController@postUpdateByGuid')->middleware('has.permission:transportvehiclesupdate');
                Route::post('delete', 'TransportVehicleController@postDeleteByGuid')->middleware('has.permission:transportvehiclesdestroy');
            });

            Route::group(['prefix' => 'drivers'], function () {
                Route::get('/', 'DriverController@getList')->middleware('has.permission:driversview');
                Route::get('detail/{id}', 'DriverController@getDetailByGuid')->middleware('has.permission:driversview');
                Route::post('create', 'DriverController@postCreate')->middleware('has.permission:driversstore');
                Route::post('update', 'DriverController@postUpdateByGuid')->middleware('has.permission:driversupdate');
                Route::post('delete', 'DriverController@postDeleteByGuid')->middleware('has.permission:driversdestroy');
            });

            Route::group(['prefix' => 'routes'], function () {
                Route::get('/', 'RouteController@getList')->middleware('has.permission:routesview');
                Route::get('detail/{id}', 'RouteController@getDetailByGuid')->middleware('has.permission:routesview');
                Route::post('create', 'RouteController@postCreate')->middleware('has.permission:routesstore');
                Route::post('update', 'RouteController@postUpdateByGuid')->middleware('has.permission:routesupdate');
                Route::post('delete', 'RouteController@postDeleteByGuid')->middleware('has.permission:routesdestroy');
            });

            Route::group(['prefix' => 'clients'], function () {
                Route::get('/', 'ClientController@getList')->middleware('has.permission:clientsview');
                Route::get('detail/{id}', 'ClientController@getDetailByGuid')->middleware('has.permission:clientsview|clientsupdate');
                Route::post('create', 'ClientController@postCreate')->middleware('has.permission:clientsstore');
                Route::post('update', 'ClientController@postUpdateByGuid')->middleware('has.permission:clientsupdate');
                Route::post('delete', 'ClientController@postDeleteByGuid')->middleware('has.permission:clientsdestroy');
            });

            Route::group(['prefix' => 'prices'], function () {
                Route::get('/', 'PriceController@getList')->middleware('has.permission:pricesview');
                Route::get('detail/{id}', 'PriceController@getDetailByGuid')->middleware('has.permission:pricesview');
                Route::post('create', 'PriceController@postCreate')->middleware('has.permission:pricesstore');
                Route::post('update', 'PriceController@postUpdateByGuid')->middleware('has.permission:pricesupdate');
                Route::post('delete', 'PriceController@postDeleteByGuid')->middleware('has.permission:pricesdestroy');
            });

            Route::group(['prefix' => 'manufacturers'], function () {
                Route::get('/', 'ManufacturerController@getList')->middleware('has.permission:manufacturersview');
                Route::get('detail/{id}', 'ManufacturerController@getDetailByGuid')->middleware('has.permission:manufacturersview');
                Route::post('create', 'ManufacturerController@postCreate')->middleware('has.permission:manufacturersstore');
                Route::post('update', 'ManufacturerController@postUpdateByGuid')->middleware('has.permission:manufacturersupdate');
                Route::post('delete', 'ManufacturerController@postDeleteByGuid')->middleware('has.permission:manufacturersdestroy');
            });

            Route::group(['prefix' => 'suppliers'], function () {
                Route::get('/', 'SupplierController@getList')->middleware('has.permission:suppliersview');
                Route::get('detail/{id}', 'SupplierController@getDetailByGuid')->middleware('has.permission:suppliersview');
                Route::post('create', 'SupplierController@postCreate')->middleware('has.permission:suppliersstore');
                Route::post('update', 'SupplierController@postUpdateByGuid')->middleware('has.permission:suppliersupdate');
                Route::post('delete', 'SupplierController@postDeleteByGuid')->middleware('has.permission:suppliersdestroy');
            });

            Route::group(['prefix' => 'dealers'], function () {
                Route::get('/', 'DealerController@getList')->middleware('permission:dealersview');
                Route::get('detail/{id}', 'DealerController@getDetailByGuid')->middleware('permission:dealersview');
                Route::post('create', 'DealerController@postCreate')->middleware('permission:dealersstore');
                Route::post('update', 'DealerController@postUpdateByGuid')->middleware('permission:dealersupdate');
                Route::post('delete', 'DealerController@postDeleteByGuid')->middleware('permission:dealersdestroy');
            });
        });
    });
});

Route::group(['prefix' => 'upload', 'middleware' => ['throttle:1500,1']], function () {
    Route::post('/', 'UploadController@upload');
});




