<?php
declare(strict_types=1);

namespace Liquid\Framework\App;


use Liquid\Content\Console\BuildStatic;
use Liquid\Content\Console\ClearAssetCache;
use Liquid\Content\Console\ListUrlRewrites;
use Liquid\Content\Repository\LocaleRepository;
use Liquid\Core\Console\ClearCache;
use Liquid\Core\Console\ShowCache;
use Liquid\Core\Console\SystemInfo;
use Liquid\Core\Console\TestCache;
use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\App\Area\AreaList;
use Liquid\Framework\App\Console\Response;
use Liquid\Framework\App\Response\ResponseInterface;
use Liquid\Framework\ObjectManager\ConfigLoader;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Seo\Console\SeoReportCommand;
use Liquid\Seo\Console\SitemapDrawCommand;
use Liquid\Seo\Console\SitemapGenerateCommand;
use Magento\Framework\App\Area;
use Symfony\Component\Console\Application as ConsoleApplication;

class CliApp implements AppInterface
{

    public function __construct(
        private readonly State                  $state,
        private readonly Console\Response       $response,
        private readonly ObjectManagerInterface $objectManager,
        private readonly AreaList               $areaList,
    )
    {

    }

    public function launch(): ResponseInterface
    {
        $this->state->setAreaCode(AreaCode::Cli);

        $configLoader = $this->objectManager->get(ConfigLoader::class);
        $this->objectManager->configure($configLoader->load(AreaCode::Cli));
//        $areaCode = $this->areaList->getArea(AreaCode::Cli);
//        $this->state->setAreaCode($areaCode->areaCode);
        $this->objectManager->configure($configLoader->load(AreaCode::Cli));
        //   $this->beforeRun();

        /** @var LocaleRepository $localeRepository */
        // $localeRepository = $this->objectManager->get(LocaleRepository::class);
        // $this->getConfig()->setLocale($localeRepository->getDefault(), false);

        $consoleApplication = new ConsoleApplication();

        //TODO: get commands through attribute tag

        $consoleApplication->add($this->objectManager->get(ListUrlRewrites::class));

        $consoleApplication->add($this->objectManager->get(SeoReportCommand::class));
        $consoleApplication->add($this->objectManager->get(SitemapGenerateCommand::class));
        $consoleApplication->add($this->objectManager->get(SitemapDrawCommand::class));

        $consoleApplication->add($this->objectManager->get(ClearCache::class));
        $consoleApplication->add($this->objectManager->get(ShowCache::class));
        $consoleApplication->add($this->objectManager->get(TestCache::class));

        $consoleApplication->add($this->objectManager->get(ClearAssetCache::class));
        $consoleApplication->add($this->objectManager->get(BuildStatic::class));

        $consoleApplication->add($this->objectManager->get(SystemInfo::class));

        $consoleApplication->run();

        $this->response->setCode(Response::SUCCESS);
        return $this->response;
    }
}
