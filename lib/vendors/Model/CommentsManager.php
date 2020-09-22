<?php
namespace Model;

use \OCFram\Manager;
use \Entity\Comment;

abstract class CommentsManager extends Manager
{
    /**
    * Méthode permettant d'ajouter un commentaire
    * @param $comment Le commentaire à ajouter
    * @return bool
    */
    abstract protected function add(Comment $comment);

    /**
    * Méthode permettant de modifier un commentaire.
    * @param $comment Le commentaire à modifier
    * @return bool
    */
    abstract protected function modify(Comment $comment);

    /**
    * Méthode permettant d'enregistrer un commentaire.
    * @param $comment Le commentaire à enregistrer
    * @return bool
    */
    public function save(Comment $comment)
    {
        if ($comment->isValid())
        {
            if ($comment->isNew())
            {
                return $this->add($comment);
            }
            else
            {
                return $this->modify($comment);
            }
        }
        else
        {
            throw new \RuntimeException(
                'Le commentaire doit être validé pour être enregistré'
            );
        }
    }

    /**
    * Méthode permettant de supprimer un commentaire.
    * @param $id L'identifiant du commentaire à supprimer
    * @return int
    */
    abstract public function delete($id);

    /**
    * Méthode permettant de supprimer tous les commentaires liés à une news
    * @param $news int L'identifiant de la news dont les commentaires
    * doivent être supprimés
    * @return int
    */
    abstract public function deleteFromNews($news);

    /**
    * Méthode permettant de supprimer tous les commentaires d'un membre
    * @param $member int L'identifiant du membre dont les commentaires
    * doivent être supprimés
    * @return int
    */
    abstract public function deleteFromMember($member);

    /**
    * Méthode permettant de récupérer la liste de commentaires d'une news.
    * @param $news int L'identifiant de la news pour laquelle on veut
    * récupérer les commentaires
    * @param $debut int La première news à sélectionner
    * @param $limite int Le nombre de news à sélectionner
    * @return array
    */
    abstract public function getListOf($news, $debut = -1, $limite = -1);

    /**
    * Méthode permettant de récupérer la liste des commentaires d'un membre.
    * @param $member int L'identifiant du membre dont on veut
    * récupérer les commentaires
    * @param $debut int La première news à sélectionner
    * @param $limite int Le nombre de news à sélectionner
    * @return array
    */
    abstract public function getListOfMember($member, $debut = -1, $limite = -1);

    /**
    * Méthode permettant d'obtenir un commentaire spécifique.
    * @param $id L'identifiant du commentaire
    * @return Comment
    */
    abstract public function get($id);

   /**
   * Méthode renvoyant le nombre de news total,
   * ou pour une news si elle est spécifiée
   * @param $news La news dont on veut compter les commentaires
   * @return int
   */
   abstract public function count($news='%');
}
