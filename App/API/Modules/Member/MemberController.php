<?php
namespace App\API\Modules\Member;

use \OCFram\APIController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \Entity\Member;
use \FormBuilder\UpdateMemberFormBuilder;

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

    public function executeMemberPATCH(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();

        if (!$authorization || !$authorization->isMember())
        {
            $this->exitWithError(401, 'user is not member');
        }

        $manager = $this->managers->getManagerOf('Members');

        $storedMember = $manager->getById($authorization->member());

        if (!$storedMember)
        {
            $this->exitWithError(
                404,
                "member does not exist"
            );
        }

        $storedMemberData = $this->dismount($storedMember);

        $storedMemberData['pass'] = "********";
        $storedMemberData['pass2'] = "********";

        unset(
            $storedMemberData['hashPass'],
            $storedMemberData['active'],
            $storedMemberData['token'],
            $storedMemberData['tokenExpiryTime']
        );

        $data = $request->requestBodyData();

        if (isset($data['birthDate']))
        {
            if ($data['birthDate'] == '')
            {
                $data['birthDate'] = null;
            }
            else
            {
                try
                {
                    $data['birthDate'] = new \DateTime($data['birthDate']);
                }
                catch (\Exception $e)
                {
                    $data['birthDate'] = new \DateTime("1800-01");
                }
            }
        }

        $member = new Member(
            array_merge(
                $storedMemberData,
                $data
                )
        );

        $formBuilder = new UpdateMemberFormBuilder($member);
        $formBuilder->build();
        $form = $formBuilder->form();

        if (!$form->isValid())
        {
            $this->exitWithError(400, 'member data incorrect', $form->errors());
        }
        elseif ($member->hasSameContent($storedMember))
        {
            $response = array(
                'message' => 'member data is identical (not updated)',
            );
        }
        elseif (
            $manager->existsLogin($member->login())
            && strtolower($member->login()) !==
                                strtolower($storedMember->login())
            || $manager->existsEmail($member->email())
            && strtolower($member->email()) !==
                                strtolower($storedMember->email())
            )
        {
            $response = array(
                'message' => 'login or email already exists (member not updated)',
            );
        }
        elseif ($manager->save($member))
        {
            $response = $this->dismount(
                $manager->getById($member->id())
            );

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
            $this->exitWithError(500);
        }

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
            catch (\RuntimeException $e)
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
