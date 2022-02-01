<?php

namespace App\Model\Repositories;

use App;
use Dibi\DateTime;
use Nette;

class StudentsRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'students';
        $this->primaryKey = 'student_id';
        $this->alias = 'st';

    }

    public function insert($data)
    {
        if (!isset($data['created'])){
            $data['created'] = new DateTime();
        }

        return parent::insert($data);
    }

}