<?php
namespace OCFram;

class NormalizeFormatter extends Formatter
{
    protected $matchExpression;
    protected $replaceExpression;

    public function __construct($matchExpression, $replaceExpression)
    {
        $this->setMatchExpression($matchExpression);
        $this->setReplaceExpression($replaceExpression);
    }

    public function ScriptFormatterExpression()
    {
        $matchExpression = substr($this->matchExpression, 1, -1);

        return "/$matchExpression/.test(this.value) ? this.value.replace(/".
            $matchExpression."/, '".$this->replaceExpression."') : this.value";
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

    public function setReplaceExpression($replaceExpression)
    {
        if (is_string($replaceExpression))
        {
            $this->replaceExpression = $replaceExpression;
        }
        else
        {
            throw new \RuntimeException(
                'L\'expression de remplacement doit être une chaîne de caractères.'
            );
        }
    }
}
