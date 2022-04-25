<?php

namespace App\Presenters;

use App;
use App\Model\Repositories\StudentsRepository;
use Nette;
use Nette\Application\UI\Form;
use App\Model\Repositories\UsersRepository;
use Nette\Security\Authenticator;



class BackendAuthPresenter extends Nette\Application\UI\Presenter
{
    /**
     * @inject
     * @var UsersRepository
     */
    public $usersRepository;

    /**
     * @inject
     * @var App\Security\Authenticator
     */
    public $authenticator;

    protected function startup()
    {
        parent::startup();

        if($this->user->isLoggedIn()){
            $this->redirect('Homepage:default');
        }
    }

    public function createComponentLoginForm(): Form
    {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím vyplnte svoje uživateľské meno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte svoje heslo.');

        $form->addSubmit('send', 'Prihlásit');

        $form->onSuccess[] = [$this, 'loginFormSucceeded'];
        return $form;
    }

    public function loginFormSucceeded(Form $form, $values): void
    {
        $values = $form->getValues();
        $this->getUser()->setAuthenticator($this->authenticator);
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->flashMessage('Boli ste úspešne prihlásený');
            $this->redirect('Homepage:');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávne prihlasovacie meno alebo heslo.');
        }

    }

    public function createComponentRegisterForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Uživatelské jméno:')
            ->setRequired('Prosím vyplňte svoje uživateľské meno.');

        $form->addEmail('email', 'Uživatelský E-mail:')
            ->setRequired('Prosím vyplňte svoje E-mail.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte svoje heslo.');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = [$this, 'registerFormSucceeded'];
        return $form;
    }

    public function registerFormSucceeded(Form $form, $values): void
    {
        $values = $form->getValues();

        $password = $this->authenticator->getHash($values->password);

        $userData = [
            'name' => $values->name,
            'email' => $values->email,
            'password' => $password,
        ];

        $this->usersRepository->insert($userData);

        $this->flashMessage('Účet bol vytvorený');

        $this->redirect('login');

    }

    public function handleLogout()
    {
        $this->getUser()->logout(true);
        $this->redirect('BackendAuth:login');
    }
}