<?php

namespace App\Model\Repositories;

use App;
use Nette;

class CustomersRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = '';
        $this->$this->primaryKey = '';
        $this->alias = '';

    }

}