<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\TeacherResources;
use Illuminate\Support\Facades\Validator;
use App\Models\Industry;
use App\Models\Student;
use App\Models\Parents;
use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('students.industries','students.teachers','students.departements','students.classes','students.parents', 'teachers.students', 'parents.students', 'industries')
            ->when(request()->search, function($query) {
                return $query->where('name', 'like', '%' . request()->search . '%');
            })
            ->latest()
            ->paginate(10);
    
        $users->appends(['search' => request()->search]);
    
        return new UserResource(true, 'List Data User', $users);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'password'  => 'required|confirmed',
            'roles'     => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create user
        $user = User::create([
            'name'      => $request->name,
            'password'  => bcrypt($request->password)
        ]);

        // Assign roles to user
        $user->assignRole($request->roles);

        try {
            switch ($request->roles) {
                case 'siswa':
                    $this->createStudent($user, $request);
                    break;
                case 'guru':
                    $this->createTeacher($user, $request);
                    break;
                case 'orang tua':
                    $this->createParent($user, $request);
                    break;
                case 'industri':
                    $this->createIndustry($user, $request);
                    break;
                default:
                    return response()->json(['error' => 'Peran tidak valid.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }

        return new UserResource(true, 'Data User Berhasil Disimpan', $user);
    }

    private function createStudent($user, $request)
    {

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('students', 'public');
        }

        $student = Student::create([
            'user_id'     => $user->id,
            'name'        => $request->name,
            'nis'         => $request->nis,
            'placeOfBirth'=> $request->placeOfBirth,
            'dateOfBirth' => $request->dateOfBirth,
            'gender'      => $request->gender,
            'bloodType'   => $request->bloodType,
            'alamat'      => $request->alamat,
            'class_id'    => $request->class_id,
            'industri_id' => $request->industri_id,
            'departemen_id' => $request->departemen_id,
            'parents_id'  => $request->parents_id,
            'teacher_id'  => $request->teacher_id,
            'image' => $imagePath
        ]);

        if (!$student) {
            throw new \Exception('Data siswa gagal disimpan.');
        }
    }

    private function createTeacher($user, $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'no_hp' => 'required',
            'departemen_id' => 'required'
        ]);

        if ($validator->fails()) {
            throw new \Exception('Validasi data guru gagal: ' . $validator->errors()->first());
        }

        $teacher = Teacher::create([
            'name'        => $request->name,
            'user_id'     => $user->id,
            'no_hp'       => $request->no_hp,
            'departemen_id' => $request->departemen_id,
        ]);

        if (!$teacher) {
            throw new \Exception('Data guru gagal disimpan.');
        }
    }

    private function createParent($user, $request)
    {
        $parent = Parents::create([
            'user_id'     => $user->id,
            'nama'        => $request->nama,
            'gender'      => $request->gender,
            'placeOfBirth'=> $request->placeOfBirth,
            'dateOfBirth' => $request->dateOfBirth,
            'alamat'      => $request->alamat,
            'occupation'  => $request->occupation,
            'phoneNumber' => $request->phoneNumber,
        ]);

        if (!$parent) {
            throw new \Exception('Data orang tua gagal disimpan.');
        }
    }

    private function createIndustry($user, $request)
    {
        $industry = Industry::create([
            'user_id'     => $user->id,
            'name'        => $request->name,
            'bidang'      => $request->bidang,
            'alamat'      => $request->alamat,
            'longitude'   => $request->longitude,
            'latitude'    => $request->latitude,
            'industryMentorName' => $request->industryMentorName,
            'industryMentorNo'   => $request->industryMentorNo,
        ]);

        if (!$industry) {
            throw new \Exception('Data industri gagal disimpan.');
        }
    }

    // public function show($id)
    // {
    //     $user = User::with('roles')->whereId($id)->first();

    //     if ($user) {
    //         return new UserResource(true, 'Detail Data User!', $user);
    //     }

    //     return new UserResource(false, 'Detail Data User Gagal Ditemukan!', null);
    // }

    // public function update(Request $request, User $user)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name'      => 'required',
    //         'password'  => 'confirmed'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     if ($request->password == "") {
    //         $user->update([
    //             'name'      => $request->name,
    //         ]);
    //     } else {
    //         $user->update([
    //             'name'      => $request->name,
    //             'password'  => bcrypt($request->password)
    //         ]);
    //     }

    //     $user->syncRoles($request->roles);

    //     return new UserResource(true, 'Data User Berhasil Diupdate!', $user);
    // }


    public function destroy($id)
{
    // Cari user berdasarkan ID
    $user = User::find($id);

    if (!$user) {
        return response()->json(['error' => 'User tidak ditemukan.'], 404);
    }

    try {
        // Hapus data terkait berdasarkan peran user
        $roles = $user->getRoleNames();

        foreach ($roles as $role) {
            switch ($role) {
                case 'siswa':
                    // Hapus data siswa jika ada
                    Student::where('user_id', $id)->delete();
                    break;
                case 'guru':
                    // Hapus data guru jika ada
                    Teacher::where('user_id', $id)->delete();
                    break;
                case 'orang tua':
                    // Hapus data orang tua jika ada
                    Parents::where('user_id', $id)->delete();
                    break;
                case 'industri':
                    // Hapus data industri jika ada
                    Industry::where('user_id', $id)->delete();
                    break;
            }
        }

        // Hapus user dan perannya
        $user->delete();
        
        return response()->json(['message' => 'Data user dan data terkait berhasil dihapus.'], 200);
    } catch (\Exception $e) {
        // Handle kesalahan dan kembalikan respons error
        return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
    }
}

public function show($id)
{
    // Temukan user berdasarkan ID
    $user = User::with(['roles', 'students.industries','students.teachers','students.departements','students.classes','students.parents', 'teachers.students', 'parents.students', 'industries'])->find($id);

    if ($user) {
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'roles' => $user->roles->pluck('name'),
        ];

        // Tambahkan data spesifik berdasarkan peran
        foreach ($userData['roles'] as $role) {
            switch ($role) {
                case 'siswa':
                    $userData['student'] = $user->students;
                    break;
                case 'guru':
                    $userData['teacher'] = $user->teachers;
                    break;
                case 'orang tua':
                    $userData['parent'] = $user->parents;
                    break;
                case 'industri':
                    $userData['industry'] = $user->industry;
                    break;
            }
        }

        return new UserResource(true, 'Detail Data User', $userData);
    }

    return new UserResource(false, 'Detail Data User Gagal Ditemukan', null);
}

public function update(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['error' => 'User tidak ditemukan.'], 404);
    }

    $validator = Validator::make($request->all(), [
        'name'      => 'required',
        'password'  => 'nullable|confirmed',
        'roles'     => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // Update user data
    $user->name = $request->name;

    if ($request->password) {
        $user->password = bcrypt($request->password);
    }

    $user->save();

    // Update roles
    $user->syncRoles($request->roles);

    try {
        // Update related data based on role
        foreach ($request->roles as $role) {
            switch ($role) {
                case 'siswa':
                    $this->updateStudent($user, $request);
                    break;
                case 'guru':
                    $this->updateTeacher($user, $request);
                    break;
                case 'orang tua':
                    $this->updateParent($user, $request);
                    break;
                case 'industri':
                    $this->updateIndustry($user, $request);
                    break;
            }
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
    }

    return new UserResource(true, 'Data User Berhasil Diupdate!', $user);
}

private function updateStudent($user, $request)
{
    $student = $user->students()->first();

    if ($student) {
        $student->update([
            'name'        => $request->name,
            'nis'         => $request->nis,
            'placeOfBirth'=> $request->placeOfBirth,
            'dateOfBirth' => $request->dateOfBirth,
            'gender'      => $request->gender,
            'bloodType'   => $request->bloodType,
            'alamat'      => $request->alamat,
            'class_id'    => $request->class_id,
            'industri_id' => $request->industri_id,
            'departemen_id' => $request->departemen_id,
            'parents_id'  => $request->parents_id,
            'teacher_id'  => $request->teacher_id,
        ]);
    }
}

private function updateTeacher($user, $request)
{
    $teacher = $user->teachers()->first();

    if ($teacher) {
        $teacher->update([
            'name'        => $request->name,
            'no_hp'       => $request->no_hp,
            'departemen_id' => $request->departemen_id,
        ]);
    }
}

private function updateParent($user, $request)
{
    $parent = $user->parents()->first();

    if ($parent) {
        $parent->update([
            'nama'        => $request->nama,
            'gender'      => $request->gender,
            'placeOfBirth'=> $request->placeOfBirth,
            'dateOfBirth' => $request->dateOfBirth,
            'alamat'      => $request->alamat,
            'occupation'  => $request->occupation,
            'phoneNumber' => $request->phoneNumber,
        ]);
    }
}

private function updateIndustry($user, $request)
{
    $industry = $user->industry()->first();

    if ($industry) {
        $industry->update([
            'name'        => $request->name,
            'bidang'      => $request->bidang,
            'alamat'      => $request->alamat,
            'longitude'   => $request->longitude,
            'latitude'    => $request->latitude,
            'industryMentorName' => $request->industryMentorName,
            'industryMentorNo'   => $request->industryMentorNo,
        ]);
    }
}

public function updateStudentImage(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['error' => 'User tidak ditemukan.'], 404);
    }

    // Validasi file image
    $validator = Validator::make($request->all(), [
        'image' => 'required|image|mimes:jpeg,png,jpg,gif',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $student = $user->students()->first();

    if (!$student) {
        return response()->json(['error' => 'Siswa tidak ditemukan.'], 404);
    }

    // Hapus image lama jika ada
    if ($request->hasFile('image')) {
        if ($student->image) {
            Storage::disk('public')->delete($student->image);
        }
        $student->image = $request->file('image')->store('students', 'public');
        Log::info('Gambar yang diunggah:', ['path' => $student->image]);
    }
    $student->save();
  

    return response()->json(['success' => true, 'message' => 'Foto berhasil diperbarui!', 'data' => $student], 200);
}



}
