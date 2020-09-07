<?php
namespace OCFram;

class StringField extends Field
{
    protected $maxLength;
    protected $size;

    public function buildWidget()
    {
        $widget = '<label>'.($this->isRequired() ? '*' : '').$this->label.
            '</label><input id="'.$this->id.'" type="text" name="'.
            $this->name.'"';

        if (!empty($this->value))
        {
            $widget .= ' value="'.htmlspecialchars($this->value).'"';
        }

        if (!empty($this->placeholder))
        {
            $widget .= ' placeholder="'.htmlspecialchars($this->placeholder).'"';
        }

        if (!empty($this->maxLength))
        {
            $widget .= ' maxlength="'.$this->maxLength.'"';
        }

        if (!empty($this->size))
        {
            $widget .= ' size="'.$this->size.'"';
        }

        $widget .= ' /><br />';

        $widget .= '<span id="'.$this->id.'-errorMessage">'.
        $this->errorMessage.'</span>';

        return $widget;
    }

    public function setMaxLength($maxLength)
    {
        $maxLength = (int) $maxLength;

        if ($maxLength > 0)
        {
            $this->maxLength = $maxLength;
        }
        else
        {
            throw new \RuntimeException(
                'La longueur maximale du champ doit être supérieure à 0'
            );
        }
    }

    public function setSize($size)
    {
        $size = (int) $size;

        if ($size > 0)
        {
            $this->size = $size;
        }
        else
        {
            throw new \RuntimeException(
                'La taille du champ doit être supérieure à 0'
            );
        }
    }
}
