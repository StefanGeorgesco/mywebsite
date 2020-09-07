<?php
namespace Model;

use \OCFram\Manager;
use \Entity\Member;

abstract class MembersManager extends Manager
{
    use \OCFram\RandomToken;

    /**
    * Méthode permettant d'ajouter un membre.
    * @param $member Member Le membre à ajouter
    * @return bool
    */
    abstract protected function add(Member $member);

    /**
    * Méthode permettant de modifier un membre.
    * @param $member Member Le membre à modifier
    * @return bool
    */
    abstract protected function modify(Member $member);

    /**
    * Méthode permettant de générer un token pour un membre.
    * @param $member Member Le membre pour lequel on veut générer le token
    * @return string | null
    */
    abstract public function generateToken(Member $member);

    /**
    * Méthode permettant de d'annuler la validité du token pour un membre.
    * @param $member Member Le membre pour lequel on veut annuler le token
    * @return bool succès de l'opération
    */
    abstract public function inhibitToken(Member $member);

    /**
    * Méthode permettant d'activer un membre.
    * @param Member $member Le membre à activer
    * @return int
    */
    abstract public function activate(Member $member);

    /**
    * Méthode permettant de modifier le mot de passe d'un membre.
    * @param $member Member Le membre à modifier
    * @return bool
    */
    abstract public function modifyPassword(Member $member);

    /**
    * Méthode permettant d'enregistrer un membre.
    * @param $member Member Le membre à enregistrer
    * @see self::add()
    * @see self::modify()
    * @return bool
    */
    public function save(Member $member)
    {
        if ($member->isValid())
        {
            if ($member->isNew())
            {
                return $this->add($member);
            }
            else
            {
                return $this->modify($member);
            }
        }
        else
        {
            throw new \RuntimeException(
                'Le membre doit être validé pour être enregistré'
            );
        }
    }

    /**
    * Méthode permettant de supprimer un membre.
    * @param Member $member Le membre à supprimer
    * @return int
    */
    abstract public function delete(Member $member);

    /**
    * Méthode retournant une liste de membres demandée
    * @param $debut int Le premier membre à sélectionner
    * @param $limite int Le nombre de membres à sélectionner
    * @return array La liste des membres. Chaque entrée est une instance de Member.
    */
    abstract public function getList($debut = -1, $limite = -1, $activeOnly=true);

    /**
    * Méthode retournant un membre précis par son id (jeu de données réduit)
    * @param $id int L'identifiant du membre à récupérer
    * @return Member Le membre demandé | null
    */
    abstract public function getById($id);

    /**
    * Méthode retournant un membre précis par son login
    * (jeu de données étendu, recherche sensible à la casse).
    * @param $login string Le login du membre à récupérer
    * @return Member Le membre demandé | null
    */
    abstract public function getByLogin($login);

    /**
    * Méthode retournant un membre précis par son email
    * (jeu de données étendu, recherche insensible à la casse).
    * @param $email string l'email du membre à récupérer
    * @return Member Le membre demandé | null
    */
    abstract public function getByEmail($email);

    /**
    * Méthode retournant un membre précis par son token
    * (jeu de données étendu, recherche sensible à la casse).
    * @param $token string Le token du membre à récupérer
    * @return Member Le membre demandé | null
    */
    abstract public function getByToken($token);

    /**
    * Méthode indiquant si un membre identifié par son login existe
    * en base de données (recherche insensible à la casse).
    * @param $login string Le login du membre dont on veut vérifier l'existence.
    * @return bool Le résultat sur l'existence du membre
    */
    abstract public function existsLogin($login);

    /**
    * Méthode indiquant si un membre identifié par son email existe
    * en base de données (recherche insensible à la casse).
    * @param $email string L'email du membre dont on veut vérifier l'existence.
    * @return bool Le résultat sur l'existence du membre
    */
    abstract public function existsEmail($email);

    /**
    * Méthode renvoyant le nombre de membres total.
    * @return int
    */
    abstract public function count($activeOnly=true);
}
