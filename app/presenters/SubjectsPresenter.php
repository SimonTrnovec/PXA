<?php

namespace App\Presenters;
use App\Model\Repositories\SubjectsRepository;
use Dibi\Row;
use Nette;
use Nette\Application\UI\Form;


final class SubjectsPresenter extends BasePresenter
{

    /**
     * @inject
     * @var SubjectsRepository
     */
    public $subjectsRepository;

    /**
     * @persistent
     * @var array
     */
    public $filter = [];

    /**
     * @var Row
     */
    protected $items = [];

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
        $itemsQuery = $this->subjectsRepository->findAll();

        if (isset($this->filter['subject_name'])) {
            $itemsQuery->where('[su.subject_name] LIKE %~like~', $this->filter['subject_name']);
        }

        $this->items = $itemsQuery->fetchAssoc('subject_id');
    }

    public function renderDefault()
    {
        $this->template->subjects = $this->items;
    }

    protected function createComponentFilterForm()
    {
        $form = new Form;

        $form->addGroup();

        $form->addTExt('subject_name', 'Predmet');

        $form->setDefaults($this->filter);

        $form->addSubmit('ok', 'Filtrovať');
        $form->addSubmit('cancel', 'Zrušiť');

        $form->onSuccess[] = [$this, 'filterFormSucceeded'];

        return $form;
    }

    public function filterFormSucceeded($form)
    {
        if($form['cancel']->isSubmittedBy()) {
            $this->redirect('default', [
                'filter'    => NULL,
            ]);
        } else {
            $values = $form->getValues();

            $filteredValues = array_filter((array) $values);


            $this->redirect('default', [
                'filter'    => $filteredValues,
            ]);
        }

    }

    protected function createComponentSubjectForm(): Form
    {
        $form = new Form;
        $form->addText('subject_name', 'Predmet');

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
            'subject_name' => $values->subject_name,
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
            'subject_name'      => $values->subject_name,
        ]);

        $this->flashMessage('Predmet bol pridaný');

        $this->redirect('default');


    }



}
