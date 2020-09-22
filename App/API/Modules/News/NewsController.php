<?php
namespace App\API\Modules\News;

use \OCFram\APIController;
use \OCFram\HTTPRequest;
use \OCFram\Pagination;
use \Entity\News;
use \Entity\Comment;

class NewsController extends APIController
{
    public function executeNews(HTTPRequest $request)
    {
        $authorization = $this->getAuthorization();

        $newsManager = $this->managers->getManagerOf('News');
        $commentsManager = $this->managers->getManagerOf('Comments');

        $switchCase = [$request->method(), $request->getExists('id')];

        switch ($switchCase)
        {
            case ['GET', true]:
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
                    $this->app->httpResponse()->jsonError(404);
                }
                break;

            case ['GET', false]:
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
                    $this->app->httpResponse()->jsonError(404);
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
                break;

            default:
                $this->app->httpResponse()->jsonError(400);
        }

        $this->setResponse($response);
    }
}
