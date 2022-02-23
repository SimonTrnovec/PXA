<?php

namespace App\Model\Repositories;

use App;
use Nette;

class TimetableRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'timetables';
        $this->primaryKey = 'timetable';
        $this->alias = 'tt';

    }

}