<?php

namespace App\Presenters;
use App\Model\Repositories\SeatsRepostory;
use Nette;


final class SeatsPresenter extends BasePresenter
{
    public function startup()
    {
        parent::startup();

        if($this->isTeacher()){
            $this->redirect('BackendAuth:login');
        }
    }

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
    }
}
