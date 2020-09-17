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
        $login = $request->getData('login');
        $login_valid = (bool) preg_match(Member::LOGIN_MATCH_REGEXP, $login);

        $response = array(
            'login' => $login,
            'login_valid' => $login_valid
        );

        if ($login_valid)
        {
            $response['login_free'] = !$this->managers->getManagerOf('Members')
                ->existsLogin($login);
        }

        $this->setJson(json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    public function executeIndex(HTTPRequest $request)
    {
        $member = $this->managers->getManagerOf('Members')->getByLogin(
            $this->app->user()->getAttribute('login')
        );

        $member->setHashPass('');

        $response = $this->dismount($member);

        $this->setJson(json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    public function executeList(HTTPRequest $request)
    {
        $manager = $this->managers->getManagerOf('Members');

        $nombreMembres = $this->app->config()->get('nombre_membres');


        try
        {
            $pagination = new Pagination($this->app, $manager, $nombreMembres);
        }
        catch (\Exception $e)
        {
            $this->app->httpResponse()->jsonError400();
        }

        $members = $manager->getList($pagination->getOffset(), $nombreMembres);

        $response = array_map(
            function ($member)
            {
                $member->setHashPass('');
                return $this->dismount($member);
            },
            $members
        );

        $this->setJson(json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    public function executeMember(HTTPRequest $request)
    {
        $member = $this->managers->getManagerOf('Members')->getById(
            $request->getData('id')
        );

        if ($member)
        {
            $response = $this->dismount($member);
        }
        else
        {
            $this->app->httpResponse()->addHeader("HTTP/1.1 404 Not Found");

            $response = array(
                'message' => 'this member does not exist'
            );
        }

        $this->setJson(json_encode($response, JSON_UNESCAPED_UNICODE));
    }
}
