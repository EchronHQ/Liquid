<?php
declare(strict_types=1);

namespace Liquid\Framework\Exception;

use Liquid\Framework\App\Response\HttpResponse;
use Liquid\Framework\App\Response\HttpResponseCode;
use Liquid\Framework\Filesystem\Filesystem;
use Liquid\Framework\Filesystem\Path;

/**
 * Handles and output error views
 */
class ErrorProcessor
{
    public function __construct(
        private readonly Filesystem $filesystem,
    )
    {
    }

    public function processErrorReport(HttpResponse $response, array $reportData): void
    {
        $response->setHeader('Content-Type', 'text/html');
        $response->setHttpResponseCode(HttpResponseCode::STATUS_CODE_500);
        $title = 'There has been an error processing your request';

        $reportUrl = $this->saveReport($reportData);

        $errorPath = $this->filesystem
            ->getDirectoryRead(Path::PUB)
            ->getAbsolutePath('errors/report.php');

        $message = 'Report ' . $reportData['report_id'];

        $body = $this->getFile($errorPath, ['title' => $title, 'code' => 500, 'message' => $message]);

        $response->setBody($body);
        $response->sendResponse();
    }

    public function processError(HttpResponse $response, string|int $code, string $message): void
    {

        $response->setHeader('Content-Type', 'text/html');
        switch ($code) {
            case 404:
                $response->setHttpResponseCode(HttpResponseCode::NOT_FOUND);
                $title = 'Error 404: Not Found';
                break;
            case 503:
                $response->setHttpResponseCode(HttpResponseCode::STATUS_CODE_503);
                $title = 'Error 503: Service Unavailable';
                break;
            case 500:
            default:
                $response->setHttpResponseCode(HttpResponseCode::STATUS_CODE_500);
                $title = 'There has been an error processing your request';
        }

        $errorPath = $this->filesystem
            ->getDirectoryRead(Path::PUB)
            ->getAbsolutePath('errors/generic.php');

        $body = $this->getFile($errorPath, ['title' => $title, 'code' => $code, 'message' => $message]);

        $response->setBody($body);
        $response->sendResponse();
    }

    /**
     * Create report
     *
     * @param array $reportData
     * @return string
     */
    public function saveReport(array $reportData): string
    {
//        $this->reportId = $reportData['report_id'];
//        $this->_reportFile = $this->getReportPath(
//            $this->getReportDirNestingLevel($this->reportId),
//            $this->reportId
//        );
//        $reportDirName = dirname($this->_reportFile);
//        if (!file_exists($reportDirName)) {
//            @mkdir($reportDirName, 0777, true);
//        }
//        $this->_setReportData($reportData);
//
//        @file_put_contents($this->_reportFile, $this->serializer->serialize($reportData). PHP_EOL);
//
//        if (isset($reportData['skin']) && self::DEFAULT_SKIN != $reportData['skin']) {
//            $this->_setSkin($reportData['skin']);
//        }
//        $this->_setReportUrl();
//
//        return $this->reportUrl;
        return '';
    }

    private function getFile(string $path, array $data = []): string
    {
        if (\file_exists($path)) {
            \ob_start();
            require $path;
            return \ob_get_clean();
        }
        return '';

    }
}
