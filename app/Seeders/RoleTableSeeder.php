<?php
namespace App\Seeders;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $role = new \App\Role();
        $role->id =1;
        $role->role = 'Admin';
        $role->save();

        $role = new \App\Role();
        $role->id =2;
        $role->role = 'Member';
        $role->save();
    }
}
