<?php
namespace App\API\Modules\News;

use \OCFram\APIController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \Entity\News;
use \Entity\Comment;
use \FormBuilder\NewsFormBuilder;
use \FormBuilder\CommentFormBuilder;

class NewsController extends APIController
{
    public function executeNewsPOST(HTTPRequest $request)
    {
        if ($request->getExists('id'))
        {
            $this->exitWithError(400);
        }

        $authorization = $this->getAuthorization();

        if (!$authorization || !$authorization->isAdmin())
        {
            $this->exitWithError(401, 'user is not admin');
        }

        if (!($opId = $request->requestBodyDataVar('opId')))
        {
            $this->exitWithError(400, 'no operation id');
        }

        if (!$authorization->isClearOpId($opId))
        {
            $this->exitWithError(400, 'operation id already used');
        }

        $news = new News($request->requestBodyData());

        $formBuilder = new NewsFormBuilder($news);
        $formBuilder->build();
        $form = $formBuilder->form();

        if (!$form->isValid())
        {
            $this->exitWithError(400, 'news data incorrect', $form->errors());
        }

        if ($this->managers->getManagerOf('News')->save($news))
        {
            $this->managers->getManagerOf('Authorizations')
                ->addOpId($authorization, $opId);

            $response = $this->dismount(
                $this->managers->getManagerOf('News')->get($news->id())
            );
        }
        else
        {
            $this->exitWithError(500);
        }

        $this->setResponseCode(201);
        $this->setResponse($response);
    }

    public function executeNewsGET(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();

        $newsManager = $this->managers->getManagerOf('News');
        $commentsManager = $this->managers->getManagerOf('Comments');

        if ($request->getExists('id'))
        {
            $news = $newsManager->get($request->getData('id'));

            if ($news)
            {
                $response = $this->dismount($news);

                $commentsArray = array_map(
                    function ($comment)
                    {
                        return $this->dismount($comment);
                    },
                    $commentsManager->getListOf($news['id'])
                );

                $response['comments'] = $commentsArray;
            }
            else
            {
                $this->exitWithError(404, 'this news does not exist');
            }
        }
        else
        {
            $nombreNews = $this->app->config()->get('nombre_news');

            $newsList = $newsManager->getList();

            $response = $this->filter(
                array_map(
                    function ($news) use ($commentsManager)
                    {
                        $newsArray = $this->dismount($news);

                        $commentsArray = array_map(
                            function ($comment)
                            {
                                return $this->dismount($comment);
                            },
                            $commentsManager->getListOf($news['id'])
                        );

                        $newsArray['comments'] = $commentsArray;

                        return $newsArray;
                    },
                    $newsList
                )
            );

            $response = $this->paginate($response, $nombreNews);
        }

        $this->setResponse($response);
    }

    public function executeNewsPATCH(HTTPRequest $request)
    {
        if (!$request->getExists('id'))
        {
            $this->exitWithError(400);
        }

        $newsId = $request->getData('id');

        $authorization = $this->getAuthorization();

        if (!$authorization || !$authorization->isAdmin())
        {
            $this->exitWithError(401, 'user is not admin');
        }

        if (!($storedNews = $this->managers->getManagerOf('News')->get($newsId)))
        {
            $this->exitWithError(404, "news $newsId does not exist");
        }

        $news = new News(
            array_merge(
                $this->dismount($storedNews),
                $request->requestBodyData()
                )
        );

        $formBuilder = new NewsFormBuilder($news);
        $formBuilder->build();
        $form = $formBuilder->form();

        if (!$form->isValid())
        {
            $this->exitWithError(400, 'news data incorrect', $form->errors());
        }
        elseif ($news->hasSameContent($storedNews))
        {
            $response = array(
                'message' => 'news data is identical (not updated)',
            );
        }
        elseif ($this->managers->getManagerOf('News')->save($news))
        {
            $response = $this->dismount(
                $this->managers->getManagerOf('News')->get($news->id())
            );
        }
        else
        {
            $this->exitWithError(500);
        }

        $this->setResponse($response);
    }

    public function executeNewsDELETE(HTTPRequest $request)
    {
        if (!$request->getExists('id'))
        {
            $this->exitWithError(400);
        }

        $newsId = $request->getData('id');

        $authorization = $this->getAuthorization();

        if (!$authorization || !$authorization->isAdmin())
        {
            $this->exitWithError(401, 'user is not admin');
        }

        if (!$this->managers->getManagerOf('News')->get($newsId))
        {
            $this->exitWithError(404, "news $newsId does not exist");
        }

        if ($this->managers->getManagerOf('News')->delete($newsId))
        {
            $this->managers->getManagerOf('Comments')->deleteFromNews($newsId);

            $response = array(
                'message' =>
                "news $newsId and attached comments have been deleted"
            );
        }
        else
        {
            $this->exitWithError(500);
        }

        $this->setResponse($response);
    }

    public function executeCommentsPOST(HTTPRequest $request)
    {
        if ($request->getExists('id'))
        {
            $this->exitWithError(400);
        }

        $authorization = $this->getAuthorization();

        if (!$authorization || !$authorization->isMember())
        {
            $this->exitWithError(401, 'user is not a member');
        }

        $newsId = $request->getData('newsId');

        $data = $request->requestBodyData();

        if (!is_numeric($newsId) || $newsId < 0)
        {
            $this->exitWithError(400, 'news id incorrect');
        }

        if (!$this->managers->getManagerOf('News')->get($newsId))
        {
            $this->exitWithError(404, "news $newsId does not exist");
        }

        if (!($opId = $request->requestBodyDataVar('opId')))
        {
            $this->exitWithError(400, 'no operation id');
        }

        if (!$authorization->isClearOpId($opId))
        {
            $this->exitWithError(400, 'operation id already used');
        }

        $data['member'] = $authorization->member();
        $data['news'] = $newsId;

        $comment = new Comment($data);

        $formBuilder = new CommentFormBuilder($comment);
        $formBuilder->build();
        $form = $formBuilder->form();

        if (!$form->isValid())
        {
            $this->exitWithError(
                400,
                'comment data incorrect',
                $form->errors()
            );
        }

        if ($this->managers->getManagerOf('Comments')->save($comment))
        {
            $this->managers->getManagerOf('Authorizations')
                ->addOpId($authorization, $opId);

            $response = $this->dismount(
                $this->managers->getManagerOf('Comments')
                    ->get($comment->id())
            );
        }
        else
        {
            $this->exitWithError(500);
        }

        $this->setResponseCode(201);
        $this->setResponse($response);
    }

    public function executeCommentsGET(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();
        $newsId = $request->getData('newsId');
        $commentsManager = $this->managers->getManagerOf('Comments');

        if ($request->getExists('id'))
        {
            $id = $request->getData('id');

            $comment = $commentsManager->get($id);

            if (!$comment || $comment->news() !== $newsId)
            {
                $this->exitWithError(
                    404,
                    "comment $id of news $newsId does not exist"
                );
            }

            $response = $this->dismount($comment);
        }
        else
        {
            $nombreCommentaires = $this->app->config()
                ->get('nombre_commentaires');

            $comments = $commentsManager->getListOf($request->getData('newsId'));

            $response = $this->filter(
                array_map(
                    function ($comment)
                    {
                        return $this->dismount($comment);
                    },
                    $comments
                )
            );

            $response = $this->paginate($response, $nombreCommentaires);
        }

        $this->setResponse($response);
    }

    public function executeCommentsPATCH(HTTPRequest $request)
    {
        if (!$request->getExists('id'))
        {
            $this->exitWithError(400);
        }

        $id = $request->getData('id');

        $authorization = $this->getAuthorization();

        if (!$authorization)
        {
            $this->exitWithError(401);
        }

        $storedComment = $this->managers->getManagerOf('Comments')
            ->get($id);

        $newsId = $request->getData('newsId');

        if (!$storedComment || $storedComment->news() !== $newsId)
        {
            $this->exitWithError(
                404, "comment $id of news $newsId does not exist"
            );
        }

        if ($authorization->isMember() &&
            $storedComment['member'] != $authorization->member())
        {
            $this->exitWithError(
                401,
                "user does not own comment $id of news $newsId"
            );
        }

        $comment = new Comment(
            array_merge(
                $this->dismount($storedComment),
                $request->requestBodyData()
                )
        );

        $formBuilder = new CommentFormBuilder($comment);
        $formBuilder->build();
        $form = $formBuilder->form();

        if (!$form->isValid())
        {
            $this->exitWithError(400, 'comment data incorrect', $form->errors());
        }
        elseif ($comment->hasSameContent($storedComment))
        {
            $response = array(
                'message' => 'comment data is identical (not updated)',
            );
        }
        elseif ($this->managers->getManagerOf('Comments')->save($comment))
        {
            $response = $this->dismount(
                $this->managers->getManagerOf('Comments')->get($comment->id())
            );
        }
        else
        {
            $this->exitWithError(500);
        }

        $this->setResponse($response);
    }

    public function executeCommentsDELETE(HTTPRequest $request)
    {
        if (!$request->getExists('id'))
        {
            $this->exitWithError(400);
        }

        $id = $request->getData('id');

        $authorization = $this->getAuthorization();

        if (!$authorization)
        {
            $this->exitWithError(401);
        }

        $comment = $this->managers->getManagerOf('Comments')
            ->get($id);

        $newsId = $request->getData('newsId');

        if (!$comment || $comment->news() !== $newsId)
        {
            $this->exitWithError(
                404,
                "comment $id of news $newsId does not exist"
            );
        }

        if ($authorization->isMember() &&
            $comment['member'] != $authorization->member())
        {
            $this->exitWithError(
                401, "user does not own comment $id of news $newsId"
            );
        }

        if ($this->managers->getManagerOf('Comments')->delete($id))
        {
            $response = array(
                'message' =>
                "comment $id of news $newsId has been deleted"
            );
        }
        else
        {
            $this->exitWithError(500);
        }

        $this->setResponse($response);
    }
}
