<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //get roles
        $roles = Role::when(request()->search, function($roles) {
            $roles = $roles->where('name', 'like', '%'. request()->search) . '%';
        })->with('permissions')->latest()->paginate(5);
        
        $roles->appends(['search' => request()->search]);

        return new RoleResource(true, 'List Data Roles', $roles);
     }

     //store
     public function store(Request $request )
     {
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'permissions'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //create role
        $role = Role::create(['name' => $request->name]);

        $role->givePermissionTo($request->permissions);

        if($role) {
            return new RoleResource(true, 'Data Role Berhasil Disimpan', $role);
        }

        return new RoleResource(false, 'Data Role Gagal Disimpan!', null);
     }

     //show
     public function show($id)
     {
        //get role
        $role = Role::with('permissions')->findOrFail( $id);

        if($role) {
            return new RoleResource(true, 'Detail Data Role!', $role);
        }

        return new RoleResource(false, 'Detail Data Role Gagal Ditemikan!', null);
     }

     //update
     public function update(Request $request, Role $role) 
     {
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'permissions'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //update
        $role->update(['name' => $request->name]);

        $role->syncPermissions($request->permissions);
        
        if($role) {
            return new RoleResource(true, 'Data Role Berhasil Diupdate!', $role);
        }

        return new RoleResource(false, 'Data Role Gagal Diupdate', null);
     }

     //Delete
     public function destroy($id)
     {
        //find role by id
        $role = Role::findOrFail($id);

        if($role->delete()) {
            return new RoleResource(true, 'Data Role Berhasil Dihapus', null);
        }

        return new RoleResource(false, 'Data Role Gagal Dihapus!', null);

     }

     //all
     public function all()
     {
        //get roles
        $roles = Role::latest()->get();

        return new RoleResource(true, 'List Data Roles', $roles);
        
     }


}
