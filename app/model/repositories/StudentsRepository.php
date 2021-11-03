<?php

namespace App\Model\Repositories;

use App;
use Nette;

class ClassesRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'students';
        $this->$this->primaryKey = 'student_id';
        $this->alias = 'st';

    }

}