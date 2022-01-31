<?php

namespace App\Presenters;
use App\Model\Repositories\TeachersRepository;

use Nette;


final class TeachersPresenter extends BasePresenter
{

    /**
     * @inject
     * @var TeachersRepository
     */
    public $teachersRepository;

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
        $this->template->teachers = $this->teachersRepository->findAll()->fetchAll();

    }
}
