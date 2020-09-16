<?php
namespace App\Back\Modules\Member;

use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \Entity\Member;

class MemberController extends BackController
{
    public function needsAuthentication()
    {
        return false;
    }

    public function isAdminAccessible()
    {
        return true;
    }

    public function executeList(HTTPRequest $request)
    {
        $manager = $this->managers->getManagerOf('Members');

        $nombreMembres = $this->app->config()->get('nombre_membres');

        try
        {
            $pagination = new Pagination(
                $this->app, $manager, $nombreMembres, false
            );
        }
        catch (\Exception $e)
        {
            $this->app->httpResponse()->redirect404();
        }

        $members = $manager->getList(
            $pagination->getOffset(), $nombreMembres, false
        );

        $this->page->addVar('title', 'Membres');
        $this->page->addVar('members', $members);
        $this->page->addVar('pagination', $pagination->createView());
    }

    public function executeDeleteMember(HTTPRequest $request)
    {
        if ($request->method() == 'POST')
        {
            $member = $this->managers->getManagerOf('Members')
            ->getById($request->postData('id'));
            $this->managers->getManagerOf('Members')->delete($member);
            $this->managers->getManagerOf('Comments')
            ->deleteFromMember($member->id());

            $this->app->user()->setAuthenticated(null, false);

            if (isset($_COOKIE['login']))
            {
                setcookie('login', '', time() - 3600);
            }

            if (isset($_COOKIE['hash_pass']))
            {
                setcookie('hash_pass', '', time() - 3600);
            }

            $this->app->user()->setFlash(
                'Le membre et ses commentaires ont bien été supprimés !'
            );

            $this->app->httpResponse()->redirect('/admin/members.html');
        }
        else
        {
            $member = $this->managers->getManagerOf('Members')
            ->getById($request->getData('id'));
        }

        $this->page->addVar('title', 'Suppression d\'un membre');
        $this->page->addVar('member', $member);
    }
}
