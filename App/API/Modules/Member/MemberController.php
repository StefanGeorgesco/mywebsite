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

        $this->setJson(
            json_encode(
                $response,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
        );
    }

    public function executeMember(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();

        if ($request->method() == 'GET')
        {
            if (!$authorization)
            {
                $this->app->httpResponse()->jsonError(401);
            }

            if ($authorization->isAdmin())
            {
                $this->app->httpResponse()->jsonError(404);
            }

            $member = $this->managers->getManagerOf('Members')
                ->getById($authorization->memberId());

            $response = $this->dismount($member);

            unset(
                $response['pass'],
                $response['pass2'],
                $response['hashPass'],
                $response['active'],
                $response['token'],
                $response['tokenExpiryTime']
            );
        }
        else
        {
            $this->app->httpResponse()->jsonError(400);
        }

        $this->setJson(
            json_encode(
                $response,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
        );
    }

    public function executeMembers(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();

        if (!$authorization)
        {
            $this->app->httpResponse()->jsonError(401);
        }

        $switchCase = [
            $request->method(),
            $request->getExists('id')
        ];

        switch ($switchCase)
        {
            case ['GET', true]:
                $member = $this->managers->getManagerOf('Members')->getById(
                    $request->getData('id')
                );

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
                }
                else
                {
                    $this->app->httpResponse()->jsonError(404);
                }
                break;

            case ['GET', false]:
                $manager = $this->managers->getManagerOf('Members');

                $nombreMembres = $this->app->config()->get('nombre_membres');

                try
                {
                    $pagination = new Pagination(
                        $this->app,
                        $manager,
                        $nombreMembres
                    );
                }
                catch (\Exception $e)
                {
                    $this->app->httpResponse()->jsonError(404);
                }

                $members = $manager->getList(
                    $pagination->getOffset(),
                    $nombreMembres
                );

                $response = array_map(
                    function ($member)
                    {
                        $array = $this->dismount($member);
                        unset(
                            $array['pass'],
                            $array['pass2'],
                            $array['hashPass'],
                            $array['active'],
                            $array['token'],
                            $array['tokenExpiryTime']
                        );
                        return $array;
                    },
                    $members
                );
                break;

            default:
                $this->app->httpResponse()->jsonError(400);
        }

        $this->setJson(
            json_encode(
                $response,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
        );
    }
}
