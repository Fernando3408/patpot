<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InputController;
use App\Http\Controllers\InventoryMovementController;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RetailController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TrashController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    Route::get('/olvidar-contrasena', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/olvidar-contrasena', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/restablecer-contrasena/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/restablecer-contrasena', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [HomeController::class, 'index']);

    /* Productos */

    Route::get('/productos', [ProductController::class, 'index'])->name('products.index');

    Route::get('/productos/create', [ProductController::class, 'create'])->name('products.create');

    Route::post('/productos', [ProductController::class, 'store'])->name('products.store');

    Route::get('/productos/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');

    Route::put('/productos/{product}', [ProductController::class, 'update'])->name('products.update');

    Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/productos/{product}', [ProductController::class, 'show'])->name('products.show');

    /* Insumos */

    Route::get('/insumos', [InputController::class, 'index'])->name('inputs.index');

    Route::get('/insumos/create', [InputController::class, 'create'])->name('inputs.create');

    Route::post('/insumos', [InputController::class, 'store'])->name('inputs.store');

    Route::get('/insumos/{input}/edit', [InputController::class, 'edit'])->name('inputs.edit');

    Route::put('/insumos/{input}', [InputController::class, 'update'])->name('inputs.update');

    Route::delete('/insumos/{input}', [InputController::class, 'destroy'])->name('inputs.destroy');
    Route::get('/insumos/{input}', [InputController::class, 'show'])->name('inputs.show');
    Route::post('/insumos/{input}/adjust', [InputController::class, 'adjust'])
        ->middleware('canManage')
        ->name('inputs.adjust');
    Route::get('/export/{entity}', [InputController::class, 'export'])->name('export.csv');

    /* Recetas */

    Route::get('/recetas', [RecipeController::class, 'index'])->name('recipes.index');

    Route::get('/recetas/create', [RecipeController::class, 'create'])->name('recipes.create');

    Route::post('/recetas', [RecipeController::class, 'store'])->name('recipes.store');

    Route::get('/recetas/{product}/edit', [RecipeController::class, 'edit'])->name('recipes.edit');

    Route::get('/recetas/{product}', [RecipeController::class, 'show'])->name('recipes.show');

    Route::put('/recetas/{product}', [RecipeController::class, 'update'])->name('recipes.update');

    Route::delete('/recetas/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');

    /* Proveedores */

    Route::get('/proveedores', [SupplierController::class, 'index'])->name('proveedores.index');

    Route::get('/proveedores/create', [SupplierController::class, 'create'])->name('proveedores.create');

    Route::post('/proveedores', [SupplierController::class, 'store'])->name('proveedores.store');

    Route::get('/proveedores/{supplier}/edit', [SupplierController::class, 'edit'])->name('proveedores.edit');

    Route::put('/proveedores/{supplier}', [SupplierController::class, 'update'])->name('proveedores.update');

    Route::delete('/proveedores/{supplier}', [SupplierController::class, 'destroy'])->name('proveedores.destroy');
    Route::get('/proveedores/{supplier}', [SupplierController::class, 'show'])->name('proveedores.show');

    /* Clientes */

    Route::get('/clientes', [CustomerController::class, 'index'])->name('customers.index');

    Route::get('/clientes/create', [CustomerController::class, 'create'])->name('customers.create');

    Route::post('/clientes', [CustomerController::class, 'store'])->name('customers.store');

    Route::get('/clientes/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');

    Route::put('/clientes/{customer}', [CustomerController::class, 'update'])->name('customers.update');

    Route::delete('/clientes/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('/clientes/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    /* Salas */

    Route::get('/salas', [StoreController::class, 'index'])->name('salas.index');

    Route::get('/salas/create', [StoreController::class, 'create'])->name('salas.create');

    Route::post('/salas', [StoreController::class, 'store'])->name('salas.store');

    Route::get('/salas/{store}/edit', [StoreController::class, 'edit'])->name('salas.edit');

    Route::put('/salas/{store}', [StoreController::class, 'update'])->name('salas.update');

    Route::delete('/salas/{store}', [StoreController::class, 'destroy'])->name('salas.destroy');
    Route::get('/salas/{store}', [StoreController::class, 'show'])->name('salas.show');

    /* Precios por cliente */

    Route::get('/precios', [PriceController::class, 'index'])->name('precios.index');

    Route::get('/precios/create', [PriceController::class, 'create'])->name('precios.create');

    Route::post('/precios', [PriceController::class, 'store'])->name('precios.store');

    Route::get('/precios/{price}/edit', [PriceController::class, 'edit'])->name('precios.edit');

    Route::put('/precios/{price}', [PriceController::class, 'update'])->name('precios.update');

    Route::delete('/precios/{price}', [PriceController::class, 'destroy'])->name('precios.destroy');
    Route::get('/precios/{price}', [PriceController::class, 'show'])->name('precios.show');

    /* Retail y quiebres */

    Route::get('/retail', [RetailController::class, 'index'])->name('retail.index');

    Route::get('/retail/create', [RetailController::class, 'create'])->name('retail.create');

    Route::post('/retail', [RetailController::class, 'store'])->name('retail.store');

    Route::get('/retail/{retail}/edit', [RetailController::class, 'edit'])->name('retail.edit');

    Route::put('/retail/{retail}', [RetailController::class, 'update'])->name('retail.update');

    Route::delete('/retail/{retail}', [RetailController::class, 'destroy'])->name('retail.destroy');
    Route::get('/retail/{retail}', [RetailController::class, 'show'])->name('retail.show');

    /* Compras */

    Route::resource('compras', PurchaseController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/compras/{compra}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::post('/compras/{compra}/recepciones', [PurchaseController::class, 'receive'])->middleware('canManage')->name('purchases.receive');

    /* Producción */

    Route::resource('produccion', ProductionController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/produccion/{produccion}', [ProductionController::class, 'show'])->name('productions.show');
    Route::post('/produccion/{produccion}/cerrar', [ProductionController::class, 'close'])->name('productions.close');

    /* Pedidos */

    Route::resource('pedidos', OrderController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/pedidos/{pedido}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pedidos/{pedido}/despachos', [OrderController::class, 'dispatch'])->middleware('canManage')->name('orders.dispatch');

    /* Tareas */

    Route::get('/tareas', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tareas/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tareas', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tareas/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tareas/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tareas/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tareas/{task}/completar', [TaskController::class, 'complete'])->name('tasks.complete');

    /* Movimientos de inventario */

    Route::get('/movimientos', [InventoryMovementController::class, 'index'])->name('movements.index');

    /* Auditoría */

    Route::get('/auditoria', [AuditLogController::class, 'index'])->name('audit.index');

    /* Adjuntos */

    Route::get('/adjuntos/lista', [AttachmentController::class, 'list'])->name('attachments.list');
    Route::post('/adjuntos', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('/adjuntos/{attachment}/descargar', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/adjuntos/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::middleware('canManage')->prefix('admin')->name('admin.')->group(function () {
        /* Papelera */
        Route::get('/papelera', [TrashController::class, 'index'])->name('trash.index');
        Route::post('/papelera/restaurar', [TrashController::class, 'restore'])->name('trash.restore');
        Route::post('/papelera/eliminar', [TrashController::class, 'forceDelete'])->name('trash.force-delete');
        Route::post('/papelera/restaurar-multiple', [TrashController::class, 'restoreMultiple'])->name('trash.restore-multiple');
        Route::post('/papelera/eliminar-multiple', [TrashController::class, 'forceDeleteMultiple'])->name('trash.force-delete-multiple');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/usuarios/{user}/editar', [AdminController::class, 'edit'])->name('users.edit');
        Route::put('/usuarios/{user}', [AdminController::class, 'update'])->name('users.update');
        Route::delete('/usuarios/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
        Route::post('/usuarios/{user}/toggle-status', [AdminController::class, 'toggleStatus'])->name('users.toggle-status');

        /* Crear usuario */
        Route::get('/usuarios/crear', [RegisteredUserController::class, 'create'])->name('users.create');
        Route::post('/usuarios', [RegisteredUserController::class, 'store'])->name('users.store');

    });

});
