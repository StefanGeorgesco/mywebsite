<?php
namespace App\Front\Modules\Connexion;

use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \OCFram\User;
use \Entity\Member;
use \FormBuilder\ConnexionFormBuilder;

class ConnexionController extends BackController
{
    const AUTOSIGNIN_VALIDITY_TIME = 30 * 24 * 60 * 60;

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
        if ($request->method() == 'POST')
        {
            $member = new Member([
                'login' => $request->postData('login'),
                'pass' => $request->postData('pass')
            ]);
        }
        else
        {
            $member = new Member;
        }

        $formBuilder = new ConnexionFormBuilder($member);
        $formBuilder->build();

        $form = $formBuilder->form();

        if ($request->method() == 'POST' && $form->isValid())
        {
            $login = $member->login();
            $storedMember = $this->managers->getManagerOf('Members')
            ->getByLogin($login);

            if (
                $storedMember
                && password_verify($member->pass(), $storedMember->hashPass())
                && $storedMember->active())
            {
                $this->app->user()->setFlash(
                    'Vous êtes bien connecté(e) !'
                );
                $this->app->user()->setAuthenticated($login);

                if ($request->postData('keepConnection'))
                {
                    setcookie(
                        'login',
                        $login,
                        time() + self::AUTOSIGNIN_VALIDITY_TIME,
                        null, null, false, true
                    );
                    setcookie(
                        'hash_pass',
                        $storedMember->hashPass(),
                        time() + self::AUTOSIGNIN_VALIDITY_TIME,
                        null, null, false, true);
                }

                $this->app->httpResponse()->redirect($this->redirect());
            }
            else
            {
                $this->app->user()->setFlash(
                    'Le pseudo ou le mot de passe est incorrect, ou l\'utilisateur n\'est pas actif.',
                    User::FLASH_ERROR
                );
            }
        }

        $this->page->addVar('title', 'Connexion');
        $this->page->addVar('form', $form->createView());
    }

    public function executeAutoSignIn(HTTPRequest $request)
    {
        $login = $_COOKIE['login'];
        $member = $this->managers->getManagerOf('Members')
        ->getByLogin($login);

        if ($member
            && $_COOKIE['hash_pass'] == $member->hashPass()
            && $member->active()
            )
        {
            $this->app->user()->setAuthenticated($login);
        }
        else
        {
            setcookie('login', '', time() - 3600);
            setcookie('hash_pass', '', time() - 3600);
        }

        $this->app->httpResponse()->redirect($this->redirect());
    }

    public function executeSignOut(HTTPRequest $request)
    {
        $this->app->user()->setAuthenticated(null, false);

        if (isset($_COOKIE['login']))
        {
            setcookie('login', '', time() - 3600);
        }

        if (isset($_COOKIE['hash_pass']))
        {
            setcookie('hash_pass', '', time() - 3600);
        }

        $this->app->httpResponse()->redirect('/');
    }
}
