<?php

namespace trinity\validator;

use trinity\contracts\validator\FormRequestFactoryInterface;
use trinity\validator\AbstractFormRequest;

class FormRequestFactory implements FormRequestFactoryInterface
{
    /**
     * @param $container
     */
    public function __construct(private $container)
    {
    }

    /**
     * @param string $formNameSpace
     * @return AbstractFormRequest
     */
    public function create(string $formNameSpace): AbstractFormRequest
    {
        return $this->container->get($formNameSpace);
    }
}
