<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Historically this added a `users.company` column for the SKY/JOJO split.
 * The portal is now single-company, so the column is no longer created. The
 * migration is kept as a no-op to preserve the migration sequence for
 * databases that already recorded this version.
 */
class AddUserCompany extends Migration
{
    public function up()
    {
        // no-op: company support removed
    }

    public function down()
    {
        // no-op
    }
}
