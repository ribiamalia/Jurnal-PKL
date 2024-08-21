<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//route login
Route::post('/login', [App\Http\Controllers\Api\Auth\LoginController::class, 'index']);

//group route with middleware "auth"
Route::group(['middleware' => 'auth:api'], function() {

    //logout
    Route::post('/logout',
    [App\Http\Controllers\Api\Auth\LoginController::class, 'logout']);

});
    //group route with prefix "admin"
Route::prefix('admin')->group(function () {
    //group route with middleware "auth:api"
    Route::group(['middleware' => 'auth:api'], function () {
        //dashboard
        Route::get('/dashboard',
        App\Http\Controllers\Api\Admin\DashboardController::class);

        //permissions
        Route::get('/permissions', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'index'])
        ->middleware('permission:permission.index');

        //permissions all
        Route::get('/permissions/all', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'all'])
        ->middleware('permission:permission.index');

        Route::get('/roles/all', [\App\Http\Controllers\Api\Admin\RoleController::class, 'all'])
        ->middleware('permission:roles.index');

        //roles
        Route::apiResource('/roles', \App\Http\Controllers\Api\Admin\RoleController::class)
        ->middleware('permission:roles.index|roles.store|roles.update|roles.delete');

        Route::apiResource('/departemen', \App\Http\Controllers\Api\Admin\DepartemenController::class)
        ->middleware('permission:jurusan.index|jurusan.store|jurusan.update|jurusan.delete');

        Route::apiResource('/classes', \App\Http\Controllers\Api\Admin\ClassesController::class)
        ->middleware('permission:kelas.index|kelas.store|kelas.update|kelas.delete');

        Route::apiResource('/teacher', \App\Http\Controllers\Api\Admin\TeacherController::class);
        
        Route::apiResource('/users', \App\Http\Controllers\Api\Admin\UserController::class);
        Route::get('/byrole', [\App\Http\Controllers\Api\Admin\UserController::class, 'indexbyrole']);
        Route::post('/UpdateStudentImage/{id}', [\App\Http\Controllers\Api\Admin\UserController::class, 'updateStudentImage']);

        Route::apiResource('/parent', \App\Http\Controllers\Api\Admin\ParentController::class);

        Route::apiResource('/student', \App\Http\Controllers\Api\Admin\StudentController::class);

        Route::apiResource('/industri', \App\Http\Controllers\Api\Admin\IndustriController::class);

        Route::post('/userImage/{id}', [\App\Http\Controllers\Api\Admin\StudentController::class, 'updateDokumen']);

        Route::apiResource('/absence', \App\Http\Controllers\Api\Admin\AttendanceController::class);
        Route::get('/parent/absence', [\App\Http\Controllers\Api\Admin\AttendanceController::class, 'getStudent']);
        Route::get('/parentabsence/{parentId}', [\App\Http\Controllers\Api\Admin\AttendanceController::class, 'getStudent']);

        Route::apiResource('/jurnal', \App\Http\Controllers\Api\Admin\ActivityController::class);
    });

});

