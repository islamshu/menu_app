<?php

namespace App\Enums;

enum RestaurantRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Manager => 'Manager',
            self::Staff => 'Staff',
        };
    }

    /**
     * Roles that may administer restaurant settings and members.
     *
     * @return array<int, self>
     */
    public static function adminRoles(): array
    {
        return [self::Owner, self::Manager];
    }
}
