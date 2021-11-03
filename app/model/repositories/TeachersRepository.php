<?php

namespace App\Model\Repositories;

use App;
use Nette;

class ClassesRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'teachers';
        $this->$this->primaryKey = 'teacher_id';
        $this->alias = 'te';

    }

}