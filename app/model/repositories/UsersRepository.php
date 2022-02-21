<?php

namespace App\Model\Repositories;

use App;
use Dibi\DateTime;
use Nette;

class UsersRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'users';
        $this->primaryKey = 'user_id';
        $this->alias = 'us';

    }

    public function insert($data)
    {
        if (!isset($data['created'])){
            $data['created'] = new DateTime();
        }

        return parent::insert($data);
    }

}