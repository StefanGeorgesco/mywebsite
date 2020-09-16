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
                'list',
                'member'
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

        $this->setJson(json_encode($response));
    }

    public function executeIndex(HTTPRequest $request)
    {
        $member = $this->managers->getManagerOf('Members')->getByLogin(
            $this->app->user()->getAttribute('login')
        );

        $response = json_encode(
            array(
                'request_success' => true,
                'data' => (array) $member
            )
        );

        $this->setJson(json_encode($response));
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

        $response = json_encode(
            array(
                'request_success' => true,
                'data' => $data
            )
        );

        $this->setJson(json_encode($response));
    }

    public function executeMember(HTTPRequest $request)
    {
        $member = $this->managers->getManagerOf('Members')->getById(
            $request->getData('id')
        );

        if ($member)
        {
            $response = array(
                'request_success' => true,
                'data' => (array) $member
            );
        }
        else
        {
            $this->app->httpResponse()->addHeader("HTTP/1.1 404 Not Found");

            $response = array(
                'request_success' => false,
                'message' => 'this member does not exist',
            );
        }

        $this->setJson(json_encode($response));
    }
}
