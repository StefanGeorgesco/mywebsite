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
        $this->httpResponse->addHeader("Content-type: application/json");
        $this->httpResponse->addHeader("Access-Control-Allow-Origin: *");

        $controller = $this->getController();

        $controller->execute();

        $this->httpResponse->addHeaders($controller->headers());
        $this->httpResponse->setJson($controller->json());
        $this->httpResponse->sendJson();
    }
}
