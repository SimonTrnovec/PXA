<?php

namespace App\Model\Repositories;

use App;
use App\Model\Enums\VisibilityStatesEnum;
use Nette;

class AdminSectionsRepository extends BaseRepository
{

    protected function setup()
    {
        $this->table = 'admin_sections';
        $this->primaryKey = 'admin_section_id';
        $this->alias = 'as';
    }

    public function getSections($adminId)
    {
        return $this->findAll()->removeClause('select')->select('*')->join('[sections] s')->using('([section_id])')->where('[as.state] = %s', VisibilityStatesEnum::ENABLED)->where('[admin_id] = %i', $adminId)->fetchAssoc('key');
    }

    public function processSections($adminId, $sectionIds)
    {
        $this->db->update($this->table, ['state' => VisibilityStatesEnum::DISABLED])
            ->where('[admin_id] = %i', $adminId)
            ->execute();

        foreach ($sectionIds as $sectionId) {
            $existingAdminSection = $this->findAll()->where('[admin_id] = %i', $adminId)->where('[section_id] = %i', $sectionId)->fetch();

            if ($existingAdminSection) {
                $this->update($existingAdminSection->admin_section_id, ['state' => VisibilityStatesEnum::ENABLED]);
            } else {
                $this->insert([
                    'admin_id'   => $adminId,
                    'section_id' => $sectionId,
                    'state'      => VisibilityStatesEnum::ENABLED,
                ]);
            }
        }
    }

}