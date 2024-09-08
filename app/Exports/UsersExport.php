<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Export semua data user, disertai role dan data relasinya (seperti siswa, guru, orang tua, atau industri)
        return User::with(['roles', 'students', 'teachers', 'parents', 'industries'])->get()->map(function($user) {
            // Menambahkan kolom sesuai role
            $role = $user->roles->pluck('name')->first();

            $relatedData = $this->getRelatedData($user, $role);

            return array_merge([
                'id'         => $user->id,
                'name'       => $user->name,
                'role'       => $role,
                'email'      => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ], $relatedData);
        });
    }

    // Fungsi untuk mendapatkan data relasi sesuai role, dengan setiap field di kolom terpisah
    private function getRelatedData($user, $role)
    {
        switch ($role) {
            case 'siswa':
                return [
                    'nis'           => optional($user->students)->nis,
                    'placeOfBirth'  => optional($user->students)->placeOfBirth,
                    'dateOfBirth'   => optional($user->students)->dateOfBirth,
                    'gender'        => optional($user->students)->gender,
                    'alamat'        => optional($user->students)->alamat,
                    'classes_id'    => optional($user->students)->classes_id,
                    'industri_id'   => optional($user->students)->industri_id,
                ];
            case 'guru':
                return [
                    'no_hp'         => optional($user->teachers)->no_hp,
                    'departemen_id' => optional($user->teachers)->departemen_id,
                ];
            case 'orang tua':
                return [
                    'occupation'    => optional($user->parents)->occupation,
                    'phoneNumber'   => optional($user->parents)->phoneNumber,
                ];
            case 'industri':
                return [
                    'bidang'        => optional($user->industries)->bidang,
                    'alamat_industri'=> optional($user->industries)->alamat,
                    'longitude'     => optional($user->industries)->longitude,
                    'latitude'      => optional($user->industries)->latitude,
                    'mentorName'    => optional($user->industries)->industryMentorName,
                    'mentorNo'      => optional($user->industries)->industryMentorNo,
                ];
                case 'jurusan':
                    return [
                        'name'         => optional($user->departements)->name,
                    ];
            default:
                return [];
        }
    }

    // Menambahkan heading pada file export
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Role',
            'Email',
            'Created At',
            'Updated At',
            // Kolom untuk data relasi siswa
            'NIS',
            'Place of Birth',
            'Date of Birth',
            'Gender',
            'Alamat',
            'Classes ID',
            'Industri ID',
            // Kolom untuk data relasi guru
            'No HP Guru',
            'Departemen ID Guru',
            // Kolom untuk data relasi orang tua
            'Occupation Orang Tua',
            'Phone Number Orang Tua',
            // Kolom untuk data relasi industri
            'Bidang Industri',
            'Alamat Industri',
            'Longitude',
            'Latitude',
            'Mentor Name',
            'Mentor No',

            'Name'
        ];
    }
}
