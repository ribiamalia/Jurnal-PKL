<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    
    {
        // // permission for user
        // permission::create(['name' => 'users.index', 'guard_name' => 'api']);
        //  permission::create(['name' => 'users.edit', 'guard_name' => 'api']);
        //  permission::create(['name' => 'users.create', 'guard_name' => 'api']);
        //  permission::create(['name' => 'users.delete', 'guard_name' => 'api']);  

        //   //permission for roles
        // permission::create(['name' => 'roles.index', 'guard_name' => 'api']);
        // permission::create(['name' => 'roles.edit', 'guard_name' => 'api']);
        // permission::create(['name' => 'roles.create', 'guard_name' => 'api']);
        // permission::create(['name' => 'roles.delete', 'guard_name' => 'api']);

        //  //permission for jurusan
        //  permission::create(['name' => 'jurusan.index', 'guard_name' => 'api']);
        //  permission::create(['name' => 'jurusan.edit', 'guard_name' => 'api']);
        //  permission::create(['name' => 'jurusan.create', 'guard_name' => 'api']);
        //  permission::create(['name' => 'jurusan.delete', 'guard_name' => 'api']);  

        //  //permission for kelas
        //  permission::create(['name' => 'kelas.index', 'guard_name' => 'api']);
        //  permission::create(['name' => 'kelas.edit', 'guard_name' => 'api']);
        //  permission::create(['name' => 'kelas.create', 'guard_name' => 'api']);
        //  permission::create(['name' => 'kelas.delete', 'guard_name' => 'api']);

        //  permission::create(['name' => 'industri.index', 'guard_name' => 'api']);
        //  permission::create(['name' => 'industri.edit', 'guard_name' => 'api']);
        //  permission::create(['name' => 'industri.create', 'guard_name' => 'api']);
        //  permission::create(['name' => 'industri.delete', 'guard_name' => 'api']); 

        //  permission::create(['name' => 'panduan.index', 'guard_name' => 'api']);
        //  permission::create(['name' => 'panduan.edit', 'guard_name' => 'api']);
        //  permission::create(['name' => 'panduan.create', 'guard_name' => 'api']);
        //  permission::create(['name' => 'panduan.delete', 'guard_name' => 'api']); 

        //  permission::create(['name' => 'jadwal.index', 'guard_name' => 'api']);
        //  permission::create(['name' => 'jadwal.edit', 'guard_name' => 'api']);
        //  permission::create(['name' => 'jadwal.create', 'guard_name' => 'api']);
        //  permission::create(['name' => 'jadwal.delete', 'guard_name' => 'api']); 

        //  permission::create(['name' => 'kunjungan.index', 'guard_name' => 'api']);
        //  permission::create(['name' => 'kunjungan.edit', 'guard_name' => 'api']);
        //  permission::create(['name' => 'kunjungan.create', 'guard_name' => 'api']);
        //  permission::create(['name' => 'kunjungan.delete', 'guard_name' => 'api']); 

        //  permission::create(['name' => 'absen.index', 'guard_name' => 'api']);
        //  permission::create(['name' => 'absen.edit', 'guard_name' => 'api']);
        //  permission::create(['name' => 'absen.create', 'guard_name' => 'api']);
        //  permission::create(['name' => 'absen.delete', 'guard_name' => 'api']); 

        //  permission::create(['name' => 'laporan.index', 'guard_name' => 'api']);
        //  permission::create(['name' => 'laporan.edit', 'guard_name' => 'api']);
        //  permission::create(['name' => 'laporan.create', 'guard_name' => 'api']);
        //  permission::create(['name' => 'laporan.delete', 'guard_name' => 'api']); 
         
        //  permission::create(['name' => 'penilaian.index', 'guard_name' => 'api']);
        //  permission::create(['name' => 'penilaian.edit', 'guard_name' => 'api']);
        //  permission::create(['name' => 'penilaian.create', 'guard_name' => 'api']);
        //  permission::create(['name' => 'penilaian.delete', 'guard_name' => 'api']); 

        //  permission::create(['name' => 'permission.index', 'guard_name' => 'api']);

        // permission::create(['name' => 'siswa.index', 'guard_name' => 'api']);
        //  permission::create(['name' => 'siswa.edit', 'guard_name' => 'api']);
        //  permission::create(['name' => 'siswa.create', 'guard_name' => 'api']);
        //  permission::create(['name' => 'siswa.delete', 'guard_name' => 'api']); 

        permission::create(['name' => 'jurusan', 'guard_name' => 'api']); 

       



    }
}
