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
        'judul' => 'required',
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
        'judul' => $request->judul,
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
        'judul' => 'required',
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
        'judul' => $request->judul,
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
        // Mendapatkan daftar academic programs dari database
        $activity = Activity::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            // Maka tambahkan kondisi WHERE untuk mencari academic programs berdasarkan nama
            $query->where('name', 'like', '%' . request()->search . '%');
        })->with('users')->latest() // Mengurutkan academic programs dari yang terbaru
        ->paginate(15); // Membuat paginasi dengan 5 item per halaman

        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $activity->appends(['search' => request()->search]);

        // Mengembalikan response dalam bentuk DepartemenResource (asumsi resource sudah didefinisikan)
        return new ActivityResource(true, 'List Data Jurnal', $activity);
    }

    public function show($id)
    {
        $activity = Activity::with('users')->find($id);

        if ($activity) {
            return new ActivityResource(true, 'Detail of Daily Activity', $activity);
        }

        return new ActivityResource(false, 'Daily Activity not found', null);
    }
}