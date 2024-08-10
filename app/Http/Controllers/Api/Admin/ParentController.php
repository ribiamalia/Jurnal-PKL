<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Parents;
use App\Http\Resources\ParentResource;

class ParentController extends Controller
{
    public function store(Request $request) 
    {

        $validator = Validator::make($request->all(), [
                'nama' => 'required' ,
                'gender' => 'required',
                'placeOfBirth' => 'required',
                 'dateOfBirth' => 'required|date',
                 'alamat' => 'required',
                 'occupation' => 'required',
                'phoneNumber' => 'required',
                 'user_id' => 'required|exists:users,id',    
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            };

            $parent = Parents::create ([
                'nama' => $request->nama ,
                'gender' => $request->gender,
                'placeOfBirth' => $request->placeOfBirth,
                 'dateOfBirth' => $request->dateOfBirth,
                 'alamat' => $request->alamat,
                 'occupation' => $request->occupation,
                'phoneNumber' => $request->phoneNumber,
                 'user_id' => $request->user_id,    
  
        ]);
        
        if($parent) {
            return new ParentResource(true, 'Data Orang Tua berhasil ditambahkan', $parent);
        }

        return new ParentResource(false, 'Data Guru gagal ditambahkan', null);
    }

    public function update(Request $request, $id) 
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required' ,
            'gender' => 'required',
            'placeOfBirth' => 'required',
             'dateOfBirth' => 'required|date',
             'alamat' => 'required',
             'occupation' => 'required',
            'phoneNumber' => 'required',
             'user_id' => 'required|exists:users,id',    
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        };

        $parent = Parents::find($id);

        if ($parent) {
            $parent->update([
                'nama' => $request->nama ,
                'gender' => $request->gender,
                'placeOfBirth' => $request->placeOfBirth,
                 'dateOfBirth' => $request->dateOfBirth,
                 'alamat' => $request->alamat,
                 'occupation' => $request->occupation,
                'phoneNumber' => $request->phoneNumber,
                 'user_id' => $request->user_id,    
  
        ]);


        if($parent) {
            return new ParentResource(true, 'Data Orang Tua berhasil diubah', $parent);
        }

        return new ParentResource(false, 'Data Guru gagal diubah', null);
    }
}

    public function show($id)
    {
        $parent = Parents::find($id);

        if($parent) {
            //return succes with Api Resource
            return new ParentResource(true, 'Detail Data Orang Tua!', $parent);
        }

        //return failed with Api Resource
        return new ParentResource(false, 'Detail Data Orang Tua Tidak Ditemukan!', null);
    }

    public function index()
    {
        // Mendapatkan daftar academic programs dari database
        $parent = Parents::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            // Maka tambahkan kondisi WHERE untuk mencari academic programs berdasarkan nama
            $query->where('name', 'like', '%' . request()->search . '%');
        })->with('users')->oldest() // Mengurutkan academic programs dari yang terbaru
        ->paginate(5); // Membuat paginasi dengan 5 item per halaman

        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $parent->appends(['search' => request()->search]);

        // Mengembalikan response dalam bentuk DepartemenResource (asumsi resource sudah didefinisikan)
        return new ParentResource(true, 'List Data Guru', $parent);
}

}