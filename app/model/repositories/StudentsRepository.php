<?php

namespace App\Model\Repositories;

use App;
use Nette;

class StudentsRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'students';
        $this->primaryKey = 'student_id';
        $this->alias = 'st';

    }

}