<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'viewer';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'ผู้ดูแลระบบสูงสุด เข้าถึงได้ทุกส่วน',
        ],
        'editor' => [
            'title'       => 'ผู้จัดการเนื้อหา',
            'description' => 'จัดการผู้ใช้และดูบทบาทได้',
        ],
        'viewer' => [
            'title'       => 'ผู้ชม',
            'description' => 'ดูข้อมูลได้อย่างเดียว',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        'admin.access'    => 'เข้าถึงพื้นที่หลังบ้าน',
        'users.view'      => 'ดูรายชื่อผู้ใช้',
        'users.create'    => 'เพิ่มผู้ใช้',
        'users.edit'      => 'แก้ไขผู้ใช้',
        'users.delete'    => 'ลบผู้ใช้',
        'roles.view'      => 'ดูบทบาท/กลุ่มสิทธิ์',
        'roles.manage'    => 'จัดการบทบาท/กลุ่มสิทธิ์',
        'settings.manage' => 'จัดการตั้งค่าระบบ',
        'logs.view'       => 'ดูบันทึกกิจกรรม',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'superadmin' => [
            'admin.*',
            'users.*',
            'roles.*',
            'settings.*',
            'logs.*',
        ],
        'editor' => [
            'admin.access',
            'users.view',
            'users.create',
            'users.edit',
            'roles.view',
        ],
        'viewer' => [
            'admin.access',
            'users.view',
            'roles.view',
        ],
    ];
}
