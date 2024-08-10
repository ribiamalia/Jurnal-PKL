<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Http\Resources\PermissionResource;

class PermissionController extends Controller
{
    /**
     * index
     * 
     * @return void
     */
    public function index()
    {
        //get permission
        $permissions = Permission::when(request()->search, function($permissions){
            $permissions = $permissions->where('name', 'like', '%'. request()->search . '%');
        })->latest()->paginate(5);

        //append query string to pagination links
        $permissions->appends(['search' => request()->search]);

        //return with Api Resource
        return new PermissionResource(true, 'List Data Permissions', $permissions);
    }

    /**
     * 
     * all
     * 
     * @return void
     */
    public function all()
    {
        //get permisssions
        $permissions = Permission::latest()->get();

        //return with Api Resource
        return new PermissionResource(true, 'List Data Permissions', $permissions);
    }
}
