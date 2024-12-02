<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution\Types;

class ResolveTypesPool
{
    /**
     * Supported types of fallback rules
     */
    public const TYPE_FILE = 'file';
    public const TYPE_LOCALE_FILE = 'locale';
    public const TYPE_TEMPLATE_FILE = 'template';
    public const TYPE_STATIC_FILE = 'static';
    public const TYPE_EMAIL_TEMPLATE = 'email';

    private array $rules = [];

    public function __construct(
        private readonly ResolveThemeFilesFactory  $resolveThemeFilesFactory,
        private readonly ResolveModuleFilesFactory $resolveModuleFilesFactory,
        private readonly ResolveBasicFilesFactory  $simpleFactory,
        private readonly ModularSwitchFactory      $modularSwitchFactory
    )
    {
    }

    /**
     * Get rule by type
     *
     * @param string $type
     * @return ResolveTypeInterface
     * @throws \InvalidArgumentException
     */
    public function getRule(string $type): ResolveTypeInterface
    {
        if (isset($this->rules[$type])) {
            return $this->rules[$type];
        }
        switch ($type) {
//            case self::TYPE_FILE:
//                $rule = $this->createFileRule();
//                break;
//            case self::TYPE_LOCALE_FILE:
//                $rule = $this->createLocaleFileRule();
//                break;
            case self::TYPE_TEMPLATE_FILE:
                $rule = $this->createTemplateFileRule();
                break;
//            case self::TYPE_STATIC_FILE:
//                $rule = $this->createViewFileRule();
//                break;
//            case self::TYPE_EMAIL_TEMPLATE:
//                $rule = $this->createEmailTemplateFileRule();
//                break;
            default:
                throw new \InvalidArgumentException("Fallback rule '$type' is not supported");
        }
        $this->rules[$type] = $rule;
        return $this->rules[$type];
    }

    /**
     * Retrieve newly created fallback rule for template files
     *
     * @return ResolveTypeInterface
     */
    protected function createTemplateFileRule(): ResolveTypeInterface
    {
        return $this->modularSwitchFactory->create(
            [
                'ruleNonModular' =>
                    $this->resolveThemeFilesFactory->create(
                        ['resolver' => $this->simpleFactory->create(['pattern' => "<theme_dir>/template"])]
                    ),
                'ruleModular' => new Composite(
                    [
                        $this->resolveThemeFilesFactory->create(
                            ['resolver' => $this->simpleFactory->create(['pattern' => "<theme_dir>/<module_name>/template"])]
                        ),
                        $this->resolveModuleFilesFactory->create(
                            ['resolver' => $this->simpleFactory->create(['pattern' => "<module_dir>/view/<area>/template"])]
                        ),
                        $this->resolveModuleFilesFactory->create(
                            ['resolver' => $this->simpleFactory->create(['pattern' => "<module_dir>/view/base/template"])]
                        ),
                    ]
                )]
        );
    }
}
