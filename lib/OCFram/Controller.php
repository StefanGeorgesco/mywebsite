<?php
namespace OCFram;

abstract class Controller extends ApplicationComponent
{
    protected $module = '';
    protected $action = '';
    protected $managers = null;

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

        if ($this->app->name() == 'API')
        {
            $method .= $this->app->httpRequest()->method();
        }

        if (!is_callable([$this, $method]))
        {
            throw new \RuntimeException(
                'L\'action "'.$this->action.'" n\'est pas définie sur ce module'
            );
        }

        $this->$method($this->app->httpRequest());
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
}
