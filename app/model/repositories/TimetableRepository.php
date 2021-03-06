<?php

namespace App\Model\Repositories;

use App;
use Dibi\DateTime;
use Nette;

class TimetableRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'timetables';
        $this->primaryKey = 'timetable_id';
        $this->alias = 'tt';

    }

    public function insert($data)
    {
        if (!isset($data['created'])){
            $data['created'] = new DateTime();
        }

        return parent::insert($data);
    }

}