<?php

namespace App\Model\Repositories;

use App;
use Dibi\DateTime;
use Nette;

class SeatsRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'seats';
        $this->primaryKey = 'seat_id';
        $this->alias = 'se';

    }

    public function insert($data)
    {
        if (!isset($data['created'])){
            $data['created'] = new DateTime();
        }

        return parent::insert($data);
    }


}