<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Student;
use App\Http\Resources\StudentResources;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class StudentController extends Controller
{
  public function store(Request $request) {
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'name' => 'required',
        'nis' => 'required',
        'placeOfBirth' => 'required',
        'dateOfBirth' => 'required|date',
        'gender' => 'required',
        'bloodType' => 'required',
        'alamat' => 'required',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,svg',
        'classes_id' => 'required|exists:classes,id',
        'industri_id' => 'required|exists:industries,id',
        'departemen_id' => 'required|exists:departemens,id',
        'parent_id' => 'required|exists:parents,id',
        'teacher_id' => 'required|exists:teachers,id',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('students', 'public');
    }

    $student = Student::create([
        'user_id' => $request->user_id,
        'name' => $request->name,
        'nis' => $request->nis,
        'placeOfBirth' => $request->placeOfBirth,
        'dateOfBirth' => $request->dateOfBirth,
        'gender' => $request->gender,
        'bloodType' => $request->bloodType,
        'alamat' => $request->alamat,
        'image' => $imagePath,
        'classes_id' => $request->classes_id,
        'industri_id' => $request->industri_id,
        'departemen_id' => $request->departemen_id,
        'parent_id' => $request->parent_id,
        'teacher_id' => $request->teacher_id,
    ]);

    if ($student) {
        return new StudentResources(true, 'Data siswa berhasil ditambahkan', $student);
    }

    return new StudentResources(false, 'Data siswa gagal ditambahkan', null);
}


  public function update(Request $request, $id ) {

    $validator = Validator::make($request->all(), [
      'user_id'=> 'required|exists:users,id',
          'name'=> 'required',
          'nis' => 'required',
          'placeOfBirth' => 'required',
          'dateOfBirth' => 'required|date',
          'gender'=> 'required',
          'bloodType' => 'required',
          'alamat' => 'required',
          'classes_id' => 'required|exists:classes,id',
          'industri_id' => 'required|exists:industries,id',
          'departemen_id' => 'required|exists:departemens,id',
          'parent_id' => 'required|exists:parents,id',
          'teacher_id' => 'required|exists:teachers,id',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
  }

  $student = Student::find($id);

  if ($student) {
    $student->update([

      'user_id'=> $request->user_id,
      'name'=> $request->name,
      'nis' => $request->nis,
      'placeOfBirth' => $request->placeOfBirth,
      'dateOfBirth' => $request->dateOfBirth,
      'gender'=> $request->gender,
      'bloodType' => $request->bloodType,
      'alamat' => $request->alamat,
      'classes_id' => $request->classes_id,
      'industri_id' => $request->industri_id,
      'departemen_id' => $request->departemen_id,
      'parent_id' => $request->parent_id,
      'teacher_id' => $request->teacher_id,

    ]);

    return new StudentResources(true, 'Data Siswa Berhasil Diperbarui!', $student);
  }
  return new StudentResources(true, 'Data Siswa Berhasil Diperbarui!', null);
  }

  public function UpdateDokumen(Request $request, $id)
  {
      $request->validate([
          'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
         
      ]);
        // Temukan submission yang akan diedit
  $student = Student::find($id);

  // Jika submission tidak ditemukan, kembalikan respons gagal
  if (!$student) {
      return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
  }

  if ($request->hasFile('image')) {
      if ($student->image) {
          Storage::disk('public')->delete($student->image);
      }
      $student->image = $request->file('image')->store('students', 'public');
      Log::info('Gambar yang diunggah:', ['path' => $student->image]);
  }
  $student->save();

  Log::info('Updated Image:', $student->toArray());

  // Return success response
  return response()->json(['success' => true, 'message' => 'Foto berhasil diperbarui!', 'data' => $student], 200);
  }

  public function index()
    {
        // Mendapatkan daftar academic programs dari database
        $student = Student::when(request()->search, function($query) {
            // Jika ada parameter pencarian (search) di URL
            // Maka tambahkan kondisi WHERE untuk mencari academic programs berdasarkan nama
            $query->where('name', 'like', '%' . request()->search . '%');
        })->with('users')->with('departements')->with('parents')->with('classes')->with('industries')->with('teachers')->oldest() // Mengurutkan academic programs dari yang terbaru
        ->paginate(5); // Membuat paginasi dengan 5 item per halaman

        // Menambahkan parameter pencarian ke URL pada hasil paginasi
        $student->appends(['search' => request()->search]);

        // Mengembalikan response dalam bentuk DepartemenResource (asumsi resource sudah didefinisikan)
        return new StudentResources(true, 'List Data Siswa', $student);
    }

    public function show($id)
    {
        $student = Student::with('parents', 'industries', 'departements', 'teachers', 'classes')
        ->find($id);

        if($student) {
            //return succes with Api Resource
            return new StudentResources(true, 'Detail Data Siswa!', $student);
        }

        //return failed with Api Resource
        return new StudentResources(false, 'Detail Data Jurusan Tidak Ditemukan!', null);
    }



}
