<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Classes;
use App\Http\Resources\ClassesResources;
use Illuminate\Support\Str;

class ClassesController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'departemen_id' => 'nullable'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $classes = Classes::create ([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-'),
            'departemen_id' => $request->departemen_id,
        ]);

        if($classes)
        {
            return new ClassesResources(true, 'Data Kelas berhasil ditambahkan', $classes);
        }

        return new ClassesResources(false, 'Data kelas gagal ditambahkan', null);
    }

    public function index()
    {
        // Mendapatkan daftar classrooms dari database
        $classes = Classes::when(request()->search, function ($query) {
            // Jika ada parameter pencarian (search) di URL
            // Maka tambahkan kondisi WHERE untuk mencari classrooms berdasarkan nama
            $query->where('name', 'like', '%' . request()->search . '%');
        })
        ->with('departemens') // Mengambil relasi academicprogram // Menghitung jumlah siswa untuk setiap kelas
        ->withCount('students')
        ->oldest() // Mengurutkan classrooms dari yang terbaru
        ->paginate(10); // Membuat paginasi dengan 5 item per halaman
    
        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $classes->appends(['search' => request()->search]);
    
        // Mengembalikan response dalam bentuk ClassroomResource (asumsi resource sudah didefinisikan)
        return new ClassesResources(true, 'List Data kelas', $classes);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:classes,name,' . $id,
            'departemen_id' => 'nullable',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        // Temukan dan perbarui kelas jika ditemukan
        $classes = Classes::find($id);
    
        if ($classes) {
            $classes->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name, '-'),
                'departemen_id' => $request->departemen_id,
            ]);
    
            // Return success with Api Resource
            return new ClassesResources(true, 'Data Kelas Berhasil Diperbarui!', $classes);
        }
    
        // Return failed with Api Resource
        return new ClassesResources(false, 'Data Kelas Tidak Ditemukan!', null);
    }


    public function show($id)
    {
        $classes = Classes::find($id);

        if($classes) {
            //return succes with Api Resource
            return new ClassesResources(true, 'Detail Data Kelas!', $classes);
        }

        //return failed with Api Resource
        return new ClassesResources(false, 'Detail Data Kelas Tidak Ditemukan!', null);
    }

    
}
    

