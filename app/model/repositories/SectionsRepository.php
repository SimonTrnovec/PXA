<?php

namespace App\Model\Repositories;

use App;
use Nette;
use Nette\Utils\DateTime;

class SectionsRepository extends BaseRepository
{

    protected function setup()
    {
        $this->table = 'sections';
        $this->primaryKey = 'section_id';
        $this->alias = 'se';
    }

    public function markHidden()
    {
        $this->db->update($this->table, ['state' => App\Model\Enums\VisibilityStatesEnum::DISABLED])->execute();
    }
//    @TODO vytvorit tabulku posielam na fb
}