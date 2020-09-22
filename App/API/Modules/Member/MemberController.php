<?php
namespace App\API\Modules\Member;

use \OCFram\APIController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \Entity\Member;

class MemberController extends APIController
{
    public function executeCheckLogin(HTTPRequest $request)
    {
        if ($request->method() == 'GET')
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
        }
        else
        {
            $this->app->httpResponse()->jsonError(400);
        }

        $this->setResponse($response);
    }

    public function executeMember(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();

        if (!$authorization)
        {
            $this->app->httpResponse()->jsonError(401);
        }

        if ($authorization->isAdmin())
        {
            $this->app->httpResponse()->jsonError(404);
        }

        $membersManager = $this->managers->getManagerOf('Members');
        $commentsManager = $this->managers->getManagerOf('Comments');

        if ($request->method() == 'GET')
        {
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
        }
        else
        {
            $this->app->httpResponse()->jsonError(400);
        }

        $this->setResponse($response);
    }

    public function executeMembers(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();

        if (!$authorization)
        {
            $this->app->httpResponse()->jsonError(401);
        }

        $membersManager = $this->managers->getManagerOf('Members');
        $commentsManager = $this->managers->getManagerOf('Comments');

        $switchCase = [
            $request->method(),
            $request->getExists('id')
        ];

        switch ($switchCase)
        {
            case ['GET', true]:
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
                    $this->app->httpResponse()->jsonError(404);
                }
                break;

            case ['GET', false]:
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
                    $this->app->httpResponse()->jsonError(404);
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
                break;

            default:
                $this->app->httpResponse()->jsonError(400);
        }

        $this->setResponse($response);
    }
}
