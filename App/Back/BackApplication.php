<?php
namespace App\Back;

use \OCFram\Application;

class BackApplication extends Application
{
    public function __construct()
    {
        parent::__construct();

        $this->name = 'Back';
    }

    public function run()
    {
        if ($this->user->isAdmin())
        {
            $controller = $this->getController();
        }
        else
        {
            $controller = new Modules\Connexion\ConnexionController(
                $this, 'Connexion', 'index');
        }

        $controller->execute();

        $this->httpResponse->setPage($controller->page());
        $this->httpResponse->send();
    }
}
