<?php
namespace FormBuilder;

use \OCFram\FormBuilder;
use \OCFram\StringField;
use \OCFram\MaxLengthValidator;
use \OCFram\MatchValidator;
use \Entity\Member;

class SendLinkFormBuilder extends FormBuilder
{
    public function build()
    {
        $this->form->add(new StringField([
            'label' => 'Entrez votre mail',
            'id' => 'email',
            'name' => 'email',
            'maxLength' => 255,
            'validators' => [
                new MaxLengthValidator(
                    'L\'adresse e-mail spécifiée est trop longue (255 caractères maximum)',
                    255
                ),
                new MatchValidator(
                    Member::EMAIL_MATCH_TEXT,
                    Member::EMAIL_MATCH_REGEXP
                ),
            ],
        ]));
    }
}
