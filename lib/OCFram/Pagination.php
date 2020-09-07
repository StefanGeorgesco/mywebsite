<?php
namespace OCFram;

class Pagination extends ApplicationComponent
{
    protected $manager;
    protected $itemsPerPage;
    protected $spread;
    protected $select;

    public function __construct(
        Application $app,
        Manager $manager,
        $itemsPerPage=10,
        $select=null,
        $spread=4
    )
    {
        parent::__construct($app);

        $this->setManager($manager);
        $this->setItemsPerPage($itemsPerPage);
        $this->setSelect($select);
        $this->setSpread($spread);
    }

    public function getOffset()
    {
        return ($this->getPage() - 1) * $this->itemsPerPage;
    }

    public function createView()
    {
        $page = $this->getPage();
        $numberOfPages = $this->getNumberOfPages();
        $spread = $this->spread;
        $view = '';

        if ($numberOfPages > 1)
        {
            $view .= '<section class="pagination"><ul>';

            if ($page > 2)
            {
                $view .= '<li><a href="?page=1">&lt;&lt;</a></li>';
            }

            if ($page > 1)
            {
                $view .= '<li><a href="?page='.($page - 1).'">&lt;</a></li>';
            }

            // now show page numbers from $fisrt to $last limits
           $fisrt = max(1, $page - $spread);
           $last = min($page + $spread, $numberOfPages);
           // half-way page numbers if user clicks on '...'
           $mid1 = (int) floor((1 + $fisrt) / 2);
           $mid2 = (int) ceil(($last + $numberOfPages) / 2);


            if ($page > $spread + 1)
            { // page number too high, show '...'
                $view .= '<li><a href="?page='.$mid1.'">...</a></li>';
            }

            for ($p = $fisrt; $p <= $last; $p++)
            {
                if ($p == $page)
                {
                    $view .= '<li><span class="active">'.$p.'</span></li>';
                }
                else
                {
                    $view .= '<li><a href="?page='.$p.'">'.$p.'</a></li>';
                }
            }

            if ($page + $spread < $numberOfPages )
            { // page number too far from number of pages, show '...'
                $view .= '<li><a href="?page='.$mid2.'">...</a></li>';
            }

            if ($page < $numberOfPages)
            {
                $view .= '<li><a href="?page='.($page + 1).'">&gt;</a></li>';
            }

            if ($page < $numberOfPages - 1)
            {
                $view .= '<li><a href="?page='.$numberOfPages.'">&gt;&gt;</a></li>';
            }

            $view .= '</ul></section>';
        }

        return $view;
    }

    public function getPage()
    {
        $page = $this->app->httpRequest()->getData('page');
        if (!$page) $page = 1;

        return max(1, min((int) $page, $this->getNumberOfPages()));
    }

    public function getNumberOfPages()
    {
        $count = ($this->select !== null ?
        $this->manager->count($this->select) :
        $this->manager->count());

        return max(
            1,
            (int) ceil($count / $this->itemsPerPage)
        );
    }

    protected function setManager($manager)
    {
        if (!($manager instanceof Manager))
        {
            throw new \InvalidArgumentException(
                'Le manager doit être un objet Manager'
            );
        }

        $this->manager = $manager;
    }

    protected function setItemsPerPage($itemsPerPage)
    {
        $itemsPerPage = (int) $itemsPerPage;

        if ($itemsPerPage < 1)
        {
            throw new \InvalidArgumentException(
                'Le nombre d\'éléments par page doit être un entier strictement positif'
            );
        }

        $this->itemsPerPage = $itemsPerPage;
    }

    protected function setSpread($spread)
    {
        $spread = (int) $spread;

        if ($spread < 1)
        {
            throw new \InvalidArgumentException(
                'L\'étendue d\'affichage doit être un entier strictement positif'
            );
        }

        $this->spread = $spread;
    }

    protected function setSelect($select)
    {
        $this->select = $select;
    }
}
