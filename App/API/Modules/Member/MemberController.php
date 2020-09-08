<?php
namespace App\API\Modules\Member;

use \OCFram\APIController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \Entity\Member;

class MemberController extends APIController
{
    public function needsAuthentication()
    {
        return in_array(
            $this->action,
            [
                'index',
                'list'
            ]
        );
    }

    public function isAdminAccessible()
    {
        return false;
    }

    public function executeCheckLogin(HTTPRequest $request)
    {
        $login = $request->getData('login') ? $request->getData('login') : '';
        $login_valid = (bool) preg_match(Member::LOGIN_MATCH_REGEXP, $login);

        $response = array(
            'request_success' => true,
            'login' => $login,
            'login_valid' => $login_valid
        );

        if ($this->managers->getManagerOf('Members')->existsLogin($login))
        {
            if ($login_valid) $response['login_free'] = false;
        }
        else
        {
            if($login_valid) $response['login_free'] = true;
        }

        echo json_encode($response);
    }

    public function executeIndex(HTTPRequest $request)
    {
        $member = $this->managers->getManagerOf('Members')->getByLogin(
            $this->app->user()->getAttribute('login')
        );

        echo json_encode(
            array(
                'request_success' => true,
                'data' => (array) $member
            )
        );
    }

    public function executeList(HTTPRequest $request)
    {
        $manager = $this->managers->getManagerOf('Members');

        $nombreMembres = $this->app->config()->get('nombre_membres');

        $pagination = new Pagination($this->app, $manager, $nombreMembres);

        $members = $manager->getList($pagination->getOffset(), $nombreMembres);

        $data = array_map(
            function ($member) { return (array) $member; },
            $members
        );

        echo json_encode(
            array(
                'request_success' => true,
                'data' => $data
            )
        );
    }
}
