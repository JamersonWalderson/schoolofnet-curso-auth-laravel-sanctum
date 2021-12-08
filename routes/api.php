<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

// [
//     "1|FI6wkJjaBkgrO8XyseeFYcV9391JJ5WD9iXV30Ht",
//     "2|uiluYo1Os0BLt94uRUjLCkevUjXd7ZRSyZe0Hunt",
//     "3|Q4wfTTbNPKXLF4XzDUYWnTl68TIdMpSTYoqXmc04"
// ]

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token_can_all = $user->createToken('can_all')->plainTextToken;
    $token_can_update = $user->createToken('can_update', ['system:update'])->plainTextToken;
    $token_can_create = $user->createToken('can_create', ['system:create'])->plainTextToken;

    $abilities = [$token_can_all, $token_can_update, $token_can_create];
    return $abilities;

});

Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::get('user', function(Request $request){
        return $request->user();
    });
    Route::get('list_tokens', function(Request $request){
        return $request->user()->tokens;
    });

    Route::get('token_abilities', function(Request $request){
        $abilities = [];

        if($request->user()->tokenCan('system:update')) {
            array_push($abilities, 'Posso atualizar');
        
        }
        if($request->user()->tokenCan('system:create')) {
            array_push($abilities, 'Posso criar');
        
        }
        if($request->user()->tokenCan('system:all')){
            array_push($abilities, 'Posso criar e atualizar');

        }
        return $abilities;

    });
});
