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
use Carbon\Carbon;


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
        'description'   => 'nullable'
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()], 422);
    }

    // Ambil student terkait dari user yang sedang login
    $student = Student::where('user_id', auth()->guard('api')->user()->id)->first();
    if (!$student) {
        return response()->json(['error' => 'Student data not found'], 404);
    }

   
    $currentDate = Carbon::now()->toDateString(); // Tanggal hari ini
    $currentTime = Carbon::now()->toTimeString(); // Waktu saat ini

    $attendanceCount = Attendance::where('user_id', auth()->guard('api')->user()->id)
                            ->where('date', $currentDate)
                            ->count();

    if ($attendanceCount == 0) {
        // Simpan absensi masuk
        $image = $request->file('image')->store('attendances', 'public');

        $attendance = Attendance::create([
            'date' => $currentDate,
            'departureTime' => $currentTime,
            'absenceReason' => $request->absenceReason,
            'image' => $image,
            'user_id' => auth()->guard('api')->user()->id,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'status' => 'Masuk',
        ]);

        return new AttendanceResource(true, 'Absen masuk berhasil disimpan', $attendance);
    } elseif ($attendanceCount == 1) {

        $imagePulang = $request->file('image')->store('attendances', 'public');
        // Simpan absensi pulang
        $attendance = Attendance::create([
            'date' => $currentDate,
            'departureTime' => $currentTime,
            'absenceReason' => $request->absenceReason,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'user_id' => auth()->guard('api')->user()->id,
            'status' => 'Pulang',
            'image' => $imagePulang,
        ]);

        return new AttendanceResource(true, 'Absen pulang berhasil disimpan', $attendance);
    } else {
        return response()->json(['error' => 'Absen untuk hari ini sudah dilakukan dua kali'], 422);
    }
}

    
    
    
    

public function index()
{
    $search = request()->search;

    $attendance = Attendance::when($search, function ($query) use ($search) {
        
        $query->whereHas('users.students', function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%');
        });
    })
    ->with('users.students.classes', 'users.students.teachers', 'users.students.departements', 'users.students.parents', 'users.students.industries') 
    ->orderBy('date', 'asc') 
    ->orderBy('departureTime', 'asc') 
    ->get() 
    ->groupBy(function ($item) {
     
        return $item->user_id . '-' . $item->date;
    });

   
    $formattedData = $attendance->map(function ($group) {
        return [
            'date' => $group->first()->date,
            'user_id' => $group->first()->user_id,
            'entries' => $group->groupBy('status')->map(function ($statusGroup) {
                return [
                    'status' => $statusGroup->first()->status,
                    'entries' => $statusGroup->all(),
                ];
            }),
        ];
    });

  
    return new AttendanceResource(true, 'List Data Kehadiran', $formattedData);
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
        $user = auth()->guard('api')->user();
    
        // Menampilkan data kehadiran berdasarkan peran pengguna
        $attendance = Attendance::when($user->hasRole('orang tua'), function($query) use ($user) {
            // Jika pengguna memiliki peran "orang tua"
            $query->whereHas('users.students', function($query) use ($user) {
                // Mengambil data siswa yang memiliki orang tua yang sesuai
                $query->whereHas('parents', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            });
        })
        ->when($user->hasRole('guru'), function($query) use ($user) {
            // Jika pengguna memiliki peran "guru"
            $query->whereHas('users.students', function($query) use ($user) {
                // Mengambil data siswa yang memiliki guru yang sesuai
                $query->whereHas('teachers', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            });
        })
        ->when($user->hasRole('industri'), function($query) use ($user) {
            // Jika pengguna memiliki peran "industri"
            $query->whereHas('users.students', function($query) use ($user) {
                // Mengambil data siswa yang memiliki industri yang sesuai
                $query->whereHas('industries', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            });
        })
        ->with('users.students.classes', 'users.students.teachers', 'users.students.departements', 'users.students.parents', 'users.students.industries') // Mengambil relasi yang diperlukan
        ->orderBy('date', 'asc') // Mengurutkan berdasarkan tanggal
        ->orderBy('departureTime', 'asc') // Mengurutkan berdasarkan waktu
        ->get() // Mengambil semua data
        ->groupBy(function ($item) {
            // Mengelompokkan data berdasarkan user_id dan tanggal
            return $item->user_id . '-' . $item->date;
        });
    
        // Format data untuk menampilkan data masuk dan pulang secara terstruktur
        $formattedData = $attendance->map(function ($group) {
            return [
                'date' => $group->first()->date,
                'user_id' => $group->first()->user_id,
                'entries' => $group->groupBy('status')->map(function ($statusGroup) {
                    return [
                        'status' => $statusGroup->first()->status,
                        'entries' => $statusGroup->all(),
                    ];
                }),
            ];
        });
    
        // Mengembalikan response dalam bentuk AttendanceResource
        return new AttendanceResource(true, 'List Data Kehadiran', $formattedData);
    }
    

public function indexStudent()
{
    // Ambil user yang sedang login
    $userId = auth()->guard('api')->user()->id;

    // Mendapatkan daftar data kehadiran hanya untuk user yang sedang login
    $attendance = Attendance::where('user_id', $userId)
    ->with('users.students.classes', 'users.students.teachers', 'users.students.departements', 'users.students.parents', 'users.students.industries')
        ->orderBy('date', 'asc') // Mengurutkan berdasarkan tanggal
        ->orderBy('departureTime', 'asc') // Mengurutkan berdasarkan waktu
        ->get() // Mengambil semua data
        ->groupBy(function ($item) {
            // Mengelompokkan data berdasarkan user_id dan tanggal
            return $item->user_id . '-' . $item->date;
        });

    // Format data untuk menampilkan data masuk dan pulang secara terstruktur
    $formattedData = $attendance->map(function ($group) {
        return [
            'date' => $group->first()->date,
            'user_id' => $group->first()->user_id,
            'entries' => $group->groupBy('status')->map(function ($statusGroup) {
                return [
                    'status' => $statusGroup->first()->status,
                    'entries' => $statusGroup->all(),
                ];
            }),
        ];
    });

    // Mengembalikan response dalam bentuk AttendanceResource
    return new AttendanceResource(true, 'List Data Kehadiran', $formattedData);
}


public function update(Request $request, $id)
{
    // Cek apakah pengguna yang sedang login memiliki peran 'industri'
    $user = auth()->guard('api')->user();

    if (!$user->hasRole('industri')) {
        return response()->json(['error' => 'Unauthorized. Only industry users can verify attendance.'], 403);
    }

    // Validasi input
    $validator = Validator::make($request->all(), [
        'verified' => 'required', // Kolom verified wajib dan harus berupa boolean
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()], 422);
    }

    // Cari data attendance berdasarkan ID
    $attendance = Attendance::find($id);

    if (!$attendance) {
        return response()->json(['error' => 'Attendance record not found'], 404);
    }

    // Update kolom verified
    $attendance->verified = $request->verified;
    $attendance->save();

    // Mengembalikan response dengan data yang sudah diupdate
    return new AttendanceResource(true, 'Attendance verification updated successfully', $attendance);
}

public function hasCheckedInToday()
{
    // Ambil user yang sedang login
    $userId = auth()->guard('api')->user()->id;

    // Ambil tanggal hari ini
    $currentDate = Carbon::now()->toDateString();

    // Cek apakah sudah ada absensi masuk (status: Masuk) untuk hari ini
    $checkIn = Attendance::where('user_id', $userId)
        ->where('date', $currentDate)
        ->where('status', 'Masuk')
        ->first();

    if ($checkIn) {
        // Cek apakah sudah ada absensi pulang (status: Pulang) untuk hari ini
        $checkOut = Attendance::where('user_id', $userId)
            ->where('date', $currentDate)
            ->where('status', 'Pulang')
            ->first();

        if ($checkOut) {
            // Jika absensi dengan status 'Pulang' ditemukan
            return response()->json(['message' => 'Anda sudah absen pulang hari ini, tidak bisa absen lagi.'], 200);
        }

        // Jika belum absen pulang, arahkan untuk absen pulang
        return response()->json(['message' => 'Pulang'], 200);
    }

    // Jika belum ada absensi masuk untuk hari ini
    return response()->json(['message' => 'Masuk'], 200);
}




    
}


