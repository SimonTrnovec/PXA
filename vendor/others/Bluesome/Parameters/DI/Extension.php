<?php

namespace Bluesome\Parameters\DI;

use Nette;
use Nette\DI\CompilerExtension;

class Extension extends CompilerExtension
{

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('provider'))
            ->setClass('Bluesome\Parameters\Provider')
            ->setArguments(['@Nette\DI\Container::parameters']);
    }

}