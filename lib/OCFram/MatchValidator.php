<?php
namespace OCFram;

class MatchValidator extends Validator
{
    protected $matchExpression;

    public function __construct($errorMessage, $matchExpression)
    {
        parent::__construct($errorMessage);

        $this->setMatchExpression($matchExpression);
    }

    public function isValid($value)
    {
        return (bool) preg_match($this->matchExpression, $value);
    }

    public function ScriptValidationExpression()
    {
        $matchExpression = substr($this->matchExpression, 1, -1);
        $matchExpression = str_replace("/", "\/", $matchExpression);

        return "/$matchExpression/.test(elem.value)";
    }

    public function setMatchExpression($matchExpression)
    {
        if (is_string($matchExpression))
        {
            $this->matchExpression = $matchExpression;
        }
        else
        {
            throw new \RuntimeException(
                'L\'expression régulière doit être une chaîne de caractères.'
            );
        }
    }
}
