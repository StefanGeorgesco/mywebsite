<?php
namespace FormBuilder;

use \OCFram\FormBuilder;
use \OCFram\StringField;
use \OCFram\PasswordField;
use \OCFram\Script;
use \OCFram\NotNullValidator;
use \OCFram\MaxLengthValidator;
use \OCFram\MatchValidator;
use \OCFram\EqualValueValidator;
use \OCFram\AutotrimFormatter;
use \OCFram\NormalizeFormatter;
use \Entity\Member;

class NewMemberFormBuilder extends FormBuilder
{
    public function build()
    {
        $passField = new PasswordField([
            'label' => 'Mot de passe',
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

        $this->form->add(new StringField([
            'label' => 'Pseudo',
            'id' => 'login',
            'name' => 'login',
            'maxLength' => 30,
            'validators' => [
                new MaxLengthValidator(
                    'Le pseudo ne doit pas comporter plus de 30 caractères',
                    30
                ),
                new MatchValidator(
                    Member::LOGIN_MATCH_TEXT,
                    Member::LOGIN_MATCH_REGEXP
                ),
            ],
        ]))
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
                    'Le mot de passe de confirmation doit être identique',
                    $passField,
                    false
                ),
            ],
        ]))
        ->add(new StringField([
            'label' => 'Email',
            'id' => 'email',
            'name' => 'email',
            'maxLength' => 255,
            'validators' => [
                new MaxLengthValidator(
                    'L\'adresse e-mail ne doit pas comporter plus de 255 caractères',
                    255
                ),
                new MatchValidator(
                    Member::EMAIL_MATCH_TEXT,
                    Member::EMAIL_MATCH_REGEXP
                ),
            ],
        ]))
        ->addScript(new Script([
            'url' => 'https://cdnjs.cloudflare.com/ajax/libs/rxjs/6.5.5/rxjs.umd.js',
            'fileName' => '/../../Web/js/checkLogin.js',
            'initFunctionName' => 'checkLoginStart',
        ]))
        ;
    }
}
