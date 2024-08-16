<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Departemen;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\DepartemenResources;
use App\Models\Classes;
use App\Models\User;
use Illuminate\Support\Str;

class DepartemenController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|unique:departemens',
        

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $departemen = Departemen::create([
            'name'  => $request->name,
            'slug' => Str::slug($request->name, '-'),
        ]);

        if($departemen) {
            return new DepartemenResources(true, 'Data Jurusan berhail disipan', $departemen);
        }

        return new DepartemenResources(false, 'Data Jurusan gagal disimpan', null);
    }

    public function index()
    {
        // Mendapatkan daftar academic programs dari database
        $departemen = Departemen::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            // Maka tambahkan kondisi WHERE untuk mencari academic programs berdasarkan nama
            $query->where('name', 'like', '%' . request()->search . '%');
        })->oldest() // Mengurutkan academic programs dari yang terbaru
        ->paginate(5); // Membuat paginasi dengan 5 item per halaman

        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $departemen->appends(['search' => request()->search]);

        // Mengembalikan response dalam bentuk DepartemenResource (asumsi resource sudah didefinisikan)
        return new DepartemenResources(true, 'List Data Jurusan', $departemen);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:departemens,name,' . $id,
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        // Temukan dan perbarui kelas jika ditemukan
        $departemen = Departemen::find($id);
    
        if ($departemen) {
            $departemen->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name, '-'),

            ]);
    
            // Return success with Api Resource
            return new DepartemenResources(true, 'Data Jurusan Berhasil Diperbarui!', $departemen);
        }
    
        // Return failed with Api Resource
        return new DepartemenResources(false, 'Data Jurusan Tidak Ditemukan!', null);
    }

    public function show($id)
    {
        $departemens = Departemen::find($id);

        if($departemens) {
            //return succes with Api Resource
            return new DepartemenResources(true, 'Detail Data Jurusan!', $departemens);
        }

        //return failed with Api Resource
        return new DepartemenResources(false, 'Detail Data Jurusan Tidak Ditemukan!', null);
    }

    public function destroy($id)
    {
        // Temukan departemen
        $departemen = Departemen::find($id);

        if ($departemen) {
            // Set departemen_id di tabel classes menjadi null
            Classes::where('departemen_id', $id)->update(['departemen_id' => null]);
            User::where('departemen_id', $id)->update(['departemen_id' => null]);

            // Hapus departemen
            $departemen->delete();

            // Return success response
            return new DepartemenResources(true, 'Data Jurusan Berhasil Dihapus!', null);
        }

        // Return failed response jika departemen tidak ditemukan
        return new DepartemenResources(false, 'Data Jurusan Tidak Ditemukan!', null);
    }

}
