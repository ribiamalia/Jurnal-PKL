<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ScheduleController extends Controller
{
    public function store(Request $request)
    {
        // Ambil user yang sedang login
        $authUser = auth()->guard('api')->user();

        // Tentukan apakah pengguna adalah admin atau guru
        $isAdmin = $authUser->hasRole('admin'); // Misalnya menggunakan metode hasRole
        $isTeacher = $authUser->hasRole('guru'); // Misalnya menggunakan metode hasRole

        // Validasi input berdasarkan peran pengguna
        $validator = Validator::make($request->all(), [
            'user_id' => $isAdmin ? 'required|exists:users,id' : 'nullable|exists:users,id',
            'industri_id' => 'required|exists:industries,id',
            'date' => 'required|date',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Jika pengguna adalah guru, set user_id ke user yang sedang login
        $user_id = $isTeacher ? $authUser->id : $request->user_id;

        $schedule = Schedule::create([
            'user_id' => $user_id,
            'industri_id' => $request->industri_id,
            'date' => $request->date,
            'status' => $request->status,
        ]);

        if($schedule) {
            return new ScheduleResource(true, 'Jadwal berhasil disimpan', $schedule);
        }

        return new ScheduleResource(false, 'Jadwal gagal disimpan', null);
    }

    public function index()
    {
        // Mendapatkan daftar jadwal dari database
        $schedules = Schedule::with(['users', 'industries'])
            ->when(request()->search, function ($query) {
                // Jika ada parameter pencarian (search) di URL
                $query->where('status', 'like', '%' . request()->search . '%');
            })
            ->get() // Ambil semua jadwal tanpa paginasi
            ->groupBy('date'); // Kelompokkan jadwal berdasarkan tanggal
    
        // Mendapatkan ID user dari jadwal
        $userIds = $schedules->flatMap(function ($dateGroup) {
            return $dateGroup->pluck('user_id');
        })->unique();
    
        // Ambil nama-nama user dari database
        $users = User::whereIn('id', $userIds)->pluck('name', 'id');
    
        // Menyusun hasil jadwal yang dikelompokkan berdasarkan tanggal dan user_id
        $groupedSchedules = $schedules->map(function ($dateGroup) use ($users) {
            return $dateGroup->groupBy('user_id')->map(function ($userGroup, $userId) use ($users) {
                return [
                    'user_id' => $userId,
                    'user_name' => $users[$userId] ?? 'Unknown', // Tambahkan nama user
                    'schedules' => $userGroup->map(function ($schedule) {
                        return [
                            'id' => $schedule->id,
                            'industri_id' => $schedule->industri_id,
                            'date' => $schedule->date,
                            'status' => $schedule->status,
                            'created_at' => $schedule->created_at,
                            'updated_at' => $schedule->updated_at
                        ];
                    })
                ];
            })->values();
        });
    
        // Mengembalikan response dalam format JSON
        return response()->json([
            'success' => true,
            'message' => 'List Schedule grouped by date and user_id',
            'data' => $groupedSchedules
        ]);
    }

    public function show($id)
    {
        $schedule = Schedule::with('users', 'industries', )
        ->find($id);

        if($schedule) {
            //return succes with Api Resource
            return new ScheduleResource(true, 'Detail Data Schedule!', $schedule);
        }

        //return failed with Api Resource
        return new ScheduleResource(false, 'Detail Data Schedul Tidak Ditemukan!', null);
    }

    
    
}