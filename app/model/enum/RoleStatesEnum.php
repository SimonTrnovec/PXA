<?php

namespace App\Model\Repositories;

use App;
use Nette;

class RoleStatesEnum
{
    use Nette\SmartObject;

    const ADMIN = '2';

    const TEACHER = '1';

    const STUDENT = '0';

    public static function getItems()
    {
        $items = [
            static::ADMIN => 'Administrátor',
            static::TEACHER => 'Učiteľ',
            static::STUDENT => 'Študent',
        ];

        return $items;
    }

}