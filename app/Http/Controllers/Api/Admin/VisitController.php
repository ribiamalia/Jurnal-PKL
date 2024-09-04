<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\VisitResource;
use Illuminate\Http\Request;
use App\Models\Visits;
use App\Models\Industry;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VisitController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'industri_id' => 'required|exists:industries,id',
            'visitDate' => 'required|date',
            'visitReport' => 'required|string',
            'image' => 'required|image'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

       
        $industry = Industry::find($request->industri_id);

        if (!$industry) {
            return response()->json(['message' => 'Industri tidak ditemukan.'], 404);
        }

       
        $duration = $industry->duration;

        // Mendapatkan jumlah visit yang sudah dilakukan oleh user di industri tersebut
        $visitCount = Visits::where('user_id', $request->user_id)
            ->where('industri_id', $request->industri_id)
            ->count();

      
        if (($duration <= 3 && $visitCount >= 3) || ($duration > 3 && $visitCount >= 4)) {
            return response()->json(['message' => 'Anda telah mencapai batas kunjungan untuk industri ini.'], 403);
        }

        $imagePath = $request->file('image')->store('visit_images', 'public');

        // Membuat visit baru
        $visit = Visits::create([
          'user_id'       => auth()->guard('api')->user()->id,
            'industri_id' => $request->industri_id,
            'visitDate' => $request->visitDate,
            'visitReport' => $request->visitReport,
            'image' => $imagePath,
        ]);

        return response()->json(['message' => 'Kunjungan berhasil disimpan.', 'visit' => $visit], 201);
    }

    public function update(Request $request , $id)
    {
      $validator = Validator::make($request->all(), [
        'industri_id' => 'required|exists:industries,id',
        'visitDate' => 'required|date',
        'visitReport' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $visit = Visits::find($id);

    if ($visit) {
      $visit->update([
  
        'user_id'       => auth()->guard('api')->user()->id,
            'industri_id' => $request->industri_id,
            'visitDate' => $request->visitDate,
            'visitReport' => $request->visitReport,
  
      ]);
  
      return new VisitResource(true, 'Data Kunjungan Berhasil Diperbarui!', $visit);
    }
    return new VisitResource(true, 'Data kunjungan Berhasil Diperbarui!', null);
    }

    
    public function index()
    {
        // Mendapatkan daftar academic programs dari database
        $visit = Visits::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            // Maka tambahkan kondisi WHERE untuk mencari academic programs berdasarkan nama
            $query->where('name', 'like', '%' . request()->search . '%');
        })->with('users', 'industries')->oldest() // Mengurutkan academic programs dari yang terbaru
        ->paginate(10); // Membuat paginasi dengan 5 item per halaman

        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $visit->appends(['search' => request()->search]);

        // Mengembalikan response dalam bentuk DepartemenResource (asumsi resource sudah didefinisikan)
        return new VisitResource(true, 'List Data Kunjungan', $visit);
}

public function show($id)
{
    
    $visit = Visits::with('users.students')->find($id);

   
    if (!$visit) {
        return response()->json(['message' => 'Kunjungan tidak ditemukan.'], 404);
    }

    // Mendapatkan industri_id dari visit yang ditemukan
    $industri_id = $visit->industri_id;

    // Mendapatkan semua siswa yang terkait dengan industri_id yang sama
    $students = Student::whereHas('users.visits', function($query) use ($industri_id) {
        $query->where('industri_id', $industri_id);
    })->get();

    // Mengembalikan response dengan data siswa yang terkait
    return response()->json([
        'message' => 'Data siswa terkait berhasil ditemukan.',
        'industri_id' => $industri_id,
        'students' => $students
    ], 200);
}

public function destroy($id)
{
    // Find IdentitasSekolah by ID
    $visit = Visits::findOrFail($id);

    Storage::disk('public')->delete('visit_images/' . basename($visit->image));

    // Delete IdentitasSekolah
    if($visit->delete()) {
        // Return success with Api Resource
        return new VisitResource(true, 'Laporan Kunjungan Berhasil di Hapus!', null);
    }

}

public function UpdateImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'nullable|file|mimes:jpeg,png,jpg,pdf',
           
        ]);
          // Temukan submission yang akan diedit
    $visit = Visits::find($id);

    // Jika submission tidak ditemukan, kembalikan respons gagal
    if (!$visit) {
        return response()->json(['success' => false, 'message' => 'Kunjungan tidak ditemukan.'], 404);
    }

    if ($request->hasFile('image')) {
        if ($visit->image) {
            Storage::disk('public')->delete('visit_images/' . basename($visit->image));
        }
        $visit->image = $request->file('image')->store('visit_images', 'public');
        Log::info('Dokumen yang diunggah:', ['path' => $visit->image]);
    }
    $visit->save();

    Log::info('Updated image:', $visit->toArray());

    // Return success response
    return response()->json(['success' => true, 'message' => 'Dokumen berhasil diperbarui!', 'data' => $visit], 200);


    }



    
}
