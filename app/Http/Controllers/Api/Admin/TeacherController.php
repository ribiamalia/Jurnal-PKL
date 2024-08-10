<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Http\Resources\StudentResources;
use App\Http\Resources\TeacherResources;
use App\Models\Departemen;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class TeacherController extends Controller
{
    public function store(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'user_id'   => 'required',
            'no_hp' => 'required',
            'departemen_id' => 'required'

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $teacher = Teacher::create ([
            'name'  => $request->name,
            'user_id'   => $request->user_id,
            'no_hp' => $request->no_hp,
            'departemen_id' => $request->departemen_id,

        ]);

        if($teacher) {
            return new TeacherResources(true, 'Data guru berhasil ditambahkan', $teacher);
        }

        return new TeacherResources(false, 'Data Guru gagal ditambahkan', null);
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'user_id'   => 'required|exists:users,id',
            'no_hp' => 'required',
            'departemen_id' => 'required|exists:departemens,id'

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $teacher = Teacher::find($id);

        if ($teacher) {
            $teacher->update([
                'name' => $request->name,
                'user_id' => $request->user_id,
                'departemen_id' => $request->departemen_id,
                'no_hp' => $request->no_hp,
            ]);
    
            // Return success with Api Resource
            return new TeacherResources(true, 'Data Guru Berhasil Diperbarui!', $teacher);
        }
        return new TeacherResources(true, 'Data Guru Berhasil Diperbarui!', null);

    }

    public function show($id)
    {
        $teacher = Teacher::find($id);

        if($teacher) {
            //return succes with Api Resource
            return new TeacherResources(true, 'Detail Data Jurusan!', $teacher);
        }

        //return failed with Api Resource
        return new TeacherResources(false, 'Detail Data Jurusan Tidak Ditemukan!', null);
    }

    public function index()
    {
        // Mendapatkan daftar academic programs dari database
        $teacher = Teacher::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            // Maka tambahkan kondisi WHERE untuk mencari academic programs berdasarkan nama
            $query->where('name', 'like', '%' . request()->search . '%');
        })->with('users')->with('departements')->oldest() // Mengurutkan academic programs dari yang terbaru
        ->paginate(5); // Membuat paginasi dengan 5 item per halaman

        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $teacher->appends(['search' => request()->search]);

        // Mengembalikan response dalam bentuk DepartemenResource (asumsi resource sudah didefinisikan)
        return new TeacherResources(true, 'List Data Guru', $teacher);
    }


}
