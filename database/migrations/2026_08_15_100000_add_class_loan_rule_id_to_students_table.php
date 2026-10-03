<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('class_loan_rules')) {
            foreach ([
                ['class_level' => 'X', 'max_books' => 6, 'max_paket' => 4, 'max_bebas' => 2, 'loan_days' => 7],
                ['class_level' => 'XI', 'max_books' => 6, 'max_paket' => 4, 'max_bebas' => 2, 'loan_days' => 7],
                ['class_level' => 'XII', 'max_books' => 7, 'max_paket' => 5, 'max_bebas' => 2, 'loan_days' => 14],
            ] as $rule) {
                $exists = DB::table('class_loan_rules')->where('class_level', $rule['class_level'])->exists();
                if (! $exists) {
                    DB::table('class_loan_rules')->insert(array_merge($rule, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }
        }

        if (! Schema::hasColumn('students', 'class_loan_rule_id') && Schema::hasTable('class_loan_rules')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('class_loan_rule_id')
                    ->nullable()
                    ->after('class')
                    ->constrained('class_loan_rules')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            });

            $rules = DB::table('class_loan_rules')->pluck('id', 'class_level');

            foreach ($rules as $level => $id) {
                DB::table('students')
                    ->where('class', $level)
                    ->update(['class_loan_rule_id' => $id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('class_loan_rule_id');
        });
    }
};
