<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class HomepagePresenter extends BasePresenter
{

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
    }
}
