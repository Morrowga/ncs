<?php

use App\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'system',
                'username' => 'system',
                'email' => 'system@mail.com',
                'password' => Hash::make('Admin123!@#')
            ],
            [
                'name' => 'system',
                'username' => 'ha',
                'email' => 'htetaung@htut.com',
                'password' => Hash::make('123123123')
            ],
            [
                'name' => 'user one',
                'username' => 'userone',
                'email' => 'user1@mail.com',
                'password' => Hash::make('User123!@#')
            ],
            [
                'name' => 'myat ko',
                'username' => 'myatko',
                'email' => 'myatkohein@htut.com',
                'password' => Hash::make('Myatkohein11111')
            ]
        ];
        foreach ($data as $datum) {
            $user = new User;
            $user->name = $datum['name'];
            $user->username = $datum['username'];
            $user->email = $datum['email'];
            $user->password = $datum['password'];
            $user->save();
        }
    }
}
