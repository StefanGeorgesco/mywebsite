<?php
namespace FormBuilder;

use \OCFram\FormBuilder;
use \OCFram\StringField;
use \OCFram\DateField;
use \OCFram\ButtonField;
use \OCFram\Script;
use \OCFram\MaxLengthValidator;
use \OCFram\MatchValidator;
use \OCFram\MinAgeValidator;
use \OCFram\MaxAgeValidator;
use \OCFram\AutotrimFormatter;
use \OCFram\NormalizeFormatter;
use \Entity\Member;

class UpdateMemberFormBuilder extends FormBuilder
{
    public function build()
    {
        $this->form
        ->add(new StringField([
            'label' => 'Prénom',
            'id' => 'firstName',
            'name' => 'firstName',
            'maxLength' => 50,
            'size' => 50,
            'validators' => [
                new MaxLengthValidator(
                    'Le prénom ne doit pas comporter plus de 30 caractères',
                    30
                ),
            ],
            'formatter' => new AutotrimFormatter,
        ]))
        ->add(new StringField([
            'label' => 'Nom',
            'id' => 'lastName',
            'name' => 'lastName',
            'size' => 50,
            'maxLength' => 50,
            'validators' => [
                new MaxLengthValidator(
                    'Le nom ne doit pas comporter plus de 30 caractères',
                    30
                ),
            ],
            'formatter' => new AutotrimFormatter,
        ]))
        ->add(new DateField([
            'label' => 'Date de naissance',
            'id' => 'birthDate',
            'name' => 'birthDate',
            'validators' => [
                new MinAgeValidator(
                    Member::MIN_AGE_TEXT,
                    Member::MIN_AGE
                ),
                new MaxAgeValidator(
                    Member::MAX_AGE_TEXT,
                    Member::MAX_AGE
                ),
            ],
        ]))
        ->add(new StringField([
            'label' => 'Pseudo',
            'id' => 'login',
            'name' => 'login',
            'maxLength' => 30,
            'size' => 30,
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
        ->add(new StringField([
            'label' => 'Email',
            'id' => 'email',
            'name' => 'email',
            'maxLength' => 255,
            'size' => 50,
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
        ->add(new StringField([
            'label' => 'Site Web',
            'id' => 'website',
            'name' => 'website',
            'maxLength' => 255,
            'size' => 50,
            'validators' => [
                new MaxLengthValidator(
                    'L\'url du site web  ne doit pas comporter plus de 255 caractères',
                    255
                ),
                new MatchValidator(
                    Member::WEBSITE_MATCH_TEXT,
                    Member::WEBSITE_MATCH_REGEXP
                ),
            ],
            'formatter' => new AutotrimFormatter,
        ]))
        ->add(new StringField([
            'label' => 'Téléphone',
            'id' => 'phone',
            'name' => 'phone',
            'maxLength' => 14,
            'size' => 14,
            'validators' => [
                new MaxLengthValidator(
                    'Le téléphone ne doit pas comporter plus de 14 caractères',
                    14
                ),
                new MatchValidator(
                    Member::PHONE_MATCH_TEXT,
                    Member::PHONE_MATCH_REGEXP
                ),
            ],
            'formatter' =>
                new NormalizeFormatter(
                    '#^\s*(0[1-8])[-. ]?([0-9]{2})[-. ]?([0-9]{2})[-. ]?([0-9]{2})[-. ]?([0-9]{2})\s*$#',
                    '$1 $2 $3 $4 $5'
                ),
        ]))
        ->add(new StringField([
            'label' => 'Adresse',
            'id' => 'address',
            'name' => 'address',
            'placeholder' => 'Cherchez votre adresse...',
            'maxLength' => 120,
            'size' => 50,
            'validators' => [
                new MaxLengthValidator(
                    'L\'adresse ne doit pas comporter plus de 120 caractères',
                    120
                ),
            ],
            'formatter' => new AutotrimFormatter,
        ]))
        ->add(new ButtonField([
            'label' => 'Effacer l\'adresse',
            'id' => 'clearButton',
        ]))
        ->add(new StringField([
            'label' => 'N°',
            'id' => 'housenumber',
            'name' => 'housenumber',
            'maxLength' => 10,
            'size' => 10,
            'validators' => [
                new MaxLengthValidator(
                    'Le numéro ne doit pas comporter plus de 10 caractères',
                    10
                ),
            ],
            'formatter' => new AutotrimFormatter,
        ]))
        ->add(new StringField([
            'label' => 'Rue',
            'id' => 'street',
            'name' => 'street',
            'maxLength' => 50,
            'size' => 50,
            'validators' => [
                new MaxLengthValidator(
                    'Le nom ne doit pas comporter plus de 50 caractères',
                    50
                ),
            ],
            'formatter' => new AutotrimFormatter,
        ]))
        ->add(new StringField([
            'label' => 'Code postal',
            'id' => 'postcode',
            'name' => 'postcode',
            'maxLength' => 5,
            'size' => 5,
            'validators' => [
                new MatchValidator(
                    Member::POSTCODE_MATCH_TEXT,
                    Member::POSTCODE_MATCH_REGEXP
                ),
            ],
            'formatter' => new AutotrimFormatter,
        ]))
        ->add(new StringField([
            'label' => 'Commune',
            'id' => 'city',
            'name' => 'city',
            'maxLength' => 50,
            'size' => 50,
            'validators' => [
                new MaxLengthValidator(
                    'Le nom ne doit pas comporter plus de 50 caractères',
                    50
                ),
            ],
            'formatter' => new AutotrimFormatter,
        ]))
        ->addScript(new Script([
            'url' => 'https://cdnjs.cloudflare.com/ajax/libs/rxjs/6.5.5/rxjs.umd.js',
            'fileName' => '/../../Web/js/findAddress.js',
            'initFunctionName' => 'findAddressStart',
        ]))
        ->addScript(new Script([
            'fileName' => '/../../Web/js/checkLogin.js',
            'initFunctionName' => 'checkLoginStart',
        ]))
        ;
    }
}
