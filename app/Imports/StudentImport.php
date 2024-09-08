<?php

namespace App\Imports;

use App\Models\Classes;
use App\Models\Departemen;
use App\Models\User;
use App\Models\Student;
use App\Models\Parents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpDate;

class StudentImport implements ToModel, WithHeadingRow
{
    
    public function model(array $row)
    {

        DB::beginTransaction();

        \Log::info('Import data row:', $row);

    if (empty($row['name'])) {
        throw new \Exception('Nama tidak boleh kosong.');
    }
        // Buat user baru
        $parentUser = User::create([
            'name'      => $row['name'],
            'password'  => Hash::make($row['password']),
        ]);

        // Assign roles to user
        $parentUser->assignRole('orang tua');

        $parents = Parents::create([
            'user_id' => $parentUser->id, // Relasi dengan student
            'nama' => $row['parent_name'],
            'gender' => $row['parent_gender'],
            'alamat' => $row['parent_alamat'],
            'occupation' => $row['parent_occupation'],
            'phoneNumber' => $row['parent_phonenumber'],
        ]);

        // Cek apakah data orang tua berhasil disimpan
        if ($parents) {
            // Buat User untuk Siswa
            $user = User::create([
                'name' => $row['username'],
                'password'  => Hash::make($row['password'])
            ]);

            // Assign role siswa
            $user->assignRole('siswa');

            // Buat Student
            $student = Student::create([
                'user_id' => $user->id,
                'nis' => $row['nis'],
                'name' => $row['name'],
                'placeOfBirth' => $row['placeofbirth'],
                'dateOfBirth' => $this->validateDate($row['dateofbirth']),
                'gender' => $row['gender'],
                'bloodType' => $row['bloodtype'],
                'alamat' => $row['alamat'],
                'classes_id' => $row['classes_id'],
                'industri_id' => $row['industri_id'],
                'departemen_id' => $row['departemen_id'],
                'teacher_id' => $row['teacher_id'],
                'parents_id' => $parents->id, // Menggunakan parents_id yang telah disimpan
                'image'       => isset($row['image']) ? $row['image'] : null, 
            ]);

            DB::commit();


        // Panggil fungsi sesuai deng

        return [$parents, $student];
    }
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
}
