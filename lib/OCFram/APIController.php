<?php
namespace OCFram;

abstract class APIController extends ApplicationComponent
{
    protected $module = '';
    protected $action = '';
    protected $managers = null;
    protected $json = '';

    public function __construct(Application $app, $module, $action)
    {
        parent::__construct($app);

        $this->managers = new Managers('PDO', PDOFactory::getMysqlConnexion());

        $this->setModule($module);
        $this->setAction($action);
    }

    public function execute()
    {
        $method = 'execute'.ucfirst($this->action);

        if (!is_callable([$this, $method]))
        {
            throw new \RuntimeException(
                'L\'action "'.$this->action.'" n\'est pas définie sur ce module'
            );
        }

        $this->$method($this->app->httpRequest());
    }

    public function json()
    {
        return $this->json;
    }

    public function setModule($module)
    {
        if (!is_string($module) || empty($module))
        {
            throw new \InvalidArgumentException(
                'Le module doit être une chaine de caractères valide'
            );
        }

        $this->module = $module;
    }

    public function setAction($action)
    {
        if (!is_string($action) || empty($action))
        {
            throw new \InvalidArgumentException(
                'L\'action doit être une chaine de caractères valide'
            );
        }

        $this->action = $action;
    }

    public function setJson($json)
    {
        if (!is_string($json) || empty($json))
        {
            throw new \InvalidArgumentException(
                'Le json doit être une chaine de caractères valide'
            );
        }

        $this->json = $json;
    }

    protected function dismount($object) {
        $reflectionClass = new \ReflectionClass(get_class($object));

        $array = array();

        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $array[$property->getName()] = $property->getValue($object);
            $property->setAccessible(false);
        }

        return $array;
    }
}
