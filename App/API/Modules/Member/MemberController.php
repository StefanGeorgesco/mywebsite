<?php
namespace App\API\Modules\Member;

use \OCFram\APIController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \Entity\Member;

class MemberController extends APIController
{
    public function executeCheckLoginGET(HTTPRequest $request)
    {
        $login = $request->getData('login');
        $login_valid = (bool) preg_match(Member::LOGIN_MATCH_REGEXP, $login);

        $response = array(
            'login' => $login,
            'login_valid' => $login_valid
        );

        if ($login_valid)
        {
            $response['login_free'] = !$this->managers
                ->getManagerOf('Members')->existsLogin($login);
        }

        $this->setResponse($response);
    }

    public function executeMemberGET(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();

        if (!$authorization)
        {
            $this->exitWithError(401);
        }

        if ($authorization->isAdmin())
        {
            $this->exitWithError(404, 'user is not a member');
        }

        $membersManager = $this->managers->getManagerOf('Members');
        $commentsManager = $this->managers->getManagerOf('Comments');

        $member = $membersManager->getById($authorization->member());

        $response = $this->dismount($member);

        unset(
            $response['pass'],
            $response['pass2'],
            $response['hashPass'],
            $response['active'],
            $response['token'],
            $response['tokenExpiryTime']
        );

        $commentsArray = array_map(
            function ($comment)
            {
                return $this->dismount($comment);
            },
            $commentsManager->getListOfMember($member['id'])
        );

        $response['comments'] = $commentsArray;

        $this->setResponse($response);
    }

    public function executeMembersGET(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();

        if (!$authorization)
        {
            $this->exitWithError(401);
        }

        $membersManager = $this->managers->getManagerOf('Members');
        $commentsManager = $this->managers->getManagerOf('Comments');

        if ($request->getExists('id'))
        {
            $member = $membersManager->getById($request->getData('id'));

            if ($member)
            {
                $response = $this->dismount($member);
                unset(
                    $response['pass'],
                    $response['pass2'],
                    $response['hashPass'],
                    $response['active'],
                    $response['token'],
                    $response['tokenExpiryTime']
                );

                $commentsArray = array_map(
                    function ($comment)
                    {
                        return $this->dismount($comment);
                    },
                    $commentsManager->getListOfMember($member['id'])
                );

                $response['comments'] = $commentsArray;
            }
            else
            {
                $this->exitWithError(404, 'this member does not exist');
            }
        }
        else
        {
            $nombreMembres = $this->app->config()->get('nombre_membres');

            try
            {
                $pagination = new Pagination(
                    $this->app,
                    $membersManager,
                    $nombreMembres
                );
            }
            catch (\Exception $e)
            {
                $this->exitWithError(404, 'this page does not exist');
            }

            $members = $membersManager->getList(
                $pagination->getOffset(),
                $nombreMembres
            );

            $response = array_map(
                function ($member) use ($commentsManager)
                {
                    $memberArray = $this->dismount($member);

                    unset(
                        $memberArray['pass'],
                        $memberArray['pass2'],
                        $memberArray['hashPass'],
                        $memberArray['active'],
                        $memberArray['token'],
                        $memberArray['tokenExpiryTime']
                    );

                    $commentsArray = array_map(
                        function ($comment)
                        {
                            return $this->dismount($comment);
                        },
                        $commentsManager->getListOfMember($member['id'])
                    );

                    $memberArray['comments'] = $commentsArray;

                    return $memberArray;
                },
                $members
            );
        }

        $this->setResponse($response);
    }
}
