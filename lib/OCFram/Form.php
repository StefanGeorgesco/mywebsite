<?php
namespace OCFram;

class Form
{
    protected $entity;
    protected $fields = [];
    protected $scripts = [];

    public function __construct(Entity $entity)
    {
        $this->setEntity($entity);
    }

    public function add(Field $field)
    {
        $attr = $field->name();

        if (is_callable([$this->entity, $attr]))
        {
            $value = $this->entity->$attr();

            if ($value instanceof \DateTime)
            {
                $value = $value->format('Y-m-d');
            }

            $field->setValue($value);
        }

        $this->fields[] = $field;

        return $this;
    }

    public function addScript(Script $script)
    {
        $this->scripts[] = $script;

        return $this;
    }

    public function createView()
    {
        $view = $this->createScript();

        foreach ($this->fields as $field)
        {
            $view .= $field->buildWidget().'<br />';
        }

        $view .= '<br />';

        return $view;
    }

    protected function createScript()
    {
        $script = '';

        foreach ($this->scripts as $s)
        {
            if ($s->url())
            {
                $script .= '<script
    src="'.$s->url().'">
</script>
';
            }
        }

        $script .= '<script>
';

        if (file_exists($filename = __DIR__.'/../../Web/js/utils.js'))
        {
            $script .= file_get_contents($filename);
        }

        foreach ($this->scripts as $s)
        {
            if ($s->fileName() &&
                file_exists($filename = __DIR__.$s->fileName()))
            {
                $script .= file_get_contents($filename);
            }
        }

        foreach ($this->fields as $field)
        {
            $script .= $field->fieldFinalValidationScriptFunctionDefinition();
            $script .= $field->fieldValidationScriptFunctionDefinition();
            $script .= $field->formatterScriptFunctionDefinition();
        }

        $script .= 'function validateForm() {
    let ret = true;
    ';
        foreach ($this->fields as $field)
        {
            $functionName = $field->fieldFinalValidationScriptFunctionName();

            if ($functionName)
            {
                $script .= 'if (!'.$functionName.'())
    {
        ret = false;
    }
    ';
            }
        }

        $script .= 'return ret;
}
';
        $script .= 'window.onload = function () {
    ';

        foreach ($this->scripts as $s)
        {
            if ($s->initFunctionName())
            {
                $script .= $s->initFunctionName().'();
    ';
            }
        }

        foreach ($this->fields as $field)
        {
            $functionName = $field->fieldValidationScriptFunctionName();

            if ($functionName)
            {
                $script .= $field->fieldValidationScriptFunctionName().'();
    ';
            }
        }

        foreach ($this->fields as $field)
        {
            $functionName = $field->formatterScriptFunctionName();

            if ($functionName)
            {
                $script .= $field->formatterScriptFunctionName().'();
    ';
            }
        }

        foreach ($this->fields as $field)
        {
            $script .= "document.getElementById('".$field->id().
                "').addEventListener('focus', ev => ev.target.select());
    ";
        }

        $script .= "let focusElements = Array.from(
      document.getElementsByTagName('form')[0].children
    ).filter(
      function(child) {
        return child.tagName.toLowerCase() === 'textarea' ||
          child.tagName.toLowerCase() === 'input' && [
          'text',
          'date',
          'email',
          'password',
          'tel',
          'url'
        ].includes(child.getAttribute('type').toLowerCase());
      }
    );

    if (focusElements.length > 0) {
      focusElements[0].focus();
    }
}";

        $script .= '
        </script>
        ';

        return $script;
    }

    public function isValid()
    {
        $valid = true;

        foreach ($this->fields as $field)
        {
            if (!$field->isValid())
            {
                $valid = false;
            }
        }

        return $valid;
    }

    public function entity()
    {
        return $this->entity;
    }

    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;
    }
}
