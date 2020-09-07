<?php
namespace App\Back\Modules\Connexion;

use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \OCFram\User;

class ConnexionController extends BackController
{
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
        $this->page->addVar('title', 'Connexion');

        if ($request->postExists('login'))
        {
            $login = $request->postData('login');
            $password = $request->postData('password');

            if ($login == $this->app->config()->get('login')
            && $password == $this->app->config()->get('pass'))
            {
                $this->app->user()->setAdmin();
                $this->app->httpResponse()->redirect('.');
            }
            else
            {
                $this->app->user()->setFlash(
                    'Le pseudo ou le mot de passe est incorrect.',
                    User::FLASH_ERROR
                );
            }
        }
    }

    public function executeSignOut(HTTPRequest $request)
    {
        $this->app->user()->setAdmin(false);
        $this->app->httpResponse()->redirect('/');
    }
}
