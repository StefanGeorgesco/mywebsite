<?php
namespace App\Front\Modules\Member;

use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \OCFram\FormHandler;
use \OCFram\User;
use \Entity\Member;
use \FormBuilder\NewMemberFormBuilder;
use \FormBuilder\SendLinkFormBuilder;
use \FormBuilder\UpdateMemberFormBuilder;
use \FormBuilder\ChangePasswordFormBuilder;
use \FormBuilder\RenewPasswordFormBuilder;
use App\Front\Modules\Connexion\ConnexionController;

class MemberController extends BackController
{
    public function needsAuthentication()
    {
        return in_array(
            $this->action,
            [
                'index',
                'list',
                'updateMember',
                'changePassword',
                'deleteMember',
            ]
        );
    }

    public function isAdminAccessible()
    {
        return false;
    }

    public function executeSignUp(HTTPRequest $request)
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
                $this->app->config()->get('host_domain').
                'member-activate-'.$member->token().'.html'
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
    }

    public function executeRenewActivationLink(HTTPRequest $request)
    {
        if ($request->method() == 'POST')
        {
            $member = new Member([
                'email' => $request->postData('email')
            ]);
        }
        else
        {
            $member = new Member;
        }

        $formBuilder = new SendLinkFormBuilder($member);
        $formBuilder->build();

        $form = $formBuilder->form();

        if ($request->method() == 'POST' && $form->isValid())
        {
            $this->app->user()->setFlash(
                "Un nouveau lien d'activation de votre compte a été envoyé
                à l'adresse que vous avez indiquée si elle est associée à un
                compte existant qui n'est pas déjà activé."
            );

            $storedMember = $this->managers->getManagerOf('Members')
            ->getByEmail($member->email());

            if ($storedMember && !$storedMember->active())
            {
                $token = $this->managers->getManagerOf('Members')
                ->generateToken($storedMember);

                $this->mailer->addVar('title', 'Activation de votre compte');
                $this->mailer->addVar('login', $storedMember->login());
                $this->mailer->addVar(
                    'url',
                    $this->app->config()->get('host_domain').
                    'member-activate-'.$token.'.html'
                );
                $this->mailer->setTo($request->postData('email'));
                $this->mailer->setSubject('Activation de votre compte');
                $this->mailer->send();
            }

            $this->app->httpResponse()->redirect('.');
        }

        $this->page->addVar('title', 'Renvoyer un lien d\'activation');
        $this->page->addVar('form', $form->createView());
    }

    public function executeActivate(HTTPRequest $request)
    {
        $member = $this->managers->getManagerOf('Members')
        ->getByToken($request->getData('token'));

        if ($member && !$member->active() && !$member->tokenExpired())
        {
            if ($this->managers->getManagerOf('Members')->activate($member))
            {
                $this->app->user()->setFlash(
                    'Votre compte a bien été activé. Vous pouvez vous connecter.'
                );
            }
            else
            {
                $this->app->user()->setFlash(
                    'Une erreur s\'est produite',
                    User::FLASH_ERROR
                );
            }
        }
        else
        {
            $this->app->user()->setFlash(
                'Le lien utilisé est invalide ou a expiré, ou l\'utilisateur est déjà activé',
                User::FLASH_ERROR
            );
        }

        $this->app->httpResponse()->redirect('.');
    }

    public function executeGenerateRenewPasswordLink(HTTPRequest $request)
    {
        if ($request->method() == 'POST')
        {
            $member = new Member([
                'email' => $request->postData('email')
            ]);
        }
        else
        {
            $member = new Member;
        }

        $formBuilder = new SendLinkFormBuilder($member);
        $formBuilder->build();

        $form = $formBuilder->form();

        if ($request->method() == 'POST' && $form->isValid())
        {
            $this->app->user()->setFlash(
                "Un lien de changement de mot de passe a été envoyé à
                l'adresse que vous avez indiquée si elle est associée à
                un compte existant et activé."
            );

            $storedMember = $this->managers->getManagerOf('Members')
            ->getByEmail($member->email());

            if ($storedMember && $storedMember->active())
            {
                $token = $this->managers->getManagerOf('Members')
                ->generateToken($storedMember);

                $this->mailer->addVar('title', 'Oubli du mot de passe');
                $this->mailer->addVar('login', $storedMember->login());
                $this->mailer->addVar(
                    'url',
                    $this->app->config()->get('host_domain').
                    'password-renew-'.$token.'.html'
                );
                $this->mailer->setTo($request->postData('email'));
                $this->mailer->setSubject('Oubli du mot de passe');
                $this->mailer->send();
            }

            $this->app->httpResponse()->redirect('.');
        }

        $this->page->addVar('title', 'Oubli du mot de passe');
        $this->page->addVar('form', $form->createView());
    }

    public function executeRenewPassword(HTTPRequest $request)
    {
        if ($request->method() == 'POST')
        {
            $member = new Member([
                'pass' => $request->postData('pass'),
                'pass2' => $request->postData('pass2')
            ]);
        }
        else
        {
            $member = new Member;
        }

        $formBuilder = new RenewPasswordFormBuilder($member);
        $formBuilder->build();

        $form = $formBuilder->form();

        $storedMember = $this->managers->getManagerOf('Members')
        ->getByToken($request->getData('token'));

        if ($storedMember
            && $storedMember->active()
            && !$storedMember->tokenExpired())
        {
            if ($request->method() == 'POST' && $form->isValid())
            {
                if (!password_verify($member->pass(), $storedMember->hashPass()))
                {
                    $member->setId($storedMember->id());

                    if ($this->managers->getManagerOf('Members')
                        ->modifyPassword($member))
                    {
                        $this->managers->getManagerOf('Members')
                            ->inhibitToken($member);

                        $this->app->user()->setFlash(
                            'Votre mot de passe a bien été changé. Vous pouvez vous connecter.'
                        );
                    }
                    else
                    {
                        $this->app->user()->setFlash(
                            'Une erreur s\'est produite',
                            User::FLASH_ERROR
                        );
                    }

                    $this->app->httpResponse()->redirect('/');
                }
                else
                {
                    $this->app->user()->setFlash(
                        'Votre mot de passe n\'a pas changé',
                        User::FLASH_ERROR
                    );

                }
            }
        }
        else
        {
            $this->app->user()->setFlash(
                'Le lien utilisé est invalide ou a expiré, ou l\'utilisateur n\'est pas activé',
                User::FLASH_ERROR
            );

            $this->app->httpResponse()->redirect('/');
        }

        $this->page->addVar('title', 'Changer mon mot de passe');
        $this->page->addVar('form', $form->createView());
    }

    public function executeIndex(HTTPRequest $request)
    {
        $member = $this->managers->getManagerOf('Members')->getByLogin(
            $this->app->user()->getAttribute('login')
        );

        $this->page->addVar('title', 'Mon profil');
        $this->page->addVar('member', $member);
    }

    public function executeList(HTTPRequest $request)
    {
        $manager = $this->managers->getManagerOf('Members');

        $nombreMembres = $this->app->config()->get('nombre_membres');

        $pagination = new Pagination($this->app, $manager, $nombreMembres);

        $members = $manager->getList($pagination->getOffset(), $nombreMembres);

        $this->page->addVar('title', 'Membres');
        $this->page->addVar('members', $members);
        $this->page->addVar('pagination', $pagination->createView());
    }

    public function executeUpdateMember(HTTPRequest $request)
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
    }

    public function executeChangePassword(HTTPRequest $request)
    {
        $storedMember = $this->managers->getManagerOf('Members')
        ->getByLogin($this->app->user()->getAttribute('login'));
        $storedHashPass = $storedMember->hashPass();

        if ($request->method() == 'POST')
        {
            $member = new Member([
                'id' => $storedMember->id(),
                'pass' => $request->postData('pass'),
                'pass2' => $request->postData('pass2')
            ]);
        }
        else
        {
            $member = new Member;
        }

        $formBuilder = new ChangePasswordFormBuilder($member);
        $formBuilder->build();

        $form = $formBuilder->form();

        if ($request->method() == 'POST' && $form->isValid())
        {
            if (password_verify($request->postData('currentPass'), $storedHashPass))
            {
                if (!password_verify($member->pass(), $storedHashPass))
                {
                    if ($this->managers->getManagerOf('Members')
                        ->modifyPassword($member))
                    {
                        $this->app->user()->setFlash(
                            'Votre mot de passe a bien été changé'
                        );

                        if (isset($_COOKIE['hash_pass']))
                        {
                            setcookie(
                                'hash_pass',
                                $member->hashPass(),
                                time()
                                + ConnexionController::AUTOSIGNIN_VALIDITY_TIME,
                                null, null, false, true
                            );
                        }

                        $this->app->httpResponse()->redirect('/profile.html');
                    }
                    else
                    {
                        $this->app->user()->setFlash(
                            'Une erreur s\'est produite',
                            User::FLASH_ERROR
                        );
                    }
                }
                else
                {
                    $this->app->user()->setFlash(
                        'Votre mot de passe n\'a pas changé',
                        User::FLASH_ERROR
                    );
                }
            }
            else
            {
                $this->app->user()->setFlash(
                    'Mot de passe actuel incorrect...',
                    User::FLASH_ERROR
                );
            }
        }

        $this->page->addVar('title', 'Changer mon mot de passe');
        $this->page->addVar('form', $form->createView());
    }

    public function executeDeleteMember(HTTPRequest $request)
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
