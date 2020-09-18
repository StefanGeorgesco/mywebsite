<?php
namespace Model;

use \OCFram\Manager;
use \Entity\Authorization;

abstract class AuthorizationsManager extends Manager
{
    /**
    * Méthode permettant d'ajouter une autorisation.
    * @param $authorization Authorization L'autorisation à ajouter
    * @return bool
    */
    abstract protected function add(Authorization $authorization);

    /**
    * Méthode permettant de modifier une autorisation.
    * @param $authorization Authorization l'autorisation à modifier
    * @return bool
    */
    abstract protected function modify(Authorization $authorization);

    /**
    * Méthode permettant d'enregistrer une autorisation.
    * @param $authorization Authorization l'autorisation à enregistrer
    * @see self::add()
    * @see self::modify()
    * @return bool
    */
    public function save(Authorization $authorization)
    {
        if ($authorization->isValid())
        {
            if ($authorization->isNew())
            {
                return $this->add($authorization);
            }
            else
            {
                return $this->modify($authorization);
            }
        }
        else
        {
            throw new \RuntimeException(
                'L\'autorisation doit être validée pour être enregistrée'
            );
        }
    }

    /**
    * Méthode permettant de supprimer une autorisation.
    * @param $id int L'identifiant de l'autorisation à supprimer
    * @return int
    */
    abstract public function delete($id);

    /**
    * Méthode retournant une liste d'autorisations demandée
    * @param $debut int La première autorisations à sélectionner
    * @param $limite int Le nombre d'autorisations à sélectionner
    * @return array La liste des autorisations. Chaque entrée est une instance
    * de Authorization.
    */
    abstract public function getList($debut = -1, $limite = -1);

    /**
   * Méthode retournant une autorisations précise.
   * @param $id int L'identifiant de l'autorisation à récupérer
   * @return Authorization L\'autorisation demandée
   */
   abstract public function get($id);

   /**
   * Méthode renvoyant le nombre d'autorisations total.
   * @return int
   */
   abstract public function count();
}
