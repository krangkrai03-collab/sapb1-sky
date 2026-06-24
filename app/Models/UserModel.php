<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

/**
 * Extends Shield's UserModel to persist our extra profile columns
 * (name, avatar) added by the AddUserProfileFields migration.
 */
class UserModel extends ShieldUserModel
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = array_merge($this->allowedFields, [
            'name',
            'avatar',
            'locale',
            'company',
        ]);
    }
}
