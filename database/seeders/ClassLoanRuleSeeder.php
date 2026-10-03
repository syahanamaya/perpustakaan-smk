<?php

namespace Database\Seeders;

use App\Support\ClassLoanQuota;
use Illuminate\Database\Seeder;

class ClassLoanRuleSeeder extends Seeder
{
    public function run(): void
    {
        if (! is_file(ClassLoanQuota::path())) {
            ClassLoanQuota::save(ClassLoanQuota::defaults());
        }
    }
}
