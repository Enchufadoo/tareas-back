<?php

use App\Http\Controllers\FriendController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login/oauth', [UserController::class, 'oauthLogin']);
Route::post('/user/login/email', [UserController::class, 'emailLogin']);
Route::post('/user/registration/email', [UserController::class, 'emailRegistration']);
Route::get('/user/username/available', [UserController::class, 'isUsernameAvailable']);
Route::get('/user/email/available', [UserController::class, 'isEmailAvailable']);
Route::post('/user/password/reset', [PasswordResetController::class, 'passwordReset']);
Route::post('/user/password/code', [PasswordResetController::class, 'enterResetCode']);
Route::post('/user/password/reset/update', [PasswordResetController::class, 'updatePasswordForReset']);

Route::middleware('auth:sanctum')->group(function () {
    /** User Routes */
    Route::get('/user', [UserController::class, 'userData']);
    Route::patch('/user', [UserController::class, 'update']);
    Route::patch('/user/username', [UserController::class, 'setUsername']);
    Route::patch('/user/password', [UserController::class, 'changePassword']);
    /** Task Routes */

    Route::middleware('check.user:task')->group(function () {
        Route::post('/task/progress/{task}', [TaskController::class, 'addProgress']);
        Route::delete('/task/progress/{task}', [TaskController::class, 'removeProgress']);
        Route::get('/task', [TaskController::class, 'listTasks']);
        Route::get('/task/week', [TaskController::class, 'weeksProgress']);
        Route::post('/task', [TaskController::class, 'store']);
        Route::get('/task/store/data', [TaskController::class, 'dataStoreTask']);
        Route::get('/task/{task}', [TaskController::class, 'show']);
        Route::delete('/task/{task}', [TaskController::class, 'delete']);
        Route::put('/task/finish/{task}/{finished}', [TaskController::class, 'finish']);
    });

    Route::get('/friend/username', [FriendController::class, 'friendUsernameExists']);
    Route::post('/friend/request', [FriendController::class, 'sendFriendRequest']);
    Route::get('/friend/request', [FriendController::class, 'list']);

    /** Setting Routes */
    Route::get('/setting', [SettingController::class, 'listSettings']);
    Route::put('/setting/{setting}', [SettingController::class, 'update']);
});

Route::get('/{any_path?}', function () {
    abort(404);
})->where('any_path', '(.*)');
