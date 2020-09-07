<?php
namespace App\Front;

use \OCFram\Application;

class FrontApplication extends Application
{
    public function __construct()
    {
        parent::__construct();

        $this->name = 'Front';
    }

    public function run()
    {
        $controller = $this->getController();

        if (
            $controller->needsAuthentication()
            && !$this->user->isAuthenticated()
            && (!$controller->isAdminAccessible() || !$this->user->isAdmin())
        )
        {
            $controller = new Modules\Connexion\ConnexionController(
                $this, 'Connexion', 'index', $this->httpRequest->requestURI());
        }

        if (!$this->user->isAuthenticated()
            && isset($_COOKIE['login'])
            && isset($_COOKIE['hash_pass']))
        {
            $controller = new Modules\Connexion\ConnexionController(
                $this, 'Connexion', 'autoSignIn', $this->httpRequest->requestURI());
        }

        $controller->execute();

        $this->httpResponse->setPage($controller->page());
        $this->httpResponse->send();
    }
}
