<?php
namespace FormBuilder;

use \OCFram\FormBuilder;
use \OCFram\TextField;
use \OCFram\NotNullValidator;

class AuthorizationFormBuilder extends FormBuilder
{
    public function build()
    {
        $this->form->add(new TextField([
            'label' => 'Description',
            'id' => 'description',
            'name' => 'description',
            'rows' => 7,
            'cols' => 50,
            'validators' => [
                new NotNullValidator('Merci de spécifier votre description'),
            ],
        ]));
    }
}
