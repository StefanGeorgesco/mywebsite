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
        $this->httpResponse->addHeader("Content-type: application/json;\n");
        $this->httpResponse->addHeader("Access-Control-Allow-Origin: *");

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

        $controller->execute();

        $this->httpResponse->setJson($controller->json());
        $this->httpResponse->sendJson();
    }
}
