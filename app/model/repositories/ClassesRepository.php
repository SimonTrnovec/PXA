<?php

namespace App\Model\Repositories;

use App;
use Nette;

class ClassesRepository extends BaseRepository
{
    protected function setup()
    {
       $this->table = 'classes';
       $this->primaryKey = 'class_id';
       $this->alias = 'cl';

    }

    public function insert($data)
    {
        $data['created'] = new DataTime();

        return parent::insert($data);
    }

}