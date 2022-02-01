<?php

namespace App\Presenters;
use App\Model\Repositories\TeachersRepository;
use App\Model\Repositories\ClassesRepository;
use App\Model\Repositories\ClassroomsRepository;

use Nette;


final class TeachersPresenter extends BasePresenter
{

    /**
     * @inject
     * @var TeachersRepository
     */
    public $teachersRepository;

    /**
     * @inject
     * @var TeachersRepository
     */
    public $classesRepository;

    /**
     * @inject
     * @var ClassroomsRepository
     */
    public $classroomsRepository;

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
        $this->template->teachers = $this->teachersRepository->findAll()
            ->select('[cl.class_name], [cr.classroom_name]')
            ->leftJoin('[classes] cl')
            ->on('[cl.class_id] = [te.class_id]')
            ->leftJoin('[classrooms] cr')
            ->on('[te.classroom_id] = [cr.classroom_id]')
            ->fetchAll();

    }
}
