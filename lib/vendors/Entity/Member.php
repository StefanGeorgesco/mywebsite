<?php
namespace Entity;

use \OCFram\Entity;

class Member extends Entity
{
    protected   $login,
                $pass,
                $pass2,
                $hashPass,
                $email,
                $firstName,
                $lastName,
                $birthDate,
                $phone,
                $website,
                $housenumber,
                $street,
                $postcode,
                $city,
                $creationDate,
                $updateDate,
                $active,
                $token,
                $tokenExpiryTime;

    const LOGIN_MATCH_REGEXP = "#^\S+$#";
    const LOGIN_MATCH_TEXT = 'Le pseudo doit comporter au minimum un caractère et ne doit pas contenir d\'espace)';
    const PASS_MATCH_REGEXP = "#^\S{8,}$#";
    const PASS_MATCH_TEXT = 'Le mot de passe doit comporter au moins 8 caractères et ne doit pas contenir d\'espace';
    const EMAIL_MATCH_REGEXP = "#^\S+@\S+$#";
    const EMAIL_MATCH_TEXT = 'L\'adresse e-mail doit être présente et valide';
    const PHONE_MATCH_REGEXP = "#^\s*(|0[1-8]([-. ]?[0-9]{2}){4})\s*$#";
    const PHONE_MATCH_TEXT = 'Le numéro de téléphone doit respecter pas le format sur 10 chiffres';
    const WEBSITE_MATCH_REGEXP = "#^\s*(|https?://\S+)\s*$#";
    const WEBSITE_MATCH_TEXT = 'L\'url du site web doit respecter le format (http://... ou https://...)';
    const POSTCODE_MATCH_REGEXP = "#^(|\S{5})$#";
    const POSTCODE_MATCH_TEXT = 'Le code postal doit comporter 5 caractères';
    const MIN_AGE = 13;
    const MIN_AGE_TEXT = 'Il faut être agé de 13 ans au minimum pour être membre';
    const MAX_AGE = 150;
    const MAX_AGE_TEXT = 'Erreur de saisie de la date';
    const LOGIN_INVALID = 1;
    const PASS_INVALID = 2;
    const PASS2_INVALID = 3;
    const HASHPASS_INVALID = 4;
    const EMAIL_INVALID = 5;
    const FIRSTNAME_INVALID = 6;
    const LASTNAME_INVALID = 7;
    const BIRTHDATE_INVALID = 8;
    const PHONE_INVALID = 9;
    const WEBSITE_INVALID = 10;
    const HOUSENUMBER_INVALID = 11;
    const STREET_INVALID = 12;
    const POSTCODE_INVALID = 13;
    const CITY_INVALID = 14;
    const TOKEN_INVALID = 15;

    public function isValid()
    {
        return (bool) preg_match(self::LOGIN_MATCH_REGEXP, $this->login)
            && (bool) preg_match(self::PASS_MATCH_REGEXP, $this->pass)
            && $this->pass == $this->pass2
            && (bool) preg_match(self::EMAIL_MATCH_REGEXP, $this->email);
    }

    public function hasSameContent(Member $otherMember)
    {
        return $this->login() ==  $otherMember->login()
            && $this->email() ==  $otherMember->email()
            && $this->firstName() == $otherMember->firstName()
            && $this->lastName() == $otherMember->lastName()
            && $this->birthDate() == $otherMember->birthDate()
            && $this->phone() == $otherMember->phone()
            && $this->website() == $otherMember->website()
            && $this->housenumber() == $otherMember->housenumber()
            && $this->street() == $otherMember->street()
            && $this->postcode() == $otherMember->postcode()
            && $this->city() == $otherMember->city()
            ;
    }

    public function tokenExpired()
    {
        return new \DateTime > $this->tokenExpiryTime;
    }

    // SETTERS //

    public function setLogin($login)
    {
        if (!preg_match(self::LOGIN_MATCH_REGEXP, $login))
        {
            $this->erreurs[] = self::LOGIN_INVALID;
        }

        $this->login = $login;
    }

    public function setPass($pass)
    {
        if (!preg_match(self::PASS_MATCH_REGEXP, $pass))
        {
            $this->erreurs[] = self::PASS_INVALID;
        }

        $this->pass = $pass;
    }

    public function setPass2($pass)
    {
        if (!is_string($pass))
        {
            $this->erreurs[] = self::PASS2_INVALID;
        }

        $this->pass2 = $pass;
    }

    public function setHashPass($hashPass)
    {
        if (!is_string($hashPass))
        {
            $this->erreurs[] = self::HASHPASS_INVALID;
        }

        $this->hashPass = $hashPass;
    }

    public function setEmail($email)
    {
        if (!preg_match(self::EMAIL_MATCH_REGEXP, $email))
        {
            $this->erreurs[] = self::EMAIL_INVALID;
        }

        $this->email = $email;
    }

    public function setFirstName($firstName)
    {
        if (!is_string($firstName))
        {
            $this->erreurs[] = self::FIRSTNAME_INVALID;
        }

        $this->firstName = $firstName;
    }

    public function setLastName($lastName)
    {
        if (!is_string($lastName))
        {
            $this->erreurs[] = self::LASTNAME_INVALID;
        }

        $this->lastName = $lastName;
    }

    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
    }

    public function setPhone($phone)
    {
        if (!preg_match(self::PHONE_MATCH_REGEXP, $phone))
        {
            $this->erreurs[] = self::PHONE_INVALID;
        }

        $this->phone = $phone;
    }

    public function setWebsite($website)
    {
        if (!preg_match(self::WEBSITE_MATCH_REGEXP, $website))
        {
            $this->erreurs[] = self::WEBSITE_INVALID;
        }

        $this->website = $website;
    }

    public function setHousenumber($housenumber)
    {
        if (!is_string($housenumber))
        {
            $this->erreurs[] = self::HOUSENUMBER_INVALID;
        }

        $this->housenumber = $housenumber;
    }

    public function setStreet($street)
    {
        if (!is_string($street))
        {
            $this->erreurs[] = self::STREET_INVALID;
        }

        $this->street = $street;
    }

    public function setPostcode($postcode)
    {
        if (!is_string($postcode))
        {
            $this->erreurs[] = self::POSTCODE_INVALID;
        }

        $this->postcode = $postcode;
    }

    public function setCity($city)
    {
        if (!is_string($city))
        {
            $this->erreurs[] = self::CITY_INVALID;
        }

        $this->city = $city;
    }

    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function setUpdateDate(\DateTime $updateDate)
    {
        $this->updateDate = $updateDate;
    }

    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    public function setToken($token)
    {
        if (!is_string($token))
        {
            $this->erreurs[] = self::TOKEN_INVALID;
        }

        $this->token = $token;
    }

    public function setTokenExpiryTime(\DateTime $tokenExpiryTime)
    {
        $this->tokenExpiryTime = $tokenExpiryTime;
    }

    // GETTERS //

    public function login()
    {
        return $this->login;
    }

    public function pass()
    {
        return $this->pass;
    }

    public function pass2()
    {
        return $this->pass2;
    }

    public function hashPass()
    {
        return $this->hashPass;
    }

    public function email()
    {
        return $this->email;
    }

    public function firstName()
    {
        return $this->firstName;
    }

    public function lastName()
    {
        return $this->lastName;
    }

    public function birthDate()
    {
        return $this->birthDate;
    }

    public function phone()
    {
        return $this->phone;
    }

    public function website()
    {
        return $this->website;
    }

    public function housenumber()
    {
        return $this->housenumber;
    }

    public function street()
    {
        return $this->street;
    }

    public function postcode()
    {
        return $this->postcode;
    }

    public function city()
    {
        return $this->city;
    }

    public function creationDate()
    {
        return $this->creationDate;
    }

    public function updateDate()
    {
        return $this->updateDate;
    }

    public function active()
    {
        return $this->active;
    }

    public function token()
    {
        return $this->token;
    }

    public function tokenExpiryTime()
    {
        return $this->tokenExpiryTime;
    }
}
