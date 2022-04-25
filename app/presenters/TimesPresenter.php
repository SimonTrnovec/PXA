<?php

namespace App\Presenters;
use App\Model\Repositories\TimesRepository;
use Dibi\Row;
use Nette;
use Nette\Application\UI\Form;


final class TimesPresenter extends BasePresenter
{

    /**
     * @inject
     * @var TimesRepository
     */
    public $timesRepository;

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
        $itemsQuery = $this->timesRepository->findAll();

        if (isset($this->filter['time_id'])) {
            $itemsQuery->where('[tm.time_id] LIKE %~like~', $this->filter['time_id']);
        }

        $this->items = $itemsQuery->fetchAssoc('time_id');
    }

    public function renderDefault()
    {
        $this->template->times = $this->items;
    }

    protected function createComponentFilterForm()
    {
        $form = new Form;

        $form->addGroup();

        $times = $this->timesRepository->findAll()->fetchAll();

        $thime = [];

        foreach ($times as $time){
            $thime[$time->time_id] = $time->time_name;
        }

        $form->addSelect('time_id', 'Čas', $thime)
            ->setPrompt('Vyberte Čas');

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

    protected function createComponentTimeForm(): Form
    {
        $form = new Form;
        $form->addText('time_name', 'Čas');

        return $form;
    }

    private function getTime($id)
    {
        $time = $this->timesRepository->findAll()->where('[tm.time_id] = %i', $id)->fetch();

        if (!$time){
            $this->flashMessage('Čas neexistuje.', 'error');
            $this->redirect('default');
        }

        return $time;
    }

    public function actionEdit($id): Form
    {
        $time = $this->getTime($id);

        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['timeForm'];

        $this->template->subject = $time;

        $form->setDefaults($time);
        $form->addSubmit('ok', 'Upraviť');
        $form->onSuccess[] = [$this, 'timeFormEditSucceeded'];

        return $form;
    }

    public function actionAdd($id): Form
    {
        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['timeForm'];

        $form->addSubmit('ok', 'Pridať');
        $form->onSuccess[] = [$this, 'timeFormAddSucceeded'];

        return $form;
    }

    public function timeFormEditSucceeded($form)
    {
        $values = $form->getValues();
        $timeId = $this->getParameter('id');

        $timeData = [
            'time_name' => $values->time_name,
        ];

        $this->timesRepository->update($timeId, $timeData);

        $this->flashMessage('Čas bol upravený');
        $this->redirect('default');
    }

    public function handleDelete($id){

        $time = $this->getTime($id);


        $this->timesRepository->delete($time->time_id);

        $this->flashMessage('Čas bol zmazaný');

        $this->redirect('default');
    }


    public function TimeFormAddSucceeded(Form $form, $values): void
    {
        $values = $form->getValues();

        $this->timesRepository->insert([
            'time_name'      => $values->time_name,
        ]);

        $this->flashMessage('Čas bol pridaný');

        $this->redirect('default');


    }



}
