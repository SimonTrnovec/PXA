<?php

namespace App\Model\Repositories;

use App;
use Dibi\DateTime;
use Nette;

class TimesRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'times';
        $this->primaryKey = 'time_id';
        $this->alias = 'tm';

    }

    public function insert($data)
    {
        if (!isset($data['created'])){
            $data['created'] = new DateTime();
        }

        return parent::insert($data);
    }


}