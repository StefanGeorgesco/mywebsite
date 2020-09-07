<?php
namespace FormBuilder;

use \OCFram\FormBuilder;
use \OCFram\StringField;
use \OCFram\PasswordField;
use \OCFram\NotNullValidator;
use \Entity\Member;

class ConnexionFormBuilder extends FormBuilder
{
    public function build()
    {
        $this->form->add(new StringField([
            'label' => 'Pseudo',
            'id' => 'login',
            'name' => 'login',
            'maxLength' => 30,
            'validators' => [
                new NotNullValidator(
                    'Le pseudo doit être renseigné'
                ),
            ],
        ]))
        ->add(new PasswordField([
            'label' => 'Mot de passe',
            'id' => 'pass',
            'name' => 'pass',
            'maxLength' => 100,
            'validators' => [
                new NotNullValidator(
                    'Le mot de passe doit être renseigné'
                ),
            ],
        ]));
    }
}
