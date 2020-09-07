<?php
namespace OCFram;

abstract class Validator
{
    protected $errorMessage;
    protected $scriptValidationOnInput=true;

    public function __construct($errorMessage)
    {
        $this->setErrorMessage($errorMessage);
    }

    abstract public function isValid($value);

    abstract public function ScriptValidationExpression();

    public function setErrorMessage($errorMessage)
    {
        if (is_string($errorMessage))
        {
            $this->errorMessage = $errorMessage;
        }
    }

    public function setScriptValidationOnInput($scriptValidationOnInput)
    {
        if (is_bool($scriptValidationOnInput))
        {
            $this->scriptValidationOnInput = $scriptValidationOnInput;
        }
    }

    public function errorMessage()
    {
        return $this->errorMessage;
    }

    public function scriptValidationOnInput()
    {
        return $this->scriptValidationOnInput;
    }
}
