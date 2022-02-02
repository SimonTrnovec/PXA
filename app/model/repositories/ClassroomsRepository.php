<?php

namespace App\Model\Repositories;

use App;
use Dibi\DateTime;
use Nette;

class ClassroomsRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'classrooms';
        $this->primaryKey = 'classroom_id';
        $this->alias = 'cr';

    }

    public function insert($data)
    {
        if (!isset($data['created'])){
            $data['created'] = new DateTime();
        }

        return parent::insert($data);
    }

}