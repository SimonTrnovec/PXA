<?php

namespace App\Presenters;
use App\Model\Repositories\SubjectsRepository;
use Nette;
use Nette\Application\UI\Form;


final class SubjectsPresenter extends BasePresenter
{

    /**
     * @inject
     * @var SubjectsRepository
     */
    public $subjectsRepository;

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
        $this->template->subjects = $this->subjectsRepository->findAll()->fetchAll();
    }

    protected function createComponentSubjectForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Predmet');

        return $form;
    }

    private function getSubject($id)
    {
        $subject = $this->subjectsRepository->findAll()->where('[su.subject_id] = %i', $id)->fetch();

        if (!$subject){
            $this->flashMessage('Predmet neexistuje.', 'error');
            $this->redirect('default');
        }

        return $subject;
    }

    public function actionEdit($id): Form
    {
        $subject = $this->getSubject($id);

        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['subjectForm'];

        $this->template->subject = $subject;

        $form->setDefaults($subject);
        $form->addSubmit('ok', 'Upraviť');
        $form->onSuccess[] = [$this, 'subjectFormEditSucceeded'];

        return $form;
    }

    public function actionAdd($id): Form
    {
        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['subjectForm'];

        $form->addSubmit('ok', 'Pridať');
        $form->onSuccess[] = [$this, 'subjectFormAddSucceeded'];

        return $form;
    }

    public function subjectFormEditSucceeded($form)
    {
        $values = $form->getValues();
        $subjectId = $this->getParameter('id');

        $subjectData = [
            'name' => $values->name,
        ];

        $this->subjectsRepository->update($subjectId, $subjectData);

        $this->flashMessage('Predmet bol upravený');
        $this->redirect('default');
    }

    public function handleDelete($id){

        $subject = $this->getSubject($id);


        $this->subjectsRepository->delete($subject->subject_id);

        $this->flashMessage('Predmet bol zmazaný');

        $this->redirect('default');
    }


    public function SubjectFormAddSucceeded(Form $form, $values): void
    {
        $values = $form->getValues();

        $this->subjectsRepository->insert([
            'name'      => $values->name,
        ]);

        $this->flashMessage('Predmet bol pridaný');

        $this->redirect('default');


    }



}
