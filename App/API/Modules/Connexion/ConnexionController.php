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
        $response = array(
            'request_success' => false,
            'message' => 'authentication is required and user is not authenticated',
        );

        echo json_encode($response);
    }
}
