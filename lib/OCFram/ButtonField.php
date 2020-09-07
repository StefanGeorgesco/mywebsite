<?php
namespace OCFram;

class ButtonField extends Field
{
    public function buildWidget()
    {
        return '<span id="'.$this->id.'" class="customButton">'.
            $this->label.'</span><br />';
    }
}
