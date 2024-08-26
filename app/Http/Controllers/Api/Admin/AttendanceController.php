<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Attendance;
use App\Models\Parents;
use App\Models\Student;
use App\Models\Industry;
use App\Http\Resources\AttendanceResource;


class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'absenceReason' => 'required_if:arrivalTime,null',
            'image' => 'required_if:arrivalTime,null|image',
            'longitude' => 'required_if:arrivalTime,null',
            'latitude' => 'required_if:arrivalTime,null',
            'reason_2' => 'nullable|required_if:arrivalTime,null',
            'longitude_2' => 'nullable|required_if:arrivalTime,null',
            'latitude_2' => 'nullable|required_if:arrivalTime,null',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        // Ambil student terkait dari user yang sedang login
        $student = Student::where('user_id', auth()->guard('api')->user()->id)->first();
        if (!$student) {
            return response()->json(['error' => 'Student data not found'], 404);
        }
    
        // Ambil waktu saat ini
        $currentDate = now()->toDateString(); // Tanggal hari ini
        $currentTime = now()->toTimeString(); // Waktu saat ini
    
        // Cari absensi berdasarkan user_id dan tanggal hari ini
        $attendance = Attendance::where('user_id', auth()->guard('api')->user()->id)
                                ->where('date', $currentDate)
                                ->first();
    
        if ($attendance) {
            // Update absensi pulang
            $attendance->update([
                'arrivalTime' => $currentTime,
                'reason_2' => $request->reason_2,
                'longitude_2' => $request->longitude_2,
                'latitude_2' => $request->latitude_2,
            ]);
    
            return new AttendanceResource(true, 'Absen pulang berhasil disimpan', $attendance);
        } else {
            // Simpan absensi masuk
            $image = $request->file('image')->store('attendances', 'public');
    
            $attendance = Attendance::create([
                'date' => $currentDate,
                'departureTime' => $currentTime,
                'absenceReason' => $request->absenceReason,
                'image' => $image,
                'user_id' => auth()->guard('api')->user()->id,
                'longitude' => $request->longitude,
                'lotitude' => $request->lotitude,
            ]);
    
            return new AttendanceResource(true, 'Absen masuk berhasil disimpan', $attendance);
        }
    }
    
    
    
    

    public function index()
    {
        // Mendapatkan daftar academic programs dari database
        $attendance = Attendance::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            // Maka tambahkan kondisi WHERE untuk mencari academic programs berdasarkan nama
            $query->where('name', 'like', '%' . request()->search . '%');
        })->with('users')
        ->oldest() // Mengurutkan academic programs dari yang terbaru
        ->paginate(5); // Membuat paginasi dengan 5 item per halaman

        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $attendance->appends(['search' => request()->search]);

        // Mengembalikan response dalam bentuk AttendanceResource (asumsi resource sudah didefinisikan)
        return new AttendanceResource(true, 'List Data Kehadiran', $attendance);
    }

    public function show($id)
    {
        $attendance = Attendance::find($id);

        if($attendance) {
            //return succes with Api Resource
            return new AttendanceResource(true, 'Detail Data Jurusan!', $attendance);
        }

        //return failed with Api Resource
        return new AttendanceResource(false, 'Detail Data Jurusan Tidak Ditemukan!', null);
    }

    public function getStudent($parentId)
    {
        // Find the parent
        $parent = Parents::find($parentId);
    
        if (!$parent) {
            return response()->json(['message' => 'Parent not found'], 404);
        }
    
        // Get students associated with the parent
        $students = $parent->students;
    
        if ($students->isEmpty()) {
            return response()->json(['message' => 'No students found for this parent'], 404);
        }
    
        // Get user_ids from these students
        $userIds = $students->pluck('user_id');
        
        if ($userIds->isEmpty()) {
            return response()->json(['message' => 'No user IDs found for these students'], 404);
        }
    
        // Get attendance records for these user_ids
        $attendances = Attendance::whereIn('user_id', $userIds)->get();
    
        return response()->json($attendances);
    }

    public function indexRole()
{
    // Ambil user yang sedang login
    $user = auth()->guard('api')->user();

    // Inisialisasi variabel untuk menampung user_id siswa terkait
    $relatedUserIds = [];

    // Cek peran pengguna
    if ($user->hasRole('industri')) {
        // Jika pengguna adalah industri, ambil siswa yang terkait dengan industri tersebut
        $industry = $user->industries; // Asumsikan relasi hasOne dengan industri
        if ($industry) {
            $relatedUserIds = Student::where('industri_id', $industry->id)->pluck('user_id')->toArray();
        }
    } elseif ($user->hasRole('orang tua')) {
        // Jika pengguna adalah orang tua, ambil anak-anak mereka
        $relatedUserIds = $user->parents->students->pluck('user_id')->toArray();
    } elseif ($user->hasRole('guru')) {
        // Jika pengguna adalah guru, ambil siswa yang diajar oleh mereka
        $relatedUserIds = $user->teachers->students->pluck('user_id')->toArray();
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }

    // Dapatkan daftar kehadiran dari siswa terkait
    $attendances = Attendance::whereIn('user_id', $relatedUserIds)->get();

    // Kembalikan response dengan resource collection
    return AttendanceResource::collection($attendances);
}

    
    

}


