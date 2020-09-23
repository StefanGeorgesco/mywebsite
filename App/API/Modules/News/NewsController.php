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

            try
            {
                $pagination = new Pagination(
                    $this->app,
                    $newsManager,
                    $nombreNews
                );
            }
            catch (\Exception $e)
            {
                $this->exitWithError(404, 'this page does not exist');
            }

            $newsList = $newsManager->getList(
                $pagination->getOffset(),
                $nombreNews
            );

            $response = array_map(
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
            );
        }

        $this->setResponse($response);
    }

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

    public function executeNewsPATCH(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();

        if (!$authorization || !$authorization->isAdmin())
        {
            $this->exitWithError(401, 'user is not admin');
        }

        if (!$request->getExists('id'))
        {
            $this->exitWithError(400, 'news id not provided');
        }

        $newsId = $request->getData('id');

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
        $authorization = $this->getAuthorization();

        if (!$authorization || !$authorization->isAdmin())
        {
            $this->exitWithError(401, 'user is not admin');
        }

        if (!$request->getExists('id'))
        {
            $this->exitWithError(400, 'news id not provided');
        }

        $newsId = $request->getData('id');

        if (!$this->managers->getManagerOf('News')->get($newsId))
        {
            $this->exitWithError(404, "news $newsId does not exist");
        }

        if ($this->managers->getManagerOf('News')->delete($newsId) &&
            $this->managers->getManagerOf('Comments')->deleteFromNews($newsId))
        {
            $response = array(
                'message' =>
                "news $newsId and attached cooments have been deleted"
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

        $data = $request->requestBodyData();

        if (!isset($data['news']))
        {
            $this->exitWithError(400, 'news id not provided');
        }

        $newsId = $data['news'];

        if (!is_numeric($newsId) || $newsId < 0)
        {
            $this->exitWithError(400, 'news id incorrect');
        }

        if (!$this->managers->getManagerOf('News')->get($newsId))
        {
            $this->exitWithError(404, "news $newsId does not exist");
        }

        $data['member'] = $authorization->member();

        $comment = new Comment($data);

        $formBuilder = new CommentFormBuilder($comment);
        $formBuilder->build();
        $form = $formBuilder->form();

        if (!$form->isValid())
        {
            $this->exitWithError(400, 'comment data incorrect', $form->errors());
        }

        if ($this->managers->getManagerOf('Comments')->save($comment))
        {
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

    public function executeCommentsPATCH(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();

        if (!$authorization)
        {
            $this->exitWithError(401);
        }

        if (!$request->getExists('id'))
        {
            $this->exitWithError(400, 'comment id not provided');
        }

        $commentId = $request->getData('id');

        $storedComment = $this->managers->getManagerOf('Comments')
            ->get($commentId);

        if (!$storedComment)
        {
            $this->exitWithError(404, "comment $commentId does not exist");
        }

        if ($authorization->isMember() &&
            $storedComment['member'] != $authorization->member())
        {
            $this->exitWithError(401, "user does not own comment $commentId");
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
        $authorization = $this->getAuthorization();

        if (!$authorization)
        {
            $this->exitWithError(401);
        }

        if (!$request->getExists('id'))
        {
            $this->exitWithError(400, 'comment id not provided');
        }

        $commentId = $request->getData('id');
        $comment = $this->managers->getManagerOf('Comments')->get($commentId);

        if (!$comment)
        {
            $this->exitWithError(404, "comment $commentId does not exist");
        }

        if ($authorization->isMember() &&
            $comment['member'] != $authorization->member())
        {
            $this->exitWithError(401, "user does not own comment $commentId");
        }

        if ($this->managers->getManagerOf('Comments')->delete($commentId))
        {
            $response = array(
                'message' =>
                "comment $commentId has been deleted"
            );
        }
        else
        {
            $this->exitWithError(500);
        }

        $this->setResponse($response);
    }
}
