<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Industry;
use App\Http\Resources\IndustriResource;


class IndustriController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'name' => 'required',
        'bidang' => 'required',
        'alamat' => 'required',
       'longitude' => 'required',
       'latitude' => 'required',
        'industryMentorName' => 'required',
        'industryMentorNo' => 'required',
        'duartion'  => 'nullable'

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $industri = Industry::create([
            'user_id' => $request->user_id,
        'name' => $request->name,
        'bidang' => $request->bidang,
        'alamat' => $request->alamat,
       'longitude' => $request->longitude,
       'latitude' => $request->latitude,
        'industryMentorName' => $request->industryMentorName,
        'industryMentorNo' => $request->industryMentorNo,
        'duration' => $request->duration,

        ]);

        if($industri) {
            return new IndustriResource(true, 'Data Industri berhail disimpan', $industri);
        }

        return new IndustriResource(false, 'Data Industri gagal disimpan', null);
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required',
            'bidang' => 'required',
            'alamat' => 'required',
           'longitude' => 'required',
           'latitude' => 'required',
            'industryMentorName' => 'required',
            'industryMentorNo' => 'required',
            'duration'      => 'nullable'
    
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $industri = Industry::find($id);

        if ($industri) {
            $industri->update([
                'user_id' => $request->user_id,
                'name' => $request->name,
                'bidang' => $request->bidang,
                'alamat' => $request->alamat,
               'longitude' => $request->longitude,
               'latitude' => $request->latitude,
                'industryMentorName' => $request->industryMentorName,
                'industryMentorNo' => $request->industryMentorNo,
                'duartion' => $request->duration,
  
        ]);


        if($industri) {
            return new IndustriResource(true, 'Data Orang Tua berhasil diubah', $industri);
        }

        return new IndustriResource(false, 'Data Guru gagal diubah', null);
    }
    }

    public function show($id)
    {
        $industry = Industry::with('users')->find($id);

        if($industry) {
            //return succes with Api Resource
            return new IndustriResource(true, 'Detail Industri!', $industry);
        }

        //return failed with Api Resource
        return new IndustriResource(false, 'Detail Industri Tidak Ditemukan!', null);
    }

    public function index()
    {
        // Mendapatkan daftar academic programs dari database
        $industry = Industry::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            // Maka tambahkan kondisi WHERE untuk mencari academic programs berdasarkan nama
            $query->where('name', 'like', '%' . request()->search . '%');
        })->with('users')->oldest() // Mengurutkan academic programs dari yang terbaru
        ->paginate(10); // Membuat paginasi dengan 5 item per halaman

        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $industry->appends(['search' => request()->search]);

        // Mengembalikan response dalam bentuk DepartemenResource (asumsi resource sudah didefinisikan)
        return new IndustriResource(true, 'List Industri', $industry);
}
    
}
