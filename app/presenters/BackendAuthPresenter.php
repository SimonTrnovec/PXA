<?php

namespace App\Presenters;

use App;
use Nette;
use Nette\Application\UI\Form;
use App\Model\Repositories\UsersRepository;



class BackendAuthPresenter extends Nette\Application\UI\Presenter
{
    /**
     * @var \App\Model\Repositories\UsersRepository
     */
    private $usersRepository;

    /**
     * @inject
     * @var App\Security\Authenticator
     */
    public $authenticator;

    public function createComponentLoginForm(): Form
    {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím vyplňte své uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo.');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = [$this, 'loginFormSucceeded'];
        return $form;
    }

    public function loginFormSucceeded(Form $form, $values): void
    {
        $values = $form->getValues();
        $this->getUser()->setAuthenticator($this->authenticator);
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->flashMessage('boli ste úspešne prihlásený');
            $this->redirect('Homepage:');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }

    }

    public function handleLogout()
    {
        $this->getUser()->logout(true);
        $this->redirect('BackendAuth:login');
    }
}