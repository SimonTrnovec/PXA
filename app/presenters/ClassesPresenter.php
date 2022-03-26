<?php

namespace App\Presenters;

use App\Model\Repositories\ClassesRepository;
use Nette;
use Nette\Application\UI\Form;


final class ClassesPresenter extends BasePresenter
{

    /**
     * @inject
     * @var ClassesRepository
     */
    public $classesRepository;

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
        $this->template->classes = $this->classesRepository->findAll()->fetchAll();
    }

    protected function createComponentClassForm(): Form
    {
        $form = new Form;
        $form->addText('class_name', 'Názov Triedy');

        return $form;
    }

    private function getClass($id)
    {
        $class = $this->classesRepository->findAll()->where('[cl.class_id] = %i', $id)->fetch();

        if (!$class){
            $this->flashMessage('Trieda neexistuje.', 'error');
            $this->redirect('default');
        }

        return $class;
    }

    public function actionEdit($id): Form
    {
        $class = $this->getClass($id);

        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['classForm'];

        $this->template->class = $class;

        $form->setDefaults($class);
        $form->addSubmit('ok', 'Upraviť');
        $form->onSuccess[] = [$this, 'classFormEditSucceeded'];

        return $form;
    }

    public function actionAdd($id): Form
    {
        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['classForm'];

        $form->addSubmit('ok', 'Pridať');
        $form->onSuccess[] = [$this, 'classFormAddSucceeded'];

        return $form;
    }

    public function classFormEditSucceeded($form)
    {
        $values = $form->getValues();
        $classId = $this->getParameter('id');

        $classData = [
            'class_name' => $values->class_name,
        ];

        $this->classesRepository->update($classId, $classData);

        $this->flashMessage('Trieda bola upravená');
        $this->redirect('default');
    }

    public function handleDelete($id){

        $class = $this->getClass($id);

        $this->classesRepository->delete($class->class_id);

        $this->flashMessage('Trieda bola zmazaná');

        $this->redirect('default');
    }

    public function classFormAddSucceeded(Form $form, $values): void
    {
        $values = $form->getValues();

        $this->classesRepository->insert([
            'class_name'      => $values->class_name,
        ]);

        $this->flashMessage('Trieda bola prdaná');

        $this->redirect('default');

    }
}
