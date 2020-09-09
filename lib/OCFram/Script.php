<?php
namespace OCFram;

class Script
{
    use Hydrator;

    protected $url = '';
    protected $fileName = '';
    protected $initFunctionName = '';

    public function __construct(array $data = [])
    {
        if (!empty($data))
        {
            $this->hydrate($data);
        }
    }

    // GETTERS

    public function url()
    {
        return $this->url;
    }

    public function fileName()
    {
        return $this->fileName;
    }

    public function initFunctionName()
    {
        return $this->initFunctionName;
    }

    // SETTERS

    public function setUrl($url)
    {
        if (is_string($url))
        {
            $this->url = $url;
        }
    }

    public function setFileName($fileName)
    {
        if (is_string($fileName))
        {
            $this->fileName = $fileName;
        }
    }

    public function setInitFunctionName($initFunctionName)
    {
        if (is_string($initFunctionName))
        {
            $this->initFunctionName = $initFunctionName;
        }
    }
}
