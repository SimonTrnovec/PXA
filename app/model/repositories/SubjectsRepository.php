<?php

namespace App\Model\Repositories;

use App;
use Nette;

class SubjectsRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'subjects';
        $this->$this->primaryKey = 'subject_id';
        $this->alias = 'su';

    }

}