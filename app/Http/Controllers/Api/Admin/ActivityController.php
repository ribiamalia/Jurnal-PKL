<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Activity;
use App\Http\Resources\ActivityResource;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{

    public function indexRole()
{
    $user = auth()->guard('api')->user();

    // Mengambil data jurnal berdasarkan peran pengguna
    $activity = Activity::when($user->hasRole('orang_tua'), function($query) use ($user) {
        $query->whereHas('users.students', function($query) use ($user) {
            $query->whereHas('parents', function($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        });
    })
    ->when($user->hasRole('guru'), function($query) use ($user) {
        $query->whereHas('users.students', function($query) use ($user) {
            $query->whereHas('teachers', function($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        });
    })
    ->when($user->hasRole('industri'), function($query) use ($user) {
        $query->whereHas('users.students', function($query) use ($user) {
            $query->whereHas('industries', function($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        });
    })
    ->with('users.students.classes', 'users.students.teachers', 'users.students.departements', 'users.students.parents', 'users.students.industries') 
    ->latest() 
    ->paginate(15); 

    return new ActivityResource(true, 'List Data Activity siswa', $activity);
}


    public function indexStudentOnly()
    {
        // Mendapatkan daftar activity yang dibuat oleh user yang sedang login
        $activity = Activity::where('user_id', auth()->guard('api')->user()->id)
            ->with('users.students.classes', 'users.students.teachers', 'users.students.departements', 'users.students.parents', 'users.students.industries') // Mengambil relasi yang diperlukan
            ->latest() // Mengurutkan activity dari yang terbaru
            ->paginate(15); // Membuat paginasi dengan 15 item per halaman
    
        // Mengembalikan response dalam bentuk ActivityResource
        if($activity) {
        return new ActivityResource(true, 'List Data Activity', $activity);}

        return new ActivityResource(false, 'Gagal ditemukan!' , null);


    }
    

    
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'date' => 'required|date',
        'start_time' => 'required',
        'end_time' => 'required',
        'description' => 'required',
        'tools' => 'required',
      
        ]);

        if($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        };

        $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('activities', 'public');
    }

        $activity = Activity::create([
            'user_id'       => auth()->guard('api')->user()->id,
        'date' => $request->date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'description' => $request->description,
        'tools' => $request->tools,
    
        'image' => $imagePath,
        ]);

        if($activity) {
            return new ActivityResource(true, 'Daily Activity berhasil disimpan', $activity);
        }

        return new ActivityResource(false, 'Daily Activity gagal disimpan', null);

    }

    public function update(Request $request, $id)

    {
        $validator = Validator::make($request->all(), [
        'date' => 'required|date',
        'start_time' => 'required',
        'end_time' => 'required',
        'description' => 'required',
        'tools' => 'required',
    
        ]);

        if($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        };

        $activity = Activity::find($id);

        if($activity) {
            $activity->update([
            'user_id'       => auth()->guard('api')->user()->id,
        'date' => $request->date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'description' => $request->description,
        'tools' => $request->tools,
     
        ]);


            return new ActivityResource(true, 'Daily Activity berhasil disimpan', $activity);
        }

        return new ActivityResource(false, 'Daily Activity gagal disimpan', null);

    }

    public function destroy($id)
    {
        $activity = Activity::find($id);

        Storage::disk('public')->delete('activities/' . basename($activity->image));
        if($activity) {
            $activity->delete();
            return new ActivityResource(true, 'Daily Activity berhasil di hapus', null);
        }
        return new ActivityResource(false, 'Daily Activity gagal di hapus', null);
    }

    public function index()
    {
        // Mendapatkan daftar activity dari database dengan filter dan pengelompokan berdasarkan user_id
        $activity = Activity::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            $query->where('description', 'like', '%' . request()->search . '%');
        })
        ->when(request()->departemen_id, function($query) {
            // Jika ada parameter departemen_id di URL
            $query->whereHas('users.students', function($query) {
                $query->where('departemen_id', request()->departemen_id);
            });
        })
        ->when(request()->classes_id, function($query) {
            // Jika ada parameter classes_id di URL
            $query->whereHas('users.students', function($query) {
                $query->where('classes_id', request()->classes_id);
            });
        })
        ->with('users.students.classes', 'users.students.teachers', 'users.students.departements', 'users.students.parents', 'users.students.industries') // Mengambil relasi yang diperlukan
        ->groupBy('user_id') // Mengelompokkan data berdasarkan user_id
        ->latest() // Mengurutkan activity dari yang terbaru
        ->paginate(15); // Membuat paginasi dengan 15 item per halaman
    
        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $activity->appends(request()->only(['search', 'departemen_id', 'classes_id']));
    
        // Mengembalikan response dalam bentuk ActivityResource
        return new ActivityResource(true, 'List Data Activity', $activity);
    }
    
    
    

    public function show($id)
    {
        $activity = Activity::with('users.students.classes', 'users.students.teachers', 'users.students.departements', 'users.students.parents', 'users.students.industries')->find($id);

        if ($activity) {
            return new ActivityResource(true, 'Detail of Daily Activity', $activity);
        }

        return new ActivityResource(false, 'Daily Activity not found', null);
    }

    public function UpdateImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'nullable|file|mimes:jpeg,png,jpg,pdf',
           
        ]);
          // Temukan submission yang akan diedit
    $activities = Activity::find($id);


    // Jika submission tidak ditemukan, kembalikan respons gagal
    if (!$activities) {
        return response()->json(['success' => false, 'message' => 'Jurnal tidak ditemukan.'], 404);
    }

    if ($request->hasFile('image')) {
        if ($activities->image) {
            Storage::disk('public')->delete('activities/' . basename($activities->image));
        }
        $activities->dokumen = $request->file('image')->store('activities', 'public');
        Log::info('Image yang diunggah:', ['path' => $activities->image]);
    }
    $activities->save();

    Log::info('Updated dokumen:', $activities->toArray());

    // Return success response
    return response()->json(['success' => true, 'message' => 'Gambar berhasil diperbarui!', 'data' => $activities], 200);


    }

    public function showByUserId($user_id)
{
    // Mencari activity berdasarkan user_id dengan relasi yang diperlukan
    $activities = Activity::with('users.students.classes', 'users.students.teachers', 'users.students.departements', 'users.students.parents', 'users.students.industries')
        ->where('user_id', $user_id)
        ->get();

    // Jika tidak ada aktivitas yang ditemukan untuk user_id tersebut, kembalikan pesan error
    if ($activities->isEmpty()) {
        return response()->json(['success' => false, 'message' => 'Aktivitas tidak ditemukan untuk user_id ini.'], 404);
    }

    // Jika aktivitas ditemukan, kembalikan data
    return response()->json([
        'success' => true,
        'message' => 'Detail Aktivitas untuk user_id ' . $user_id,
        'data' => $activities,
    ], 200);
}


    public function indexGroupedByUserIdWithActivities()
{
    // Mengambil daftar activity dari database dengan filter dan pengelompokan berdasarkan user_id
    $activities = Activity::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            $query->where('description', 'like', '%' . request()->search . '%');
        })
        ->when(request()->departemen_id, function($query) {
            // Jika ada parameter departemen_id di URL
            $query->whereHas('users.students', function($query) {
                $query->where('departemen_id', request()->departemen_id);
            });
        })
        ->when(request()->classes_id, function($query) {
            // Jika ada parameter classes_id di URL
            $query->whereHas('users.students', function($query) {
                $query->where('classes_id', request()->classes_id);
            });
        })
        ->with('users.students.classes', 'users.students.teachers', 'users.students.departements', 'users.students.parents', 'users.students.industries') // Mengambil relasi yang diperlukan
        ->orderBy('user_id') // Mengurutkan berdasarkan user_id
        ->latest() // Mengurutkan activity dari yang terbaru
        ->get()
        ->groupBy('user_id'); // Mengelompokkan activity berdasarkan user_id
    
    // Menambahkan parameter pencarian ke URL pada hasil paginasi (jika ada paginasi)
    // return $activities;

    // Membuat custom response untuk menampilkan data terkelompok berdasarkan user_id
    $groupedActivities = $activities->map(function ($activities, $userId) {
        return [
            'user_id' => $userId,
            'total_activities' => $activities->count(), // Jumlah aktivitas per user
            'activities' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'date' => $activity->date,
                    'start_time' => $activity->start_time,
                    'end_time' => $activity->end_time,
                    'description' => $activity->description,
                    'tools' => $activity->tools,
                    'image' => $activity->image,
                    'related_data' => [
                        'class' => $activity->users->students->classes->name ?? null,
                        'departement' => $activity->users->students->departements->name ?? null,
                        'teacher' => $activity->users->students->teachers->name ?? null,
                        'parent' => $activity->users->students->parents->name ?? null,
                        'industry' => $activity->users->students->industries->name ?? null,
                    ],
                ];
            }),
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'List Data Activity grouped by user_id',
        'data' => $groupedActivities
    ], 200);
}


    
public function indexActivityForTeacher()
{
    $user = auth()->guard('api')->user(); // Mendapatkan user yang sedang login

    // Pastikan user memiliki peran 'guru'
    if ($user->hasRole('guru')) {
        // Mendapatkan data activity berdasarkan teacher_id dari siswa
        $activities = Activity::whereHas('users.students', function ($query) use ($user) {
            $query->where('teacher_id', $user->teachers->id); // Filter berdasarkan teacher_id dari tabel teachers
        })
        ->with('user.students.classes', 'user.students.departements', 'user.students.parents', 'user.students.teachers', 'user.students.industries') // Memuat relasi yang diperlukan
        ->groupBy('user_id') // Mengelompokkan berdasarkan user_id (siswa)
        ->latest() // Mengurutkan berdasarkan data terbaru
        ->paginate(15); // Membuat paginasi 15 item per halaman

        // Mengembalikan response dengan data activity yang difilter
        return response()->json([
            'success' => true,
            'message' => 'List Activity untuk guru',
            'data' => $activities
        ], 200);
    }

    // Jika user bukan guru, kembalikan pesan error
    return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk melihat aktivitas ini.'], 403);
}


public function updateVerified(Request $request, $id)
{
    // Cek apakah pengguna yang sedang login memiliki peran 'industri'
    $user = auth()->guard('api')->user();

    if (!$user->hasRole('industri')) {
        return response()->json(['error' => 'Unauthorized. Only industry users can verify activities.'], 403);
    }

    // Validasi input
    $validator = Validator::make($request->all(), [
        'verified' => 'required', // Kolom verified wajib dan harus berupa boolean
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()], 422);
    }

    // Cari data attendance berdasarkan ID
    $activities = Activity::find($id);

    if (!$activities) {
        return response()->json(['error' => 'activities record not found'], 404);
    }

    // Update kolom verified
    $activities->verified = $request->verified;
    $activities->save();

    // Mengembalikan response dengan data yang sudah diupdate
    return new ActivityResource(true, 'Attendance verification updated successfully', $activities);
}


}