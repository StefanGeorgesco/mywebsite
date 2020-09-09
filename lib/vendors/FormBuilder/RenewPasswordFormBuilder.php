<?php
namespace FormBuilder;

use \OCFram\FormBuilder;
use \OCFram\PasswordField;
use \OCFram\NotNullValidator;
use \OCFram\MatchValidator;
use \OCFram\EqualValueValidator;
use \Entity\Member;

class RenewPasswordFormBuilder extends FormBuilder
{
    public function build()
    {
        $passField = new PasswordField([
            'label' => 'Nouveau mot de passe',
            'id' => 'pass',
            'name' => 'pass',
            'maxLength' => 100,
            'validators' => [
                new MatchValidator(
                    Member::PASS_MATCH_TEXT,
                    Member::PASS_MATCH_REGEXP
                ),
            ],
        ]);

        $this->form
        ->add($passField)
        ->add(new PasswordField([
            'label' => 'Confirmez le mot de passe',
            'id' => 'pass2',
            'name' => 'pass2',
            'maxLength' => 100,
            'validators' => [
                new NotNullValidator(
                    'Le mot de passe de confirmation doit être renseigné'
                ),
                new EqualValueValidator(
                    'Le mot de passe de confirmation n\'est pas identique',
                    $passField,
                    false
                ),
            ],
        ]))
        ;
    }
}
