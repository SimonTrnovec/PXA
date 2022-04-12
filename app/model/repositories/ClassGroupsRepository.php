<?php

namespace App\Model\Repositories;

use App;
use Dibi\DateTime;
use Nette;

class ClassGroupsRepository extends BaseRepository
{
    protected function setup()
    {
        $this->table = 'class_groups';
        $this->primaryKey = 'class_group_id';
        $this->alias = 'cg';

    }

    public function insert($data)
    {
        if (!isset($data['created'])){
            $data['created'] = new DateTime();
        }

        return parent::insert($data);
    }

}