<?php
namespace FormBuilder;

use \OCFram\FormBuilder;
use \OCFram\StringField;
use \OCFram\TextField;
use \OCFram\NotNullValidator;

class CommentFormBuilder extends FormBuilder
{
    public function build()
    {
        $this->form->add(new TextField([
            'label' => 'Contenu',
            'id' => 'contents',
            'name' => 'contents',
            'rows' => 7,
            'cols' => 50,
            'validators' => [
                new NotNullValidator('Merci de spécifier votre commentaire'),
            ],
        ]));
    }
}
