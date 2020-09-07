<?php
namespace OCFram;

abstract class Field
{
    use Hydrator;

    protected $errorMessage;
    protected $label;
    protected $id;
    protected $name;
    protected $value;
    protected $placeholder;
    protected $validators = [];
    protected $scriptValidationOnly=false;
    protected $formatter = null;

    public function __construct(array $data = [])
    {
        if (!empty($data))
        {
            $this->hydrate($data);
        }
    }

    public function isValid()
    {
        if ($this->scriptValidationOnly)
        {
            return true;
        }

        foreach ($this->validators as $validator)
        {
            if (!$validator->isValid($this->value))
            {
                $this->errorMessage = $validator->errorMessage();
                return false;
            }
        }

        return true;
    }

    public function isRequired()
    {
        foreach ($this->validators as $validator)
        {
            if (!$validator->isValid(''))
            {
                return true;
            }
        }

        return false;
    }

    abstract public function buildWidget();

    // Script generation

    public function fieldFinalValidationScriptFunctionName()
    {
        if (empty($this->validators))
        {
            return '';
        }

        return 'finalValidate'.ucfirst($this->name);
    }

    public function fieldFinalValidationScriptFunctionDefinition()
    {
        if (empty($this->validators))
        {
            return '';
        }

        $functionDef = "function ".
            $this->fieldFinalValidationScriptFunctionName()."() {
    let elem = document.getElementById('".$this->id."');
    ";

            foreach ($this->validators as $validator)
            {
                $functionDef .= 'if (!('
                    .$validator->ScriptValidationExpression().'))
    {
        elem.style.outline ="1px solid red";
        document.getElementById("'.$this->id.
        '-errorMessage").textContent = "'.
        $validator->errorMessage().'";
        return false;
    }
    ';
            }

            $functionDef .= "elem.style.outline ='none';
        document.getElementById('".$this->id."-errorMessage').textContent = '';
        ";

        $functionDef .= "return true;
}
";

        return $functionDef;
    }

    public function fieldValidationScriptFunctionName()
    {
        if (empty($this->validators))
        {
            return '';
        }

        return 'validate'.ucfirst($this->name);
    }

    public function fieldValidationScriptFunctionDefinition()
    {
        if (empty($this->validators))
        {
            return '';
        }

        $functionDef = "function ".$this->fieldValidationScriptFunctionName().
            "() {
    document.getElementById('".$this->id.
        "').addEventListener('input', function () {
        let elem = this;
        ";

            foreach ($this->validators as $validator)
            {
                if ($validator->scriptValidationOnInput())
                {
                    $functionDef .= 'if (!('
                        .$validator->ScriptValidationExpression().'))
        {
            elem.style.outline ="1px solid red";
            document.getElementById("'.$this->id.
            '-errorMessage").textContent = "'.
            $validator->errorMessage().'";
            return;
        }
        ';
                }
            }

        $functionDef .= "elem.style.outline ='none';
        document.getElementById('".$this->id."-errorMessage').textContent = '';
    });
}
";

        return $functionDef;
    }

    public function formatterScriptFunctionName()
    {
        if (!$this->formatter)
        {
            return '';
        }

        return 'format'.ucfirst($this->name);
    }

    public function formatterScriptFunctionDefinition()
    {
        if (!$this->formatter)
        {
            return '';
        }

        $functionDef = "function ".$this->formatterScriptFunctionName()."() {
    document.getElementById('".$this->id.
        "').addEventListener('change', function () {
        this.value = ".$this->formatter->ScriptFormatterExpression().";
    });
}
";

        return $functionDef;
    }

    // GETTERS

    public function label()
    {
        return $this->label;
    }

    public function id()
    {
        return $this->id;
    }

    public function name()
    {
        return $this->name;
    }

    public function value()
    {
        return $this->value;
    }

    public function placeholder()
    {
        return $this->placeholder;
    }

    public function validators()
    {
        return $this->validators;
    }

    public function scriptValidationOnly()
    {
        return $this->scriptValidationOnly;
    }

    public function formatter()
    {
        return $this->formatter ;
    }

    // SETTERS

    public function setLabel($label)
    {
        if (is_string($label))
        {
            $this->label = $label;
        }
    }

    public function setId($id)
    {
        if (is_string($id))
        {
            $this->id = $id;
        }
    }

    public function setName($name)
    {
        if (is_string($name))
        {
            $this->name = $name;
        }
    }

    public function setValue($value)
    {
        if (is_string($value))
        {
            $this->value = $value;
        }
    }

    public function setPlaceholder($placeholder)
    {
        if (is_string($placeholder))
        {
            $this->placeholder = $placeholder;
        }
    }

    public function setValidators(array $validators)
    {
        foreach ($validators as $validator)
        {
            if ($validator instanceof Validator
            && !in_array($validator, $this->validators))
            {
                $this->validators[] = $validator;
            }
        }
    }

    public function setScriptValidationOnly($scriptValidationOnly)
    {
        if (is_bool($scriptValidationOnly))
        {
            $this->scriptValidationOnly = $scriptValidationOnly;
        }
    }

    public function setFormatter($formatter)
    {
        if ($formatter instanceof Formatter)
        {
            $this->formatter = $formatter;
        }
    }
}
