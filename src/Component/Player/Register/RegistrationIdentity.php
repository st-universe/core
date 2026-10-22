<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

final class RegistrationIdentity
{
    public static function email(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public static function mobile(string $mobile): string
    {
        return str_replace(['+', ' '], ['00', ''], trim($mobile));
    }
}
