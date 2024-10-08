<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AspekProduktif;
use App\Http\Resources\AspekProduktifResource;
use Illuminate\Support\Facades\Validator;

class AspekProduktifController extends Controller
{
    public function store(Request $request)
    {
        // Validasi data request
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'name'  => 'required',
            'score' => 'required|numeric|min:0|max:100',

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
        $produktif = AspekProduktif::create([
            'student_id' => $request->student_id,
            'industri_id' => $industry->id,
            'name' => $request->name,
            'score' => $request->score,
            
        ]);

        if ($produktif) {
            return new AspekProduktifResource(true, 'Penilaian berhasil disimpan', $produktif);
        }

        return new AspekProduktifResource(false, 'Penilaian gagal disimpan', null);
    }

    public function update(Request $request, $id)
{
    // Validasi data request
    $validator = Validator::make($request->all(), [
        'student_id' => 'required|exists:students,id',
        'name'  => 'required',
        'score' => 'required|numeric|min:0|max:100',

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
    $produktif = AspekProduktif::where('id', $id)->where('industri_id', $industry->id)->first();

    if (!$produktif) {
        return response()->json([
            'success' => false,
            'message' => 'AspekProduktif not found'
        ], 404);
    }

    // Update data evaluasi
    $produktif->update([
        'student_id' => $request->student_id,
        'name' => $request->name,
            'score' => $request->score,
    ]);

    return new AspekProduktifResource(true, 'Penilaian berhasil diperbarui', $produktif);
}

public function show($id)
{
    $produktif = AspekProduktif::with( 'industries', 'students')->find($id);

    if($produktif) {
        //return succes with Api Resource
        return new AspekProduktifResource(true, 'Detail Peniaian!', $produktif);
    }

    //return failed with Api Resource
    return new AspekProduktifResource(false, 'Detail penilaian', null);
}

public function index()
{
    // Mengambil data penilaian per student_id dan menyertakan relasi industries dan students
    $produktif = AspekProduktif::when(request()->search, function($query) {
        // Jika ada parameter pencarian (search) di URL
        $query->where('name', 'like', '%' . request()->search . '%');
    })
    ->with('students', 'industries') // Memuat relasi students dan industries
    ->get() // Mengambil semua data
    ->groupBy('student_id'); // Mengelompokkan data berdasarkan student_id

    // Menyiapkan array untuk output
    $result = [];

    // Looping setiap grup student_id
    foreach ($produktif as $studentId => $penilaian) {
        // Memasukkan data grup ke dalam array hasil
        $result[] = [
            'student_id' => $studentId, // ID Siswa
            'student_name' => $penilaian->first()->students->name, // Nama Siswa (mengambil nama dari relasi students)
            'industry_name' => $penilaian->first()->industries->name ?? 'N/A', // Nama Industri (mengambil dari relasi industries)
            'total_scores' => $penilaian->count(), // Total penilaian
            'average_score' => $penilaian->avg('score'), // Rata-rata nilai
            'scores' => $penilaian->map(function($item) {
                return [
                    'name' => $item->name, // Nama aspek produktif
                    'score' => $item->score // Nilai
                ];
            }) // Mengembalikan daftar nilai untuk setiap aspek
        ];
    }

    // Mengembalikan response JSON
    return response()->json([
        'success' => true,
        'message' => 'List Penilaian per Siswa',
        'data' => $result
    ], 200);
}




public function destroy($id)
{
    // Find IdentitasSekolah by ID
    $produktif = AspekProduktif::findOrFail($id);

    // Delete IdentitasSekolah
    if($produktif->delete()) {
        // Return success with Api Resource
        return new AspekProduktifResource(true, 'Penilaian Berhasil di Hapus!', null);
    }
}



public function indexPerSiswa(Request $request)
{
    // Mengambil data penilaian per student_id dan menyertakan relasi industries dan students
    $produktif = AspekProduktif::when($request->search, function($query) {
        // Jika ada parameter pencarian (search) di URL
        $query->where('name', 'like', '%' . request()->search . '%');
    })
    ->when($request->student_id, function($query) use ($request) {
        // Jika ada parameter student_id di URL
        $query->where('student_id', $request->student_id);
    })
    ->with('students', 'industries') // Memuat relasi students dan industries
    ->get() // Mengambil semua data
    ->groupBy('student_id'); // Mengelompokkan data berdasarkan student_id

    // Menyiapkan array untuk output
    $result = [];

    // Looping setiap grup student_id
    foreach ($produktif as $studentId => $penilaian) {
        // Memasukkan data grup ke dalam array hasil
        $result[] = [
            'student_id' => $studentId, // ID Siswa
            'student_name' => $penilaian->first()->students->name ?? 'N/A', // Nama Siswa (mengambil nama dari relasi students)
            'industry_name' => $penilaian->first()->industries->name ?? 'N/A', // Nama Industri (mengambil dari relasi industries)
            'total_scores' => $penilaian->count(), // Total penilaian
            'average_score' => $penilaian->avg('score'), // Rata-rata nilai
            'scores' => $penilaian->map(function($item) {
                return [
                    'name' => $item->name, // Nama aspek produktif
                    'score' => $item->score // Nilai
                ];
            }) // Mengembalikan daftar nilai untuk setiap aspek
        ];
    }

    // Mengembalikan response JSON
    return response()->json([
        'success' => true,
        'message' => 'List Penilaian per Siswa',
        'data' => $result
    ], 200);
}


}

