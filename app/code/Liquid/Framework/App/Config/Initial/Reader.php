<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config\Initial;

use Liquid\Framework\Config\FileResolverInterface;

class Reader
{
    /**
     * Scope priority loading scheme
     *
     * @var array
     */
    private array $_scopePriorityScheme = ['global'];

    public function __construct(
        private readonly FileResolverInterface $fileResolver,
        private readonly string                $fileName = 'config.yml'
    )
    {

    }

    /**
     * Read configuration scope
     */
    public function read(): array
    {
        $fileList = [];
        foreach ($this->_scopePriorityScheme as $scope) {
            $directories = $this->fileResolver->get($this->fileName, $scope);
            foreach ($directories as $key => $directory) {
                $fileList[$key] = $directory;
            }
        }

        if (!count($fileList)) {
            return [];
        }

//        /** @var \Liquid\Framework\Config\Dom $domDocument */
//        $domDocument = null;
//        foreach ($fileList as $file) {
//            try {
//                if (!$domDocument) {
//                    $domDocument = $this->domFactory->createDom(['xml' => $file, 'schemaFile' => $this->_schemaFile]);
//                } else {
//                    $domDocument->merge($file);
//                }
//            } catch (\Liquid\Framework\Config\Dom\ValidationException $e) {
//                throw new \Liquid\Framework\Exception\LocalizedException(
//                    new \Liquid\Framework\Phrase(
//                        'The XML in file "%1" is invalid:' . "\n%2\nVerify the XML and try again.",
//                        [$file, $e->getMessage()]
//                    )
//                );
//            }
//        }
//
//        $output = [];
//        if ($domDocument) {
//            $output = $this->_converter->convert($domDocument->getDom());
//        }
//        return $output;
        return [];
    }
}
