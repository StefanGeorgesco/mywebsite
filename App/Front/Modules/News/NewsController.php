<?php
namespace App\Front\Modules\News;

use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \OCFram\FormHandler;
use \OCFram\User;
use \Entity\Comment;
use \FormBuilder\CommentFormBuilder;

class NewsController extends BackController
{
    public function needsAuthentication()
    {
        return in_array(
            $this->action,
            [
                'insertComment',
                'updateComment',
                'deleteComment'
            ]
        );
    }

    public function isAdminAccessible()
    {
        return in_array(
            $this->action,
            [
                'updateComment',
                'deleteComment'
            ]
        );
    }

    public function executeIndex(HTTPRequest $request)
    {
        $manager = $this->managers->getManagerOf('News');

        $nombreNews = $this->app->config()->get('nombre_news');
        $nombreCaracteres = $this->app->config()->get('nombre_caracteres');

        try
        {
            $pagination = new Pagination($this->app, $manager, $nombreNews);
        }
        catch (\Exception $e)
        {
                $this->app->httpResponse()->redirect404();
        }


        $listeNews = $manager->getList($pagination->getOffset(), $nombreNews);

        foreach ($listeNews as $news)
        {
            if (strlen($news->contents()) > $nombreCaracteres)
            {
                $debut = substr($news->contents(), 0, $nombreCaracteres);
                $debut = substr($debut, 0, strrpos($debut, ' ')) . '...';

                $news->setContents($debut);
            }
        }

        $this->page->addVar('title', 'Liste des news');
        $this->page->addVar('listeNews', $listeNews);
        $this->page->addVar('pagination', $pagination->createView());
    }

    public function executeShow(HTTPRequest $request)
    {
        $news = $this->managers->getManagerOf(
            'News')->get($request->getData('id'));

        if (empty($news))
        {
            $this->app->httpResponse()->redirect404();
        }

        $manager = $this->managers->getManagerOf('Comments');

        $nombreCommentaires = $this->app->config()->get('nombre_commentaires');

        try
        {
            $pagination = new Pagination(
                $this->app, $manager,
                $nombreCommentaires,
                $news->id()
            );
        }
        catch (\Exception $e)
        {
            $this->app->httpResponse()->redirect404();
        }

        $comments = $manager->getListOf(
            $news->id(),
            $pagination->getOffset(),
            $nombreCommentaires
        );

        $this->page->addVar('title', $news->title());
        $this->page->addVar('news', $news);
        $this->page->addVar('comments', $comments);
        $this->page->addVar('pagination', $pagination->createView());
    }

    public function executeInsertComment(HTTPRequest $request)
    {
        $this->processForm($request);

        $this->page->addVar('title', 'Ajout d\'un commentaire');
    }

    public function executeUpdateComment(HTTPRequest $request)
    {
        $this->processForm($request);

        $this->page->addVar('title', 'Modification d\'un commentaire');
    }

    public function executeDeleteComment(HTTPRequest $request)
    {
        $news = $this->managers->getManagerOf('Comments')
            ->get($request->getData('id'))->news();
        $this->managers->getManagerOf('Comments')->delete($request->getData('id'));

        $this->app->user()->setFlash('Le commentaire a bien été supprimé !');

        $this->app->httpResponse()->redirect('/news-'.$news.'.html');
    }

    public function processForm(HTTPRequest $request)
    {
        if ($request->method() == 'POST' && $request->postExists('submit'))
        {
            $member = $this->managers->getManagerOf('Members')
                ->getByLogin($this->app->user()->getAttribute('login'));

            $comment = new Comment([
                'member' => $member ? $member->id() : null,
                'contents' => trim($request->postData('contents'))
            ]);

            if ($request->getExists('news'))
            {
                $comment->setNews($request->getData('news'));
            }

            if ($request->getExists('id'))
            {
                $comment->setId($request->getData('id'));
                $comment->setAuthor(
                    $this->managers->getManagerOf('Comments')
                    ->get($request->getData('id'))->author()
                );
                $comment->setCreationDate(
                    $this->managers->getManagerOf('Comments')
                    ->get($request->getData('id'))->creationDate()
                );
                $comment->setUpdateDate(
                    $this->managers->getManagerOf('Comments')
                    ->get($request->getData('id'))->updateDate()
                );
            }
        }
        else
        {
            if ($request->getExists('id'))
            {
                $comment = $this->managers->getManagerOf('Comments')
                ->get($request->getData('id'));
            }
            else
            {
                $comment = new Comment;
            }
        }

        if ($request->getExists('news'))
        {
            $news = $this->managers->getManagerOf(
                'News')->get($request->getData('news'));
        }
        elseif ($request->getExists('id'))
        {
            $news = $this->managers->getManagerOf(
                'News')->get($this->managers->getManagerOf('Comments')
                ->get($request->getData('id'))->news());
        }

        $formBuilder = new CommentFormBuilder($comment);
        $formBuilder->build();

        $form = $formBuilder->form();

        $formHandler = new FormHandler(
            $form,
            $this->managers->getManagerOf('Comments'),
            $request
        );

        $isNew = $comment->isNew();

        if ($formHandler->process())
        {
            $this->app->user()->setFlash(
                $isNew ?
                'Le commentaire a bien été ajouté, merci !' :
                'Le commentaire a bien été modifié'
            );
            $this->app->httpResponse()->redirect(
                '/news-'.$news->id().'.html'
            );
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
                    $isNew ?
                    'Le commentaire n\'a pas pu être ajouté...' :
                    'Le commentaire n\'a pas été modifié...',
                    User::FLASH_ERROR
                );
            }
        }

        $this->page->addVar('news', $news);
        $this->page->addVar('comment', $comment);
        $this->page->addVar('form', $form->createView());
    }
}
