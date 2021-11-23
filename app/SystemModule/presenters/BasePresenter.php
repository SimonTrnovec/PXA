<?php

namespace App\SystemModule\Presenters;

use App;
use Nette;

abstract class BasePresenter extends App\Presenters\BackendBasePresenter
{

    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->isAjax = $this->isAjax();
        if ($this->isAjax()) {
            $this->redrawControl('modal');
        }
    }
}