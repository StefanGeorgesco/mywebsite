<?php
namespace OCFram;

class TextField extends Field
{
    protected $cols;
    protected $rows;

    public function buildWidget()
    {
        $widget = '<label>'.($this->isRequired() ? '*' : '').$this->label.
            '</label><textarea id="'.$this->id.'" name="'.$this->name.'"';

        if (!empty($this->cols))
        {
            $widget .= ' cols="'.$this->cols.'"';
        }

        if (!empty($this->rows))
        {
            $widget .= ' rows="'.$this->rows.'"';
        }

        if (!empty($this->placeholder))
        {
            $widget .= ' placeholder="'.htmlspecialchars($this->placeholder).'"';
        }

        $widget .= '>';

        if (!empty($this->value))
        {
            $widget .= htmlspecialchars($this->value);
        }

        $widget .= '</textarea>';

        $widget .= '<span id="'.$this->id.'-errorMessage">'.
        $this->errorMessage.'</span>';

        return $widget;
    }

    public function setCols($cols)
    {
        $cols = (int) $cols;

        if ($cols > 0)
        {
            $this->cols = $cols;
        }
        else
        {
            throw new \RuntimeException(
                'Le nombre de colonnes du champ doit être supérieur à 0'
            );
        }
    }

    public function setRows($rows)
    {
        $rows = (int) $rows;

        if ($rows > 0)
        {
            $this->rows = $rows;
        }
        else
        {
            throw new \RuntimeException(
                'Le nombre de lignes du champ doit être supérieur à 0'
            );
        }
    }
}
