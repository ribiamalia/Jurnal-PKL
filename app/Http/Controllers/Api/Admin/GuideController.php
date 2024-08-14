<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guide;
use App\Http\Resources\GuideResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class GuideController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|exists:users,id',
            'dokumen'   => 'required|file|mimes:pdf,jpg,jpeg,png',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        };

        $dokumen = null;
    if ($request->hasFile('dokumen')) {
        $dokumen = $request->file('dokumen')->store('guides', 'public');
    }




        $guide = Guide::create ([
            'user_id' => auth()->guard('api')->user()->id,
            'dokumen'   => $dokumen
        ]);

        if($guide) {
            return new GuideResource(true, 'Info Panduan PKL berhasil disimpan', $guide);
        }

        return new GuideResource(false, 'Info Panduan PKL gagal disimpan', null);


    }

    public function update(Request $request, $id) {
        $request->validate([
            'dokumen' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg',
           
        ]);
          // Temukan submission yang akan diedit
    $guide = Guide::find($id);
  
    // Jika submission tidak ditemukan, kembalikan respons gagal
    if (!$guide) {
        return response()->json(['success' => false, 'message' => 'Info Panduan tidak ditemukan.'], 404);
    }
  
    if ($request->hasFile('dokumen')) {
        if ($guide->dokumen) {
            Storage::disk('public')->delete($guide->dokumen);
        }
        $guide->dokumen = $request->file('dokumen')->store('guides', 'public');
        Log::info('Dokumen yang diunggah:', ['path' => $guide->dokumen]);
    }
    $guide->save();
  
    Log::info('Updated Dokumen:', $guide->toArray());
  
    // Return success response
    return response()->json(['success' => true, 'message' => 'Dokumen berhasil diperbarui!', 'data' => $guide], 200);
    }

    public function show($id)
    {
        $guide = Guide::find($id);

        if($guide) {
            //return succes with Api Resource
            return new GuideResource(true, 'Detail Info panduan!', $guide);
        }

        //return failed with Api Resource
        return new GuideResource(false, 'Detail info panduan Tidak Ditemukan!', null);
    }

    public function index()
    {
        // Mendapatkan daftar academic programs dari database
        $guide = Guide::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            // Maka tambahkan kondisi WHERE untuk mencari academic programs berdasarkan nama
            $query->where('name', 'like', '%' . request()->search . '%');
        })->with('user')->oldest() // Mengurutkan academic programs dari yang terbaru
        ->paginate(5); // Membuat paginasi dengan 5 item per halaman

        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $guide->appends(['search' => request()->search]);

        // Mengembalikan response dalam bentuk DepartemenResource (asumsi resource sudah didefinisikan)
        return new GuideResource(true, 'List Info panduan', $guide);
}

}