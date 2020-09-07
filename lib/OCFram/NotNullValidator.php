<?php
namespace OCFram;

class NotNullValidator extends Validator
{
    public function isValid($value)
    {
        return trim($value) != '';
    }

    public function ScriptValidationExpression()
    {
        return "elem.value.trim() !== ''";
    }
}
