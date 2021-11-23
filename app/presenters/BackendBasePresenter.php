<?php

namespace App\Presenters;

use App;
use Nette;
use Nette\Utils\ArrayHash;

abstract class BackendBasePresenter
{

    /**
     * @autowire
     * @var App\Model\Repositories\AdminsRepository
     */
    protected $adminsRepository;

    /**
     * @var string
     */
    public $adminType;

    protected function startup()
    {

        $this->getUser()->getStorage()->setNamespace(App\Security\User::NAMESPACE_BACKEND);

        if ($this->getUser()->isLoggedIn()) {
            $admin = $this->adminsRepository->find($this->getUser()->getId());
            $this->adminType = $admin->type;
        }
    }
}