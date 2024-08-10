<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Industry;
use App\Http\Resources\IndustriResource;

class IndustriController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'name' => 'required',
        'bidang' => 'required',
        'alamat' => 'required',
       'longitude' => 'required',
       'latitude' => 'required',
        'industryMentorName' => 'required',
        'industryMentorNo' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $industri = Industry::create([
            'user_id' => $request->user_id,
        'name' => $request->name,
        'bidang' => $request->bidang,
        'alamat' => $request->alamat,
       'longitude' => $request->longitude,
       'latitude' => $request->latitude,
        'industryMentorName' => $request->industryMentorName,
        'industryMentorNo' => $request->industryMentorNo,

        ]);

        if($industri) {
            return new IndustriResource(true, 'Data Industri berhail disimpan', $industri);
        }

        return new IndustriResource(false, 'Data Industri gagal disimpan', null);
    }
}
