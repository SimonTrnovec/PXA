<?php

namespace App\Model\Repositories;

use App;
use Dibi\DateTime;
use Nette;

class SubjectsRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'subjects';
        $this->primaryKey = 'subject_id';
        $this->alias = 'su';

    }

    public function insert($data)
    {
        if (!isset($data['created'])){
            $data['created'] = new DateTime();
        }

        return parent::insert($data);
    }

}