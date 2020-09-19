<?php
namespace OCFram;

abstract class BackController extends Controller
{
    protected $view = '';
    protected $redirect = '.';
    protected $page = null;
    protected $mailer = null;

    public function __construct(Application $app, $module, $action, $redirect='.')
    {
        parent::__construct($app, $module, $action);

        $this->page = new Page($app);
        $this->mailer = new Mailer($app);

        if (file_exists($file = __DIR__.'/../../App/'.$this->app->name().
        '/Modules/'.$this->module.'/EmailTemplates/'.$this->action.'.php'))
        {
            $this->mailer->setContentFile($file);
        }

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

        parent::execute();
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
