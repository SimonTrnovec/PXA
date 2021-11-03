<?php

namespace App\Model\Repositories;

use App;
use Nette;

class VisibilityStatesEnum extends Nette\Object
{
    const ENABLED = 'E';

    const DISABLED = 'P';

    const DELETED = 'D';

    public static function getItems($includedDeleted = TRUE)
    {
        $items = [
            static::ENABLED => 'Zapnutý',
            static::DISABLED => 'Vypnutý',
            static::DELETED => 'Zmazaný',
        ];

        if (!$includedDeleted) {
            unset($items[static::DELETED]);
        }

        return $items;
    }

}