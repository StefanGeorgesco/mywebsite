<?php
namespace App\Front\Modules\Security;

use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \OCFram\FormHandler;
use \OCFram\User;
use \Entity\Authorization;
use \FormBuilder\AuthorizationFormBuilder;

class SecurityController extends BackController
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
        $memberId = $this->managers->getManagerOf('members')
            ->getByLogin($this->app->user()->getAttribute('login'))->id();

        $manager = $this->managers->getManagerOf('Authorizations');

        $nombreAutorisations = $this->app->config()->get('nombre_autorisations');

        try
        {
            $pagination = new Pagination(
                $this->app,
                $manager,
                $nombreAutorisations,
                $memberId
            );
        }
        catch (\RuntimeException $e)
        {
            $this->app->httpResponse()->redirect404();
        }


        $authorizations = $manager->getListOfMember(
            $memberId,
            $pagination->getOffset(),
            $nombreAutorisations
        );

        $this->page->addVar('title', 'Autorisations');
        $this->page->addVar('authorizations', $authorizations);
        $this->page->addVar('pagination', $pagination->createView());
    }

    public function executeAdd(HTTPRequest $request)
    {
        $memberId = $this->managers->getManagerOf('members')
            ->getByLogin($this->app->user()->getAttribute('login'))->id();

        if ($request->method() == 'POST' && $request->postExists('submit'))
        {
            $authorization = new Authorization([
                'type' => 'member',
                'member' => $memberId,
                'description' => $request->postData('description'),
            ]);
        }
        else
        {
            $authorization = new Authorization;
        }

        $formBuilder = new AuthorizationFormBuilder($authorization);
        $formBuilder->build();

        $form = $formBuilder->form();

        $formHandler = new FormHandler(
            $form,
            $this->managers->getManagerOf('Authorizations'),
            $request
        );

        if ($formHandler->process())
        {
            $this->app->user()->setFlash(
                "Votre autorisation a été créée. Veuillez copier ce numéro,
                car vous ne le reverrez plus :<br />" .
                $authorization->getFullToken()
            );

            $this->app->httpResponse()->redirect('authorizations.html');
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
                    'Une erreur s\'est produite.',
                    User::FLASH_ERROR
                );
            }
        }

        $this->page->addVar('title', 'Nouvelle autorisation');
        $this->page->addVar('form', $form->createView());
    }

    public function executeUpdate(HTTPRequest $request)
    {
        $manager = $this->managers->getManagerOf('Authorizations');

        $storedAuthorization = $manager->get($request->getData('id'));

        if ($request->method() == 'POST' && $request->postExists('submit'))
        {
            $authorization = new Authorization([
                'id' => $storedAuthorization->id(),
                'type' => $storedAuthorization->type(),
                'member' => $storedAuthorization->member(),
                'description' => $request->postData('description'),
            ]);
        }
        else
        {
            $authorization = $storedAuthorization;
        }

        $formBuilder = new AuthorizationFormBuilder($authorization);
        $formBuilder->build();

        $form = $formBuilder->form();

        $formHandler = new FormHandler(
            $form,
            $manager,
            $request
        );

        if ($formHandler->process())
        {
            $this->app->user()->setFlash('L\'autorisation a bien été modifiée');

            $this->app->httpResponse()->redirect('authorizations.html');
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
                    'L\'autorisation n\'a pas été modifiée.',
                    User::FLASH_ERROR
                );
            }
        }

        $this->page->addVar('title', 'Modifier une autorisation');
        $this->page->addVar('form', $form->createView());
    }

    public function executeDelete(HTTPRequest $request)
    {
        $authorization = $this->managers->getManagerOf('Authorizations')
            ->get($request->getData('id'));

        if ($request->method() == 'POST')
        {
            $this->managers->getManagerOf('Authorizations')
                ->delete($authorization);

            $this->app->user()->setFlash(
                'L\'autorisation a bien été supprimée.'
            );

            $this->app->httpResponse()->redirect('authorizations.html');
        }

        $this->page->addVar('title', 'Supprimer une autorisation');
        $this->page->addVar('authorization', $authorization);
    }
}
