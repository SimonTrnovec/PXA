<?php

namespace App\Model\Repositories;

use App;
use Nette;
use Nette\Utils\DateTime;

class AdminsRepository extends BaseRepository
{

    protected function setup()
    {
        $this->table = 'admins';
        $this->primaryKey = 'admin_id';
        $this->alias = 'ad';
    }

    public function insert($data)
    {
        $data['created'] = new DateTime();

        return parent::insert($data);
    }

    public function existsByUsername($control, $exclude = NULL)
    {
        $query = $this->getCount()->where('[username] = %s', $control->value)->where('[state] != %s', App\Model\Enums\VisibilityStatesEnum::DELETED);

        if ($exclude) {
            $query->where('%n != %i', $this->primaryKey, $exclude);
        }

        return !$query->fetchSingle();
    }

    public function existsByEmail($control, $exclude = NULL)
    {
        $query = $this->getCount()->where('[email] = %s', $control->value)->where('[state] != %s', App\Model\Enums\VisibilityStatesEnum::DELETED);

        if ($exclude) {
            $query->where('%n != %i', $this->primaryKey, $exclude);
        }

        return !$query->fetchSingle();
    }

    public function findAll($languageId = NULL, $append = '')
    {
        return $this->db->select('%n.*, [m.filename], [m.folder], [m.extension]', $this->alias)
            ->from('%n %n', $this->table, $this->alias)
            ->leftJoin('[media] m')->using('([medium_id])');
    }

}