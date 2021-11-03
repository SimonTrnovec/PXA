<?php

namespace App\Model\Repositories;

use App;
use Nette;

class ClassesRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'classrooms';
        $this->$this->primaryKey = 'clasroom_id';
        $this->alias = 'cr';

    }

}