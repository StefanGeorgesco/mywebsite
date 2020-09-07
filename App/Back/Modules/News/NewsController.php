<?php
namespace App\Back\Modules\News;

use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \OCFram\FormHandler;
use \OCFram\User;
use \Entity\News;
use \FormBuilder\NewsFormBuilder;

class NewsController extends BackController
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
        $manager = $this->managers->getManagerOf('News');

        $nombreNews = $this->app->config()->get('nombre_news');
        $nombreCaracteres = $this->app->config()->get('nombre_caracteres');

        $pagination = new Pagination($this->app, $manager, $nombreNews);

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

        $this->page->addVar('title', 'Gestion des news');
        $this->page->addVar('listeNews', $listeNews);
        $this->page->addVar('nombreNews', $manager->count());
        $this->page->addVar('pagination', $pagination->createView());
    }

    public function executeInsert(HTTPRequest $request)
    {
        $this->processForm($request);

        $this->page->addVar('title', 'Ajout d\'une news');
    }

    public function executeUpdate(HTTPRequest $request)
    {
        $this->processForm($request);

        $this->page->addVar('title', 'Modification d\'une news');
    }

    public function executeDelete(HTTPRequest $request)
    {
        $news = $request->getData('id');

        $this->managers->getManagerOf('News')->delete($news);
        $this->managers->getManagerOf('Comments')->deleteFromNews($news);

        $this->app->user()->setFlash('La news a bien été supprimée !');

        $this->app->httpResponse()->redirect('.');
    }

    public function processForm(HTTPRequest $request)
    {
        if ($request->method() == 'POST' && $request->postExists('submit'))
        {
            $news = new News([
                'author' => trim($request->postData('author')),
                'title' => trim($request->postData('title')),
                'contents' => trim($request->postData('contents'))
            ]);

            if ($request->getExists('id'))
            {
                $news->setId($request->getData('id'));
                $news->setCreationDate(
                    $this->managers->getManagerOf('News')
                    ->get($request->getData('id'))->creationDate()
                );
                $news->setUpdateDate(
                    $this->managers->getManagerOf('News')
                    ->get($request->getData('id'))->updateDate()
                );
            }
        }
        else
        {
            if ($request->getExists('id'))
            {
                $news = $this->managers->getManagerOf('News')
                ->get($request->getData('id'));
            }
            else
            {
                $news = new News;
            }
        }

        $formBuilder = new NewsFormBuilder($news);
        $formBuilder->build();

        $form = $formBuilder->form();

        $formHandler = new FormHandler(
            $form,
            $this->managers->getManagerOf('News'),
            $request
        );

        $isNew = $news->isNew();

        if ($formHandler->process())
        {
            $this->app->user()->setFlash(
                $isNew ?
                'La news a bien été ajoutée !' :
                'La news a bien été modifiée !'
            );
            $this->app->httpResponse()->redirect('.');
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
                    'La news n\'a pas pu être ajoutée...' :
                    'La news n\'a pas été modifiée...',
                    User::FLASH_ERROR
                );
            }
        }

        $this->page->addVar('news', $news);
        $this->page->addVar('form', $form->createView());
    }
}
