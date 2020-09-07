<?php
namespace OCFram;

use \OCFram\Field;

class EqualValueValidator extends Validator
{
    protected $otherField;

    public function __construct(
        $errorMessage, Field $otherField, bool $scriptValidationOnInput
        )
    {
        parent::__construct($errorMessage);

        $this->setOtherField($otherField);
        $this->setScriptValidationOnInput($scriptValidationOnInput);
    }

    public function isValid($value)
    {
        return $value == $this->otherField->value();
    }

    public function ScriptValidationExpression()
    {
        $otherFieldId = $this->otherField->id();

        return "elem.value === document.getElementById('$otherFieldId').value";
    }

    public function setOtherField($otherField)
    {
        if ($otherField instanceof Field)
        {
            $this->otherField = $otherField;
        }
        else
        {
            throw new \RuntimeException(
                'Le paramètre doit être un objet de la classe Field.'
            );
        }
    }
}
