<?php

namespace App\Imports;

use App\Models\Classes;
use App\Models\Departemen;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Parents;
use App\Models\Industry;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpDate;

class UsersImport implements ToModel, WithHeadingRow
{
    
    public function model(array $row)
    {

        \Log::info('Import data row:', $row);

    if (empty($row['name'])) {
        throw new \Exception('Nama tidak boleh kosong.');
    }
        // Buat user baru
        $user = User::create([
            'name'      => $row['name'],
            'password'  => Hash::make($row['password']),
        ]);

        // Assign roles to user
        $user->assignRole($row['roles']);

        // Panggil fungsi sesuai dengan peran
        switch ($row['roles']) {
            case 'siswa':
                $this->createStudent($user, $row);
                break;
            case 'guru':
                $this->createTeacher($user, $row);
                break;
            case 'orang tua':
                $this->createParent($user, $row);
                break;
            case 'industri':
                $this->createIndustry($user, $row);
                break;
            default:
                throw new \Exception('Peran tidak valid.');
        }

        return $user;
    }

    private function createStudent($user, $row)
    {

        $class = Classes::where('name', $row['kelas'])->first();
        $department = Departemen::where('name', $row['jurusan'])->first();
        $parent = Parents::where('nama', $row['orang tua'])->first();
        $teacher = Teacher::where('name', $row['guru'])->first();
        $industri = Industry::where('name', $row['industri'])->first();


        Student::create([
            'user_id'     => $user->id,
            'name'        => $row['name'],
            'nis'         => $row['nis'],
            'placeOfBirth'=> $row['placeofbirth'],
            'dateOfBirth' => $this->validateDate($row['dateofbirth']),
            'gender'      => $row['gender'],
            'bloodType'   => $row['bloodtype'],
            'alamat'      => $row['alamat'],
            'classes_id'    => $class ? $class->id : null,
            'industri_id' => $industri ? $industri->id : null,
            'departemen_id' => $department ? $department->id : null,
            'parents_id'  => $parent ? $parent->id : null,
            'teacher_id'  => $teacher ? $teacher->id : null,
            'image'       => isset($row['image']) ? $row['image'] : null,  // File gambar bisa di-handle jika diperlukan
        ]);
    }

    private function validateDate($date)
    {
        // Cek apakah tanggal kosong atau tidak valid
        if (empty($date) || !is_numeric($date)) {
            return null;
        }

        // Jika tanggal dalam format serial Excel, konversi ke format tanggal
        if (is_numeric($date)) {
            $date = PhpDate::excelToDateTimeObject($date);
        }

        // Validasi format tanggal
        if (!$date instanceof \DateTime) {
            return null;
        }
        
        return $date->format('Y-m-d');
    }


    private function createTeacher($user, $row)
    {

        $department = Departemen::where('name', $row['jurusan'])->first();

        Teacher::create([
            'name'        => $row['name'],
            'user_id'     => $user->id,
            'no_hp'       => $row['no_hp'],
             'departemen_id' => $department ? $department->id : null,
        ]);
    }

    private function createParent($user, $row)
    {
        Parents::create([
            'user_id'     => $user->id,
            'nama'        => $user->name,
            'gender'      => $row['gender'],
            'alamat'      => $row['alamat'],
            'occupation'  => $row['occupation'],
            'phoneNumber' => $row['no_hp'],
        ]);
    }

    private function createIndustry($user, $row)
    {
        Industry::create([
            'user_id'     => $user->id,
            'name'        => $row['name'],
            'bidang'      => $row['bidang'],
            'alamat'      => $row['alamat'],
            'longitude'   => $row['longitude'],
            'latitude'    => $row['latitude'],
            'industryMentorName' => $row['industrymentorname'],
            'industryMentorNo'   => $row['industrymentorno'],
        ]);
    }
}
