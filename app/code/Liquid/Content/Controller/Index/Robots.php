<?php
declare(strict_types=1);

namespace Liquid\Content\Controller\Index;

use Liquid\Framework\App\Action\ActionInterface;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\Result\Plain;
use Liquid\Framework\Controller\ResultFactory;
use Liquid\Framework\Controller\ResultInterface;

#[Route('robots.txt', name: 'robots')]
class Robots implements ActionInterface
{
    public function __construct(
        private readonly ResultFactory $resultFactory,
    )
    {
    }

    public function execute(): ResultInterface
    {

        $result = $this->resultFactory->create(Plain::class);

        // TODO: implement this
        $rules = 'User-agent: *
Allow: /

Disallow: /cdn-cgi/

Sitemap: https://attlaz.com/sitemap.xml';
        $result->setText($rules);

        return $result;
    }
}
