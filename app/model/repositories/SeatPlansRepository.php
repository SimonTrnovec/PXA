<?php

namespace App\Model\Repositories;

use App;
use Nette;

class ClassesRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'seat_plans';
        $this->$this->primaryKey = 'seat_plan';
        $this->alias = 'sp';

    }

}