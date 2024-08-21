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
}
