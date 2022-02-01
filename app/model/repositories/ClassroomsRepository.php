<?php

namespace App\Model\Repositories;

use App;
use Nette;

class ClassroomsRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'classrooms';
        $this->primaryKey = 'classroom_id';
        $this->alias = 'cr';

    }

}