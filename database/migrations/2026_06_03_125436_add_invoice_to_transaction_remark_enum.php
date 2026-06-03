<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN remark ENUM(
                'deposit',
                'withdrawal',
                'transfer',
                'convert',
                'level_bonus',
                'package_purchased',
                'director_bonus',
                'shareholder_bonus',
                'club_bonus',
                'rank_bonus',
                'director_pool',
                'shareholder_pool',
                'club_pool',
                'rank_pool',
                'invoice'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN remark ENUM(
                'deposit',
                'withdrawal',
                'transfer',
                'convert',
                'level_bonus',
                'package_purchased',
                'director_bonus',
                'shareholder_bonus',
                'club_bonus',
                'rank_bonus',
                'director_pool',
                'shareholder_pool',
                'club_pool',
                'rank_pool'
            ) NOT NULL
        ");
    }
};
