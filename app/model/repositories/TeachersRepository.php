<?php

namespace App\Model\Repositories;

use App;
use Dibi\DateTime;
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
        if (!isset($data['created'])){
            $data['created'] = new DateTime();
        }

        return parent::insert($data);
    }


}