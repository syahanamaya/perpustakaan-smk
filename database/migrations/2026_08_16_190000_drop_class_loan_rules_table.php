<?php

use App\Support\ClassLoanQuota;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('class_loan_rules')) {
            $exported = ClassLoanQuota::defaults();
            foreach (DB::table('class_loan_rules')->get() as $row) {
                $level = strtoupper((string) $row->class_level);
                if (! isset($exported[$level])) {
                    continue;
                }
                $exported[$level]['max_paket'] = (int) ($row->max_paket ?? $exported[$level]['max_paket']);
                $exported[$level]['max_bebas'] = (int) ($row->max_bebas ?? $exported[$level]['max_bebas']);
            }
            ClassLoanQuota::save($exported);
        }

        if (Schema::hasColumn('students', 'class_loan_rule_id')) {
            Schema::table('students', function ($table) {
                $table->dropConstrainedForeignId('class_loan_rule_id');
            });
        }

        Schema::dropIfExists('class_loan_rules');
    }

    public function down(): void
    {
        // Tidak dibuat ulang; kuota kelas disimpan di storage/app/loan_quotas.json
    }
};
