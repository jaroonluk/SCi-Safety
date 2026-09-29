<?php

namespace App\Enums;

enum AccountType: string
{
    case Student = 'student';
    case Staff = 'staff';
    case External = 'external';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'นักศึกษา มข.',
            self::Staff => 'บุคลากร มข.',
            self::External => 'บุคคลภายนอก',
        };
    }
}
