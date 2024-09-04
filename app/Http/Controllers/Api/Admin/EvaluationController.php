<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Http\Resources\ActivityResource;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\EvaluationResource;

class EvaluationController extends Controller
{
    public function store(Request $request)
    {
        // Validasi data request
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'skills' => 'required|string',
            'score' => 'required|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Ambil user yang sedang login
        $user = auth()->guard('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Ambil industri terkait dari user yang sedang login
        $industry = $user->industries; // Menggunakan relasi hasOne dengan nama metode 'industry'

        if (!$industry) {
            return response()->json([
                'success' => false,
                'message' => 'Industry not found for this user'
            ], 404);
        }

        // Membuat data evaluasi
        $evaluation = Evaluation::create([
            'student_id' => $request->student_id,
            'industri_id' => $industry->id,
            'skills' => $request->skills,
            'score' => $request->score,
        ]);

        if ($evaluation) {
            return new ActivityResource(true, 'Penilaian berhasil disimpan', $evaluation);
        }

        return new ActivityResource(false, 'Penilaian gagal disimpan', null);
    }

    public function update(Request $request, $id)
{
    // Validasi data request
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|exists:students,id',
        'skills' => 'required|string',
        'score' => 'required|numeric|min:0|max:100'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation Errors',
            'errors' => $validator->errors()
        ], 422);
    }

    // Ambil user yang sedang login
    $user = auth()->guard('api')->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not authenticated'
        ], 401);
    }

    // Ambil industri terkait dari user yang sedang login
    $industry = $user->industries; // Menggunakan relasi hasOne dengan nama metode 'industry'

    if (!$industry) {
        return response()->json([
            'success' => false,
            'message' => 'Industry not found for this user'
        ], 404);
    }

    // Cari evaluasi berdasarkan ID dan industri yang terkait
    $evaluation = Evaluation::where('id', $id)->where('industri_id', $industry->id)->first();

    if (!$evaluation) {
        return response()->json([
            'success' => false,
            'message' => 'Evaluation not found'
        ], 404);
    }

    // Update data evaluasi
    $evaluation->update([
        'student_id' => $request->student_id,
        'skills' => $request->skills,
        'score' => $request->score,
    ]);

    return new ActivityResource(true, 'Penilaian berhasil diperbarui', $evaluation);
}

public function show($id)
{
    $evaluation = Evaluation::with( 'industries', 'students')->find($id);

    if($evaluation) {
        //return succes with Api Resource
        return new ActivityResource(true, 'Detail Peniaian!', $evaluation);
    }

    //return failed with Api Resource
    return new ActivityResource(false, 'Detail penilaian', null);
}

public function index()
{
    // Mendapatkan daftar academic programs dari database
    $evaluation = Evaluation::when(request()->search, function($query) {
        // Jika ada parameter pencarian (search) di URL
        // Maka tambahkan kondisi WHERE untuk mencari academic programs berdasarkan nama
        $query->where('name', 'like', '%' . request()->search . '%');
    })->with('industries', 'students')->latest() // Mengurutkan academic programs dari yang terbaru
    ->paginate(10); // Membuat paginasi dengan 5 item per halaman

    // Menambahkan parameter pencarian ke URL pada hasil paginasi
    $evaluation->appends(['search' => request()->search]);

    // Mengembalikan response dalam bentuk DepartemenResource (asumsi resource sudah didefinisikan)
    return new ActivityResource(true, 'List Penilaian', $evaluation);
}

public function destroy($id)
{
    // Find IdentitasSekolah by ID
    $evaluation = Evaluation::findOrFail($id);

    // Delete IdentitasSekolah
    if($evaluation->delete()) {
        // Return success with Api Resource
        return new EvaluationResource(true, 'Penilaian Berhasil di Hapus!', null);
    }
}

}