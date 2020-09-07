<?php
namespace Model;

use \Entity\Comment;

class CommentsManagerPDO extends CommentsManager
{
    protected function add(Comment $comment)
    {
        $q = $this->dao->prepare("
            INSERT INTO comments
            SET news=:news, member=:member, contents=:contents,
            creationDate=NOW(), updateDate=NOW()
        ");

        $q->bindValue(':news', $comment->news(), \PDO::PARAM_INT);
        $q->bindValue(':member', $comment->member(), \PDO::PARAM_INT);
        $q->bindValue(':contents', $comment->contents());

        $ret = $q->execute();

        $comment->setId($this->dao->lastInsertId());

        return $ret;
    }

    protected function modify(Comment $comment)
    {
        if (!$comment->hasSameContent($this->get($comment->id())))
        {
            $q = $this->dao->prepare("
                UPDATE comments
                SET contents=:contents, updateDate=NOW()
                WHERE id=:id
            ");

            $q->bindValue(':contents', $comment->contents());
            $q->bindValue(':id', $comment->id(), \PDO::PARAM_INT);

            return $q->execute();
        }

        return false;
    }

    public function delete($id)
    {
        return $this->dao->exec("
            DELETE FROM comments WHERE id=
        ".(int) $id);
    }

    public function deleteFromNews($news)
    {
        return $this->dao->exec("
            DELETE FROM comments WHERE news=
        ".(int) $news);
    }

    public function deleteFromMember($member)
    {
        return $this->dao->exec("
            DELETE FROM comments WHERE member=
        ".(int) $member);
    }

    public function getListOf($news, $debut = -1, $limite = -1)
    {
        if (!ctype_digit($news))
        {
            throw new \InvalidArgumentException(
                'L\'identifiant de la news passé doit être un nombre entier valide'
            );
        }

        $sql = "
            SELECT comments.id, news, member, members.login AS author, contents,
            comments.creationDate, comments.updateDate
            FROM comments
            LEFT JOIN members ON members.id=comments.member
            WHERE news=:news
            ORDER BY comments.creationDate DESC
        ";

        if ($debut != -1 || $limite != -1)
        {
            $sql .= ' LIMIT '.(int) $limite.' OFFSET '.(int) $debut;
        }

        $q = $this->dao->prepare($sql);
        $q->bindValue(':news', $news, \PDO::PARAM_INT);
        $q->execute();

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Comment'
        );

        $comments = $q->fetchAll();

        foreach ($comments as $comment)
        {
            $comment->setCreationDate(new \DateTime($comment->creationDate()));
            $comment->setUpdateDate(new \DateTime($comment->updateDate()));
        }

        return $comments;
    }

    public function get($id)
    {
        $q = $this->dao->prepare("
            SELECT comments.id, news, member, members.login AS author, contents,
            comments.creationDate, comments.updateDate
            FROM comments
            LEFT JOIN members ON members.id=comments.member
            WHERE comments.id=:id
        ");
        $q->bindValue(':id', (int) $id, \PDO::PARAM_INT);
        $q->execute();

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Comment');

        if ($comment = $q->fetch())
        {
            $comment->setCreationDate(new \DateTime($comment->creationDate()));
            $comment->setUpdateDate(new \DateTime($comment->updateDate()));

            return $comment;
        }

        return null;
    }

    public function count($news='%')
    {
        $q = $this->dao->prepare("
            SELECT COUNT(*) FROM comments WHERE news LIKE :news
        ");
        $q->bindValue(':news', $news);
        $q->execute();

        return $q->fetchColumn();
    }
}
