<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\TeacherResources;
use App\Imports\StudentImport;
use App\Imports\UsersImport;
use App\Models\Departemen;
use Illuminate\Support\Facades\Validator;
use App\Models\Industry;
use App\Models\Student;
use App\Models\Parents;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class UserController extends Controller
{


public function export($role)
{
    return Excel::download(new UsersExport($role), 'users_'.$role.'.xlsx');
}

    public function import(Request $request) 
{
    $request->validate([
        'file' => ['required', 'file'],
    ]);

    try {
        Excel::import(new UsersImport, $request->file('file'));

        return response()->json(['success' => true, 'message' => 'Data berhasil diimpor.']);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => 'Gagal mengimpor data: ' . $e->getMessage()], 500);
    }
}
    public function importStudent(Request $request) 
{
    $request->validate([
        'file' => ['required', 'file'],
    ]);

    try {
        Excel::import(new StudentImport, $request->file('file'));

        return response()->json(['success' => true, 'message' => 'Data berhasil diimpor.']);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => 'Gagal mengimpor data: ' . $e->getMessage()], 500);
    }
}



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
            'name'      => $request->username,
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
                case 'jurusan':
                    $this->createDepartemen($user, $request);
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
            'classes_id'    => $request->classes_id,
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

    private function createDepartemen($user, $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Validasi data guru gagal: ' . $validator->errors()->first());
        }

        $departemen = Departemen::create([
            'name'        => $request->name,
            'slug' => Str::slug($request->name, '-'),
            'user_id'     => $user->id,
        ]);

        if (!$departemen) {
            throw new \Exception('Data jurusan gagal disimpan.');
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
                    $student = Student::where('user_id', $id)->first();
                    if ($student) {
                        if ($student->image) {
                            Storage::disk('public')->delete('students/' . basename($student->image));
                        }
                        $student->delete();
                    }
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
                case 'jurusan':
                    // Hapus data industri jika ada
                    Departemen::where('user_id', $id)->delete();
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
                case 'jurusan':
                    $userData['departemen'] = $user->departemen;
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
        'roles'     => 'required' // Pastikan roles adalah array
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
        // Pastikan roles adalah array sebelum foreach
        $roles = is_array($request->roles) ? $request->roles : explode(',', $request->roles);

        foreach ($roles as $role) {
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
                case 'jurusan':
                    $this->updateDepartemen($user, $request);
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
            'classes_id'  => $request->classes_id,
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
private function updateDepartemen($user, $request)
{
    $departemen = $user->departemens()->first();

    if ($departemen) {
        $departemen->update([
            'name'        => $request->name,
            'slug' => Str::slug($request->name, '-'),
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
    $industry = $user->industries()->first();

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
            Storage::disk('public')->delete('students/' . basename($student->image));
        }
        $student->image = $request->file('image')->store('students', 'public');
        Log::info('Gambar yang diunggah:', ['path' => $student->image]);
    }
    $student->save();
  

    return response()->json(['success' => true, 'message' => 'Foto berhasil diperbarui!', 'data' => $student], 200);
}

public function indexbyrole()
{
    $user = auth()->user();

    // Periksa peran pengguna
    if ($user->hasRole('orang tua')) {
        // Jika pengguna adalah orang tua, tampilkan data siswa yang terkait dengan orang tua tersebut
        $students = Student::with('industries', 'teachers', 'departements', 'classes', 'parents')
            ->where('parents_id', $user->parents->id)
            ->paginate(10);
    } elseif ($user->hasRole('guru')) {
        // Jika pengguna adalah guru, tampilkan data siswa yang terkait dengan guru tersebut
        $students = Student::with('industries', 'teachers', 'departements', 'classes', 'parents')
            ->where('teacher_id', $user->teachers->id)
            ->paginate(10);
    } elseif ($user->hasRole('industri')) {
        // Jika pengguna adalah industri, tampilkan data siswa yang terkait dengan industri tersebut
        $students = Student::with('industries', 'teachers', 'departements', 'classes', 'parents')
            ->where('industri_id', $user->industries->id)
            ->paginate(10);
    } elseif ($user->hasRole('jurusan')) {
        // Jika pengguna adalah industri, tampilkan data siswa yang terkait dengan industri tersebut
        $students = Student::with('industries', 'teachers', 'departements', 'classes', 'parents')
            ->where('departemen_id', $user->departements->id)
            ->paginate(10);
    } else {
        // Jika pengguna tidak memiliki peran yang relevan, kembalikan data kosong
        $students = collect([]);
    }

    return new UserResource(true, 'List Data Siswa Terkait', $students);
}

public function storeStudentWithParent(Request $request)
{
    DB::beginTransaction();

    try {
        // Validasi data input
        $request->validate([
            'name' => 'required',
            'password' => 'required|string|confirmed',
            'roles' => 'required',
            'nis' => 'required',
            'placeOfBirth' => 'required',
            'dateOfBirth' => 'required',
            'gender' => 'required',
            'alamat' => 'required',
            'classes_id' => 'required',
            'industri_id' => 'required',
            'departemen_id' => 'nullable',
            'teacher_id' => 'required',
            'image' => 'nullable',

            // Data Orang Tua
            'parent_name' => 'required|string|max:255',
            'parent_gender' => 'required',
            'parent_alamat' => 'required',
            'parent_occupation' => 'required',
            'parent_phoneNumber' => 'required',
            'parent_password' => 'required|string|confirmed', // Password orang tua
        ]);

        // Buat User untuk Orang Tua
        $parentUser = User::create([
            'name' => $request->parent_username,
            'password' => bcrypt($request->parent_password) // Ganti dengan parent_password
        ]);

        // Assign role orang tua
        $parentUser->assignRole('orang tua');

        // Buat Parents
        $parents = Parents::create([
            'user_id' => $parentUser->id, // Relasi dengan student
            'nama' => $request->parent_name,
            'gender' => $request->parent_gender,
            'alamat' => $request->parent_alamat,
            'occupation' => $request->parent_occupation,
            'phoneNumber' => $request->parent_phoneNumber,
        ]);

        // Cek apakah data orang tua berhasil disimpan
        if ($parents) {
            // Buat User untuk Siswa
            $user = User::create([
                'name' => $request->username,
                'password' => bcrypt($request->password)
            ]);

            // Assign role siswa
            $user->assignRole('siswa');

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('students', 'public');
            }

            // Buat Student
            $student = Student::create([
                'user_id' => $user->id,
                'nis' => $request->nis,
                'name' => $request->name,
                'placeOfBirth' => $request->placeOfBirth,
                'dateOfBirth' => $request->dateOfBirth,
                'gender' => $request->gender,
                'bloodType' => $request->bloodType,
                'alamat' => $request->alamat,
                'classes_id' => $request->classes_id,
                'industri_id' => $request->industri_id,
                'departemen_id' => $request->departemen_id,
                'teacher_id' => $request->teacher_id,
                'parents_id' => $parents->id, // Menggunakan parents_id yang telah disimpan
                'image' => $imagePath
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data Siswa dan Orang Tua Berhasil Disimpan',
                'data' => [
                    'student' => $student,
                    'parent' => $parents
                ]
            ], 201);
        } else {
            throw new \Exception('Gagal menyimpan data orang tua');
        }

    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan data: ' . $e->getMessage()
        ], 500);
    }
}
}