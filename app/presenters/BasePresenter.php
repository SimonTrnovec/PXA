<?php

namespace App\Presenters;

use App;
use Nette;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
  public function beforeRender()
  {

  }

  protected function startup()
  {
      parent::startup();

      if(! $this->user->isLoggedIn()){
          $this->redirect('BackendAuth:login');
      }
      $user = $this->getUser();
      $this->template->isAdmin = $user->getIdentity()->admin;
  }

    public function isAdmin()
    {
        $user = $this->getUser();
        $isAdmin = $user->getIdentity()->admin;
        if ($isAdmin == ADMIN){

        } else{
            $this->redirect('BackendAuth:Homepage');
        }
    }

    public function isTeacher()
    {
        $user = $this->getUser();
        $isAdmin = $user->getIdentity()->admin;
        if ($isAdmin >= TEACHER){

        } else{
            $this->redirect('BackendAuth:Homepage');
        }
    }

    public function handleLogout()
    {
        $this->getUser()->logout(true);
        $this->redirect('BackendAuth:login');
    }
}