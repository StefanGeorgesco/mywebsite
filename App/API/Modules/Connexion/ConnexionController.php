<?php
namespace App\API\Modules\Connexion;

use \OCFram\APIController;
use \OCFram\HTTPRequest;
use \Entity\Member;

class ConnexionController extends APIController
{
    public function needsAuthentication()
    {
        return false;
    }

    public function isAdminAccessible()
    {
        return true;
    }

    public function executeIndex(HTTPRequest $request)
    {
        $this->app->httpResponse()->addHeader("HTTP/1.1 401 Not Unauthorized");

        $response = array(
            'message' => 'authentication is required and user is not authenticated',
        );

        $this->setJson(json_encode($response));
    }
}
