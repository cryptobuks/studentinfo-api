<?php

namespace StudentInfo\Roles;


class AssistantRole extends Role
{
    /**
     * Returns the permission of this role.
     *
     * @return array
     */
    public function getPermissions()
    {
        return [
            'user.update',
            'event.create',
            'event.update',
            'notification.retrieve',
            'data.retrieve',
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'professor_role';
    }
}
