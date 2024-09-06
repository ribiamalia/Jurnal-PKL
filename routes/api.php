<?php

use App\Http\Controllers\Api\Admin\UserController;
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

        Route::apiResource('/departemen', \App\Http\Controllers\Api\Admin\DepartemenController::class);

        Route::apiResource('/classes', \App\Http\Controllers\Api\Admin\ClassesController::class);

        Route::apiResource('/teacher', \App\Http\Controllers\Api\Admin\TeacherController::class);
        
        Route::apiResource('/users', \App\Http\Controllers\Api\Admin\UserController::class);

        Route::get('/Studentbyrole', [\App\Http\Controllers\Api\Admin\UserController::class, 'indexbyrole']);

        Route::post('/UpdateStudentImage/{id}', [\App\Http\Controllers\Api\Admin\UserController::class, 'updateStudentImage']);
        Route::post('/updateImage/{id}', [\App\Http\Controllers\Api\Admin\ActivityController::class, 'updateImage']);
        Route::post('/updateVisitImage/{id}', [\App\Http\Controllers\Api\Admin\VisitController::class, 'updateImage']);

        Route::apiResource('/parent', \App\Http\Controllers\Api\Admin\ParentController::class);

        Route::apiResource('/student', \App\Http\Controllers\Api\Admin\StudentController::class);

        Route::apiResource('/industri', \App\Http\Controllers\Api\Admin\IndustriController::class);

        Route::post('/userImage/{id}', [\App\Http\Controllers\Api\Admin\StudentController::class, 'updateDokumen']);

        Route::apiResource('/absence', \App\Http\Controllers\Api\Admin\AttendanceController::class);

        Route::post('/absence/update', [\App\Http\Controllers\Api\Admin\AttendanceController::class, 'update']);
        Route::get('/parent/absence', [\App\Http\Controllers\Api\Admin\AttendanceController::class, 'getStudent']);
        Route::get('/parentabsence/{parentId}', [\App\Http\Controllers\Api\Admin\AttendanceController::class, 'getStudent']);
        Route::get('/absenSiswa', [\App\Http\Controllers\Api\Admin\AttendanceController::class, 'indexRole']);
        Route::get('/absenSiswaOnly', [\App\Http\Controllers\Api\Admin\AttendanceController::class, 'IndexStudent']);

        Route::apiResource('/jurnal', \App\Http\Controllers\Api\Admin\ActivityController::class);
        Route::get('/StudentOnly', [\App\Http\Controllers\Api\Admin\ActivityController::class, 'indexStudentOnly']);
        Route::get('/student-jurnal', [\App\Http\Controllers\Api\Admin\ActivityController::class, 'indexStudentOnly' ]);
        Route::get('/indexRole-jurnal', [\App\Http\Controllers\Api\Admin\ActivityController::class, 'indexRole' ]);


        Route::apiResource('/penilaian', \App\Http\Controllers\Api\Admin\EvaluationController::class);
        Route::apiResource('/panduan', \App\Http\Controllers\Api\Admin\GuideController::class);
        Route::apiResource('/jadwal', \App\Http\Controllers\Api\Admin\ScheduleController::class);

      

        Route::get('/byrole', [\App\Http\Controllers\Api\Admin\EvaluationController::class, 'IndexRole']);
        Route::get('/UserIndexbyrole', [\App\Http\Controllers\Api\Admin\UserController::class, 'indexbyRole']);
        Route::get('/attendance/role', [\App\Http\Controllers\Api\Admin\AttendanceController::class, 'indexrole']);

        Route::post('/storeStudentandParent', [UserController::class, 'storeStudentWithParent']);

        Route::post('/import', [UserController::class, 'import']);
        Route::get('/export-users', [UserController::class, 'export']);


    });

});

