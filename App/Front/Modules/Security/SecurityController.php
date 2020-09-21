<?php
namespace App\Front\Modules\Member;

use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \OCFram\FormHandler;
use \OCFram\User;
use \FormBuilder\NNNFormBuilder; ///

class MemberController extends BackController
{
    public function needsAuthentication()
    {
        return true;
    }

    public function isAdminAccessible()
    {
        return false;
    }

    public function executeIndex(HTTPRequest $request)
    {
        $manager = $this->managers->getManagerOf('Members');

        $nombreMembres = $this->app->config()->get('nombre_membres');

        try
        {
            $pagination = new Pagination($this->app, $manager, $nombreMembres);
        }
        catch (\Exception $e)
        {
            $this->app->httpResponse()->redirect404();
        }


        $members = $manager->getList($pagination->getOffset(), $nombreMembres);

        $this->page->addVar('title', 'Membres');
        $this->page->addVar('members', $members);
        $this->page->addVar('pagination', $pagination->createView());
    }

    public function executeCreate(HTTPRequest $request)
    {
        if ($request->method() == 'POST' && $request->postExists('submit'))
        {
            $member = new Member([
                'login' => $request->postData('login'),
                'pass' => $request->postData('pass'),
                'pass2' => $request->postData('pass2'),
                'email' => $request->postData('email'),
            ]);
        }
        else
        {
            $member = new Member;
        }

        $formBuilder = new NewMemberFormBuilder($member);
        $formBuilder->build();

        $form = $formBuilder->form();

        $formHandler = new FormHandler(
            $form,
            $this->managers->getManagerOf('Members'),
            $request
        );

        if ($formHandler->process())
        {
            $this->app->user()->setFlash(
                "Vous êtes maintenant inscrit, merci !
                Un e-mail d'activation de votre compte a été envoyé
                à l'adresse que vous avez indiquée."
            );

            $this->mailer->addVar('title', 'Activation de votre compte');
            $this->mailer->addVar('login', $member->login());
            $this->mailer->addVar(
                'url',
                $this->app->baseUrl().'member-activate-'.$member->token().'.html'
            );
            $this->mailer->setTo($request->postData('email'));
            $this->mailer->setSubject('Activation de votre compte');
            $this->mailer->send();

            $this->app->httpResponse()->redirect('.');
        }
        else
        {
            if (
                $request->method() == 'POST'
                && $request->postExists('submit')
                && $form->isValid()
                )
            {
                $this->app->user()->setFlash(
                    'Le pseudo ou l\'e-mail est déjà pris.',
                    User::FLASH_ERROR
                );
            }
        }

        $this->page->addVar('title', 'S\'inscrire');
        $this->page->addVar('form', $form->createView());
        $this->page->addVar('initial_login', '');
    }

    public function executeUpdate(HTTPRequest $request)
    {
        $manager = $this->managers->getManagerOf('Members');

        $storedMember = $manager->getByLogin(
            $this->app->user()->getAttribute('login')
        );

        if ($request->method() == 'POST' && $request->postExists('submit'))
        {
            $member = new Member([
                'id' => $storedMember->id(),
                'login' => $request->postData('login'),
                'pass' => '********',
                'pass2' => '********',
                'email' => $request->postData('email'),
                'firstName' => trim($request->postData('firstName')),
                'lastName' => trim($request->postData('lastName')),
                'birthDate' => $request->postData('birthDate') ?
                    new \DateTime($request->postData('birthDate')) :
                    null,
                'phone' => trim($request->postData('phone')),
                'website' => trim($request->postData('website')),
                'housenumber' => trim($request->postData('housenumber')),
                'street' => trim($request->postData('street')),
                'postcode' => trim($request->postData('postcode')),
                'city' => trim($request->postData('city')),
            ]);
        }
        else
        {
            $member = $storedMember;
        }

        $formBuilder = new UpdateMemberFormBuilder($member);
        $formBuilder->build();

        $form = $formBuilder->form();

        $formHandler = new FormHandler(
            $form,
            $manager,
            $request
        );

        if ($formHandler->process())
        {
            $this->app->user()->setFlash('Votre profil a bien été modifié');
            $this->app->user()->setAttribute('login', $member->login());
            if (isset($_COOKIE['login']))
            {
                setcookie(
                    'login',
                    $member->login(),
                    time() + ConnexionController::AUTOSIGNIN_VALIDITY_TIME,
                    null, null, false, true
                );
            }
            $this->app->httpResponse()->redirect('/profile.html');
        }
        else
        {
            if (
                $request->method() == 'POST'
                && $request->postExists('submit')
                && $form->isValid()
                )
            {
                if (
                    $manager->existsLogin($member->login())
                    && strtolower($member->login()) !==
                                        strtolower($storedMember->login())
                    || $manager->existsEmail($member->email())
                    && strtolower($member->email()) !==
                                        strtolower($storedMember->email())
                    )
                {
                    $this->app->user()->setFlash(
                        'Le pseudo ou l\'e-mail est déjà pris.',
                        User::FLASH_ERROR
                    );
                }
                else
                {
                    $this->app->user()->setFlash(
                        'Votre profil n\'a pas été modifié...',
                        User::FLASH_ERROR
                    );
                }
            }
        }

        $this->page->addVar('title', 'Modifier mon profil');
        $this->page->addVar('form', $form->createView());
        $this->page->addVar('initial_login', $storedMember->login());
    }

    public function executeDelete(HTTPRequest $request)
    {
        if ($request->method() == 'POST')
        {
            $member = $this->managers->getManagerOf('Members')
            ->getByLogin($this->app->user()->getAttribute('login'));

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
                'Votre profil et vos commentaires ont bien été supprimés !'
            );

            $this->app->httpResponse()->redirect('/');
        }

        $this->page->addVar('title', 'Supprimer mon profil');
    }
}
