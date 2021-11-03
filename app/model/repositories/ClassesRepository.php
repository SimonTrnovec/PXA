<?php

namespace App\Model\Repositories;

use App;
use Nette;

class ClassesRepository extends BaseRepository
{
    protected function setup()
    {
       $this->table = 'classes';
       $this->$this->primaryKey = 'class_id';
       $this->alias = 'cl';

    }

}