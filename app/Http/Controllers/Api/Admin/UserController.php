<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        // Memuat relasi 'students' saat mengambil data users
        $users = User::with('students')
            ->when(request()->search, function($query) {
                return $query->where('name', 'like', '%' . request()->search . '%');
            })
            ->latest()
            ->paginate(10);
    
        // Menambahkan parameter search ke paginasi
        $users->appends(['search' => request()->search]);
    
        // Mengembalikan response dengan UserResource
        return new UserResource(true, 'List Data User', $users);
    }
    

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'password'  => 'required|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //create user
        $user = User::create([
            'name'      => $request->name,
            'password'  => bcrypt($request->password)
        ]);

        //assign roles to user
        $user->assignRole($request->roles);

        if($user) {
            //return success with Api Resource
            return new UserResource(true, 'Data User Berhasil Disimpan', $user);
        }

        //return failed with Api Resource
        return new UserResource(false, 'Data User Gagal Disimpan!', null);
    }

    public function show($id)
     {
        $user = User::with('roles')->whereId($id)->first();

        if($user) {
            //return success with Api Resource
            return new UserResource(true, 'Detail Data User!', $user);
        }

        //return failed with Api Resource
        return new UserResource(false, 'Detail Data User Gagal Ditemukan!', null);
     }

    /**
     * Update the specified resource in storage.
     * 
     * @param \Illuminate\Http\Request  $request
     * @param   int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'password'  => 'confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if($request->password == "") {

            //update user without password
            $user->update([
                'name'      => $request->name,
            ]);

        } else {

            //update user with new password
            $user->update([
                'name'      => $request->name,
                'password'  => bcrypt($request->password)
            ]);

        }

        //assign roles to user
        $user->syncRoles($request->roles);

        if($user) {
            //return succes with Api Resource
            return new UserResource(true, 'Data User Berhasil Diupdate!', $user);
        }

        //return failed with Api Resource
        return new UserResource(false, 'Data User Gagal Diupdate!', null);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param   int $id
     * @return \Illuminateg\Http\Response
     */
    public function destroy(User $user)
    {
        if($user->delete()) {
            //return success with Api resource
            return new UserResource(true, 'Data User Berhasil Dihapus!', null);
        }

        //return failed with Api Resource
        return new UserResource(false, 'Data User Gagal Dihapus!', null);
    }

    


}
