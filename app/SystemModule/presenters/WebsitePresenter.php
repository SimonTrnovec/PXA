<?php

namespace App\SystemModule\Presenter;

use App;
use Nette;

class WebsitePresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();

        $this->getUser()->getStorage()->setNamespace(App\Security\User::NAMESPACE_BACKEND);

        if (!$this->getUser()->isLoggedIn()){
            $this->flashMessage('Musíte sa prihlásiť pre zobrazenie zoznamu webov.', 'error');
            $this->redirect(':BackendAuth:login' , ['key' => $this->storeRequest()]);
        }
    }

    public function renderDefault()
    {
        if ($this->isAjax()) {
            $this->redrawControl('full');
        }
    }
}