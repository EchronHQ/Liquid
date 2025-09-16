<?php

declare(strict_types=1);

namespace Liquid\UrlRewrite\Controller;

use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Content\Model\Segment\SegmentManager;
use Liquid\Core\Helper\Resolver;
use Liquid\Framework\App\Action\ActionFactory;
use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Action\ForwardAction;
use Liquid\Framework\App\Action\RedirectAction;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Response\Response;
use Liquid\Framework\App\Router\RouterInterface;
use Liquid\Framework\Controller\Result\Forward;
use Liquid\Framework\Controller\Result\Redirect;
use Liquid\UrlRewrite\Model\Resource\UrlRewrite;
use Liquid\UrlRewrite\Model\Resource\UrlRewriteType;
use Liquid\UrlRewrite\Model\UrlFinderInterface;
use Psr\Log\LoggerInterface;

class Router implements RouterInterface
{
    public function __construct(
        private readonly UrlFinderInterface $urlFinder,
        private readonly SegmentManager     $segmentManager,
        private readonly Response           $response,
        private readonly Resolver           $resolver,
        private readonly LoggerInterface    $logger,
        private readonly ActionFactory      $actionFactory
    )
    {

    }

    public function match(Request $request): ActionInterface|null
    {
        $rewrite = $this->getRewrite(
            $request->getPathInfo(),
            $this->segmentManager->getSegment()->getId()
        );
        if ($rewrite === null) {
            return null;
        }
        $this->logger->info('Found url rewrite: `' . $rewrite->getRequestPath() . '` > `' . $rewrite->getTargetPath() . '`');
        if ($rewrite->getRedirectType() === UrlRewriteType::INTERNAL) {
            $this->logger->debug('Rewrite matched (internal)', [
                'Origin' => $request->getPathInfo(),
                'Target' => $rewrite->getTargetPath(),
                //'Action' => \get_class($action),
            ]);
            $request->setPathInfo($rewrite->getTargetPath(), $rewrite);

            // Forward the request after rewriting the path info to handle it further
            return $this->actionFactory->create(ForwardAction::class, ['forward' => new Forward($request)]);
        }

        $url = $this->resolver->getUrl($rewrite->getTargetPath());
        $this->response->setRedirect($url, $rewrite->getRedirectType()->value);
        $request->setMatched(true);

        return $this->actionFactory->create(RedirectAction::class, ['redirect' => new Redirect($url, $rewrite->getRedirectType()->value)]);

    }

    protected function getRewrite(string $requestPath, SegmentId $segmentId): UrlRewrite|null
    {
        return $this->urlFinder->findOneByData([UrlRewrite::REQUEST_PATH => ltrim($requestPath, '/'), UrlRewrite::SEGMENT_ID => $segmentId]);
    }
}
