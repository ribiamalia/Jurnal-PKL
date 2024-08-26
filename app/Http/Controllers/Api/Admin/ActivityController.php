<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Activity;
use App\Http\Resources\ActivityResource;

class ActivityController extends Controller
{
    
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
        if($activity) {
            $activity->delete();
            return new ActivityResource(true, 'Daily Activity berhasil di hapus', null);
        }
        return new ActivityResource(false, 'Daily Activity gagal di hapus', null);
    }

    public function index()
    {
        // Mendapatkan daftar activity dari database dengan filter berdasarkan departemen_id dan classes_id
        $activity = Activity::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            // Maka tambahkan kondisi WHERE untuk mencari activity berdasarkan deskripsi
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
        ->latest() // Mengurutkan activity dari yang terbaru
        ->paginate(15); // Membuat paginasi dengan 15 item per halaman
    
        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $activity->appends([
            'search' => request()->search,
            'departemen_id' => request()->departemen_id,
            'classes_id' => request()->classes_id
        ]);
    
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
}