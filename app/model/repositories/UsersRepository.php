<?php

namespace App\Model\Repositories;

use App;
use Nette;
use Nette\Utils\DateTime;

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
        $data['created'] = new DataTime();

        return parent::insert($data);
    }

    public function authenticate(string $email, string $password): SimpleIdentity{

    }

}