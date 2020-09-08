<?php
namespace App\API;

use \OCFram\Application;

class APIApplication extends Application
{
    public function __construct()
    {
        parent::__construct();

        $this->name = 'API';
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
                $this, 'Connexion', 'index');
        }
        
        header("Content-type: application/json;\n");

        $controller->execute();
    }
}
