<?php
namespace OCFram;

class AutotrimFormatter extends Formatter
{
    public function ScriptFormatterExpression()
    {
        return "this.value.trim()";
    }
}
