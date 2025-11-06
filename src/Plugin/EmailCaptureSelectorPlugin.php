<?php

declare(strict_types=1);

namespace Hyva\DotdigitalgroupEmail\Plugin;

use Dotdigitalgroup\Email\Helper\Config;
use Dotdigitalgroup\Email\Model\Config\Backend\EmailCaptureSelectors;
use Magento\Config\App\Config\Source\ModularConfigSource;
use Magento\Framework\Serialize\Serializer\Json;

class EmailCaptureSelectorPlugin
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ModularConfigSource
     */
    private $modularConfigSource;

    /**
     * @var array
     */
    public const XML_PATHS_HYVA_DEFAULTS = [
        'tracking/email_capture_selectors_hyva',
        'tracking/email_capture_selectors_hyva_checkout'
    ];

    /**
     * @param Json $serializer
     * @param ModularConfigSource $modularConfigSource
     */
    public function __construct(
        Json $serializer,
        ModularConfigSource $modularConfigSource
    ) {
        $this->serializer = $serializer;
        $this->modularConfigSource = $modularConfigSource;
    }

    /**
     * Extend the default email capture selectors when getting configuration value
     *
     * @param EmailCaptureSelectors $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfigurationValue(EmailCaptureSelectors $subject, array $result): array
    {
        $defaultConfig = $this->getConfigPath(Config::XML_PATH_CONNECTOR_EMAIL_CAPTURE_SELECTORS);
        $hyvaConfig = $this->getHyvaConfigurations(static::XML_PATHS_HYVA_DEFAULTS);

        if ($result === $defaultConfig) {
            return array_merge_recursive($defaultConfig, $hyvaConfig);
        }
        return $result;
    }

    /**
     * Get configuration value from config path
     *
     * @param string $path
     * @return array
     */
    private function getConfigPath(string $path): array
    {
        try {
            return $this->serializer->unserialize(
                $this->modularConfigSource->get("default/{$path}") ?? ''
            ) ?? [];
        } catch (\TypeError $e) {
            return [];
        }
    }

    /**
     * Get Hyva specific configurations from given config paths
     *
     * @param array $hyvaConfigPaths
     * @return array
     */
    private function getHyvaConfigurations(array $hyvaConfigPaths): array
    {
        return array_reduce(
            $hyvaConfigPaths,
            function ($carry, $configPath) {
                return array_merge($carry, $this->getConfigPath($configPath));
            },
            []
        );
    }
}
