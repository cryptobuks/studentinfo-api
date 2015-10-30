<?php

namespace StudentInfo\Roles;


class AdminRole extends Role
{

    /**
     * Returns the permission of this role.
     *
     * @return array
     */
    public function getPermissions()
    {
        return [
            'user.edit',
            'user.create',
            'user.delete',
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_role';
    }
}