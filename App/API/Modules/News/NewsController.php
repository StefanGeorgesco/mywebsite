<?php
namespace App\API\Modules\News;

use \OCFram\APIController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \Entity\News;
use \Entity\Comment;

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
        $authorization = $this->getAuthorization();

        if (!$authorization)
        {
            $this->exitWithError(401);
        }

        if ($request->getExists('id'))
        {
            if (!$authorization->isMember())
            {
                $this->exitWithError(401, 'user is not a member');
            }

            $newsId = $request->getData('id');

            if (!$this->managers->getManagerOf('News')->get($newsId))
            {
                $this->exitWithError(404, "news $newsId does not exist");
            }

            $data = array_merge(
                $request->requestBodyData(),
                [
                    'news' => $newsId,
                    'member' => $authorization->member()
                ]
            );

            $comment = new Comment($data);

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
        }
        else
        {
            if (!$authorization->isAdmin())
            {
                $this->exitWithError(401, 'user is not admin');
            }

            $news = new News($request->requestBodyData());

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
        }

        $this->setResponseCode(201);
        $this->setResponse($response);
    }
}
