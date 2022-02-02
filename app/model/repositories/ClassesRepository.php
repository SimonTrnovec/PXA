<?php

namespace App\Model\Repositories;

use App;
use Dibi\DateTime;
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
        if (!isset($data['created'])){
            $data['created'] = new DateTime();
        }

        return parent::insert($data);
    }

}