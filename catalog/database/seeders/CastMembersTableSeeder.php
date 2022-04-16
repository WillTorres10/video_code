<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CastMember;

class CastMembersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CastMember::factory(100)->create();
    }
}
