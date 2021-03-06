<?php

namespace App\Presenters;

use App\Model\Repositories\ClassroomsRepository;
use Dibi\Row;
use Nette;
use Nette\Application\UI\Form;


final class ClassroomsPresenter extends BasePresenter
{

    /**
     * @inject
     * @var ClassroomsRepository
     */
    public $classroomsRepository;

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
        $itemsQuery = $this->classroomsRepository->findAll();

        if (isset($this->filter['classroom_name'])) {
            $itemsQuery->where('[cr.classroom_name] LIKE %~like~', $this->filter['classroom_name']);
        }

        $this->items = $itemsQuery->fetchAssoc('classroom_id');
    }

    public function renderDefault()
    {
        $this->template->classrooms = $this->items;
    }

    protected function createComponentFilterForm()
    {
        $form = new Form;

        $form->addGroup();

        $form->addText('classroom_name', 'Názov učebne');

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

    protected function createComponentClassroomForm(): Form
    {
        $form = new Form;
        $form->addText('classroom_name', 'Názov učebne');
        $form->addText('height', 'Výška učebne');
        $form->addText('width', 'Šírka učebne');

        return $form;
    }

    private function getClassroom($id)
    {
        $classroom = $this->classroomsRepository->findAll()->where('[cr.classroom_id] = %i', $id)->fetch();

        if (!$classroom){
            $this->flashMessage('Učebňa neexistuje.', 'error');
            $this->redirect('default');
        }

        return $classroom;
    }

    public function actionEdit($id): Form
    {
        $classroom = $this->getClassroom($id);

        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['classroomForm'];

        $this->template->classroom = $classroom;

        $form->setDefaults($classroom);
        $form->addSubmit('ok', 'Upraviť');
        $form->onSuccess[] = [$this, 'classroomFormEditSucceeded'];

        return $form;
    }

    public function actionAdd($id): Form
    {
        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['classroomForm'];

        $form->addSubmit('ok', 'Pridať');
        $form->onSuccess[] = [$this, 'classroomFormAddSucceeded'];

        return $form;
    }

    public function actionDetail($id)
    {
        $this->template->classrooms = $this->classroomsRepository->findAll()->where('[cr.classroom_id] = %i' , $id)->fetchAll();
    }

    public function classroomFormEditSucceeded($form)
    {
        $values = $form->getValues();
        $classroomId = $this->getParameter('id');

        $classroomData = [
            'classroom_name' => $values->classroom_name,
            'height'         => $values->height,
            'width'          => $values->width,
        ];

        $this->classroomsRepository->update($classroomId, $classroomData);

        $this->flashMessage('Učebňa bola upravená');
        $this->redirect('default');
    }

    public function handleDelete($id){

        $classroom = $this->getClassroom($id);

        $this->classroomsRepository->delete($classroom->classroom_id);

        $this->flashMessage('Učebňa bola zmazaná');

        $this->redirect('default');
    }

    public function classroomFormAddSucceeded(Form $form, $values): void
    {
        $values = $form->getValues();

        $this->classroomsRepository->insert([
            'classroom_name'      => $values->classroom_name,
            'height'              => $values->height,
            'width'               => $values->width,
        ]);

        $this->flashMessage('Učebňa bola prdaná');

        $this->redirect('default');

    }
}
