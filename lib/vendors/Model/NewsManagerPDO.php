<?php
namespace Model;

use \Entity\News;

class NewsManagerPDO extends NewsManager
{
    protected function add(News $news)
    {
        $q = $this->dao->prepare("
            INSERT INTO news
            SET author=:author, title=:title, contents=:contents,
            creationDate=NOW(), updateDate=NOW()
        ");

        $q->bindValue(':title', $news->title());
        $q->bindValue(':author', $news->author());
        $q->bindValue(':contents', $news->contents());

        $ret = $q->execute();

        $news->setId($this->dao->lastInsertId());

        return $ret;
    }

    protected function modify(News $news)
    {
        if (!$news->hasSameContent($this->get($news->id())))
        {
            $q = $this->dao->prepare("
                UPDATE news
                SET author=:author, title=:title, contents=:contents,
                updateDate=NOW()
                WHERE id=:id
            ");

            $q->bindValue(':author', $news->author());
            $q->bindValue(':title', $news->title());
            $q->bindValue(':contents', $news->contents());
            $q->bindValue(':id', $news->id(), \PDO::PARAM_INT);

            return $q->execute();
        }

        return false;
    }

    public function delete($id)
    {
        return $this->dao->exec("
            DELETE FROM news WHERE id=
        ".(int) $id);
    }

    public function getList($debut = -1, $limite = -1)
    {
        $sql = "
        SELECT id, author, title, contents, creationDate, updateDate
        FROM news
        ORDER BY creationDate DESC
        ";

        if ($debut != -1 || $limite != -1)
        {
            $sql .= ' LIMIT '.(int) $limite.' OFFSET '.(int) $debut;
        }

        $q = $this->dao->query($sql);
        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\News'
        );

        $newsList = $q->fetchAll();

        $q->closeCursor();

        foreach ($newsList as $news)
        {
            $news->setCreationDate(new \DateTime($news->creationDate()));
            $news->setUpdateDate(new \DateTime($news->updateDate()));
        }

        return $newsList;
    }

    public function get($id)
    {
        $q = $this->dao->prepare("
            SELECT id, author, title, contents, creationDate, updateDate
            FROM news
            WHERE id=:id
        ");
        $q->bindValue(':id', (int) $id, \PDO::PARAM_INT);
        $q->execute();

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\News'
        );

        if ($news = $q->fetch())
        {
            $news->setCreationDate(new \DateTime($news->creationDate()));
            $news->setUpdateDate(new \DateTime($news->updateDate()));

            return $news;
        }

        return null;
    }

    public function count()
    {
        return $this->dao->query("
            SELECT COUNT(*) FROM news
        ")->fetchColumn();
    }
}
