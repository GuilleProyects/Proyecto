<?php
//En la parte superiror:
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudyController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
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
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/



// rutas con este prefijo: /api/auth/....
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
    ], function ($router) {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh',  [AuthController::class, 'refresh']);
        Route::post('me',  [AuthController::class, 'me']);
});




Route::get("/", function(){
    $data = ["message"=>"Bienvenido a la API"];
    return response()->json($data,200);
    
});

Route::resource("/products", ProductController::class);
Route::resource("/products", ProductController::class)->except(['create', 'edit']);
Route::fallback(function(){
    return response()->json(["error"=>"No encontradooo"],404);
});

