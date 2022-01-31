<?php

namespace App\Presenters;

use Nette;


final class ClassesPresenter extends BasePresenter
{

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
    }
}
