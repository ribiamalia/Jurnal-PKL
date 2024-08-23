<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Http\Resources\ActivityResource;
use Illuminate\Support\Facades\Validator;

class EvaluationController extends Controller
{
    public function store(Request $request)
    {
        // Validasi data request
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id', // Pastikan id siswa ada di tabel students
            'skills' => 'required',
            'score' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Ambil industri terkait dari user yang sedang login
        $user = auth()->user();
        $industry = $user->industries; // Menggunakan relasi hasOne

        if (!$industry) {
            return response()->json(['error' => 'Industry not found'], 404);
        }

        // Buat data evaluasi dengan industri_id dari industri terkait
        $evaluation = Evaluation::create([
            'student_id' => $request->student_id,
            'industri_id' => $industry->id, // Menggunakan ID dari industri terkait
            'skills' => $request->skills,
            'score' => $request->score,
        ]);

        if ($evaluation) {
            return new ActivityResource(true, 'Penilaian berhasil disimpan', $evaluation);
        }

        return new ActivityResource(false, 'Penilaian gagal disimpan', null);
    }
}
