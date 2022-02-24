<?php

namespace App\Presenters;
use App\Model\Repositories\TimesRepository;
use Nette;
use Nette\Application\UI\Form;


final class TimesPresenter extends BasePresenter
{

    /**
     * @inject
     * @var TimesRepository
     */
    public $timesRepository;

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
        $this->template->times = $this->timesRepository->findAll()->fetchAll();
    }

    protected function createComponentTimeForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Čas');

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
            'name' => $values->name,
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
            'name'      => $values->name,
        ]);

        $this->flashMessage('Čas bol pridaný');

        $this->redirect('default');


    }



}
