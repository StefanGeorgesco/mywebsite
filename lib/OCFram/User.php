<?php
namespace OCFram;

session_start();

class User extends ApplicationComponent
{
    const FLASH_ERROR = 1;
    const FLASH_OK = 2;

    public function getAttribute($attr)
    {
        return isset($_SESSION[$attr]) ? $_SESSION[$attr] : null;
    }

    public function getFlash()
    {
        $flash = $_SESSION['flash'];
        $flashType =
            isset($_SESSION['flashType']) ? $_SESSION['flashType'] : null;

        unset($_SESSION['flash']);
        unset($_SESSION['flashType']);

        return ['flash' => $flash, 'flashType' => $flashType];
    }

    public function hasFlash()
    {
        return isset($_SESSION['flash']);
    }

    public function isAuthenticated()
    {
        return isset($_SESSION['auth']) && $_SESSION['auth'] === true;
    }

    public function isAdmin()
    {
        return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
    }

    public function setAttribute($attr, $value)
    {
        $_SESSION[$attr] = $value;
    }

    public function setFlash($value, $type=self::FLASH_OK)
    {
        $_SESSION['flash'] = $value;
        $_SESSION['flashType'] =
            $type == self::FLASH_OK ? 'flash_ok' : 'flash_error';
    }

    public function setAuthenticated($login = null, $authenticated = true)
    {
        if (!is_bool($authenticated))
        {
            throw new \InvalidArgumentException(
                'La valeur spécifiée à la méthode User::setAuthenticated() doit être un booléen'
            );
        }

        if ($authenticated && $login)
        {
            $_SESSION['login'] = $login;
        }

        if (!$authenticated)
        {
            unset($_SESSION['login']);
        }

        $_SESSION['auth'] = $authenticated;
    }

    public function setAdmin($admin = true)
    {
        if (!is_bool($admin))
        {
            throw new \InvalidArgumentException(
                'La valeur spécifiée à la méthode User::setAdmin() doit être un booléen'
            );
        }

        $_SESSION['admin'] = $admin;
    }
}
