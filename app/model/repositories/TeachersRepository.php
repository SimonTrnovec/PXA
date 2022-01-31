<?php

namespace App\Model\Repositories;

use App;
use Nette;

class TeachersRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'teachers';
        $this->primaryKey = 'teacher_id';
        $this->alias = 'te';

    }

    public function insert($data)
    {
        $data['created'] = new DataTime();

        return parent::insert($data);
    }

}