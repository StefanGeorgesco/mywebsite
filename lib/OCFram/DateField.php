<?php
namespace OCFram;

class DateField extends Field
{
    public function buildWidget()
    {
        $widget = '<label>'.($this->isRequired() ? '*' : '').$this->label.
            '</label><input id="'.$this->id.'" type="date" name="'.
            $this->name.'"';

        $widget .= ' value="'.$this->value.'"';

        $widget .= ' />';

        $widget .= '<span class="customButton" onclick="document.getElementById('.
            "'".$this->id."'".').value='."''".
            '; setTimeout(validateForm, 100);">Effacer</span>';

        $widget .= '<br />';

        $widget .= '<span id="'.$this->id.'-errorMessage">'.
        $this->errorMessage.'</span>';

        return $widget;
    }
}
