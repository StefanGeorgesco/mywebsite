<?php
namespace OCFram;

abstract class BackController extends ApplicationComponent
{
    protected $module = '';
    protected $action = '';
    protected $view = '';
    protected $redirect = '.';
    protected $managers = null;
    protected $page = null;
    protected $mailer = null;

    public function __construct(Application $app, $module, $action, $redirect='.')
    {
        parent::__construct($app);

        $this->managers = new Managers('PDO', PDOFactory::getMysqlConnexion());
        $this->page = new Page($app);
        $this->mailer = new Mailer($app);

        $this->setModule($module);
        $this->setAction($action);
        $this->setView($action);
        $this->setRedirect($redirect);
    }

    abstract public function needsAuthentication();

    abstract public function isAdminAccessible();

    public function execute()
    {
        $this->page->addVar(
            'numberOfMembers',
            $this->managers->getManagerOf('Members')->count()
        );

        $method = 'execute'.ucfirst($this->action);

        if (!is_callable([$this, $method]))
        {
            throw new \RuntimeException(
                'L\'action "'.$this->action.'" n\'est pas définie sur ce module'
            );
        }

        $this->$method($this->app->httpRequest());
    }

    public function redirect()
    {
        return $this->redirect;
    }

    public function page()
    {
        return $this->page;
    }

    public function mailer()
    {
        return $this->mailer;
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

        if (file_exists($file = __DIR__.'/../../App/'.$this->app->name().
        '/Modules/'.$this->module.'/EmailTemplates/'.$this->action.'.php'))
        {
            $this->mailer->setContentFile($file);
        }
    }

    public function setView($view)
    {
        if (!is_string($view) || empty($view))
        {
            throw new \InvalidArgumentException(
                'La vue doit être une chaine de caractères valide'
            );
        }

        $this->view = $view;

        $this->page->setContentFile(
            __DIR__.'/../../App/'.$this->app->name().'/Modules/'.$this->module.
            '/Views/'.$this->view.'.php'
        );
    }

    public function setRedirect($redirect)
    {
        if (!is_string($redirect))
        {
            throw new \InvalidArgumentException(
                'La redirection doit être une chaine de caractères'
            );
        }

        $this->redirect = $redirect;
    }
}
