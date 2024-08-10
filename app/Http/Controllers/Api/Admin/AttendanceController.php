<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Attendance;
use App\Models\Parents;
use App\Http\Resources\AttendanceResource;


class AttendanceController extends Controller
{
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
    
            'date' => 'required|date',
            'departureTime' => 'required',
            'arrivalTime' => 'required',
            'absenceReason' => 'required',
            'image'=> 'required|image',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->input('image');
        $image = $request->file('image')->store('attendances', 'public');

        $attendance = Attendance::create ([
            'date' => $request->date,
            'departureTime' => $request->departureTime,
            'arrivalTime' => $request->arrivalTime,
            'absenceReason' => $request->absenceReason,
            'image'=> $image,
            'user_id'       => auth()->guard('api')->user()->id,
        ]);

        if($attendance) {
            return new AttendanceResource(true, 'Absen berhasil disimpan', $attendance);
        }

        return new AttendanceResource(false, 'Absen gagal disimpan', null);

    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'date' => 'require|date',
            'departureTime' => 'require',
            'arrivalTime' => 'require',
            'absenceReason' => 'require',
        ]);

        if($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $attendance = Attendance::find($id);

        if($attendance) {
            $attendance->update([
                'date' => $request->date,
            'departureTime' => $request->departureTime,
            'arrivalTime' => $request->arrivalTime,
            'absenceReason' => $request->absenceReason,
            'user_id'       => auth()->guard('api')->user()->id,

            ]);

            return new AttendanceResource(true, 'Absen berhasil disimpan', $attendance);
        }

        return new AttendanceResource(false, 'Absen gagal disimpan', null);
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
    
    

}


