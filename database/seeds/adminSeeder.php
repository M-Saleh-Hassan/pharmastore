<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class adminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = DB::table('role_user')->where('role_id', 1)->first();
        if(is_null($adminRole)) {
            $user = App\Model\User::create([
                'name' => "Admin",
                'username' => "admin",
                'email' => "admin@pharmastore.com",
                'password' => Hash::make('admin'),
            ]);
            $user->roles()->attach(1);
        }
    }
}
