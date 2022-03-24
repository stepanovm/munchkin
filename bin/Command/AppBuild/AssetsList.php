<?php

namespace bin\Command\AppBuild;

use Lynxx\Lynxx;
use Psr\Container\ContainerInterface;

class AssetsList
{
    private array $assetsList;

    public function __construct()
    {
        $this->init();
    }


    private function init(): void
    {
        $emptyList = [
            'all_assets' => [
                AssetsListManager::RES_TYPE_CSS => [],
                AssetsListManager::RES_TYPE_JS => []
            ],
            'templates_assets' => []
        ];
        try {
            if ($r = unserialize(@file_get_contents(__DIR__ . '/assets_list'))) {
                $this->assetsList = $r;
            } else {
                $this->assetsList = $emptyList;
            }
        } catch (\Throwable $ex) {
            $this->assetsList = $emptyList;
        }
    }


    /**
     * @param string $templatePath
     * @return bool
     */
    public function hasTemplate(string $templatePath): bool
    {
        return array_key_exists($templatePath, $this->assetsList['templates_assets']);
    }


    /**
     * @param string $templatePath
     * @param string $assetType
     * @param array $templateAssets
     * @return void
     */
    public function updateTemplate(string $templatePath, string $assetType, array $templateAssets): void
    {
        $list =& $this->assetsList['templates_assets'][$templatePath][$assetType];

        if ($this->hasTemplate($templatePath)) {
            $list['version'] += 1;
        } else {
            $list['version'] = 1;
        }

        $list['assets'] = $templateAssets;
    }


    /**
     * @return array
     */
    public function getAllAssetsList(): array
    {
        return $this->assetsList['all_assets'];
    }


    /**
     * @param string $templatePath
     * @return array|null
     */
    public function getTemplateAssets(string $templatePath): ?array
    {
        if (!$this->hasTemplate($templatePath)) {
            return null;
        }
        return $this->assetsList['templates_assets'][$templatePath];
    }


    /**
     * @return void
     */
    private function saveAssetsList(): void
    {
        file_put_contents(__DIR__ . '/assets_list', serialize($this->assetsList));
    }


    public function isAssetModified(string $asset, string $assetType): bool
    {
        $lastModified = filemtime(__DIR__ . '/../../../web' . $asset);

        $list =& $this->assetsList['all_assets'][$assetType];

        // new resource
        if (!array_key_exists($asset, $list)) {
            $list[$asset] = [
                'modifiedTime' => $lastModified,
                'version' => 1
            ];
        }

        // already checked
        if (array_key_exists('modifiedTime', $list[$asset])) {
            return true;
        }

        // check res date
        if ($list[$asset]['lastModified'] < $lastModified) {
            $list[$asset]['modifiedTime'] = $lastModified;
            $list[$asset]['version']++;
            return true;
        }

        return false;
    }

    public function resolveAssetsList(): void
    {
        foreach ($this->assetsList['all_assets'] as &$assetType) {
            foreach ($assetType as &$assetInfo) {
                if (array_key_exists('modifiedTime', $assetInfo)) {
                    $assetInfo['lastModified'] = $assetInfo['modifiedTime'];
                    unset($assetInfo['modifiedTime']);
                }
            }
        }
        $this->saveAssetsList();
    }

    public function exitsAssetInTemplateList(string $template, string $asset, string $assetType): bool
    {
        if (array_key_exists($assetType, $this->assetsList['templates_assets'][$template])) {
            if (in_array($asset, $this->assetsList['templates_assets'][$template][$assetType]['assets'])) {
                return true;
            }
        }
        return false;
    }

    public function getTemplateAssetVersion($template, $assetType): ?int
    {
        try {
            if (array_key_exists($assetType, $this->assetsList['templates_assets'][$template])) {
                return $this->assetsList['templates_assets'][$template][$assetType]['version'];
            }
            return null;
        } catch (\Throwable $ex) {
            return null;
        }
    }

    public function removeFromAssetsList(string $key, string $assetType)
    {
        if (array_key_exists($key, $this->assetsList['all_assets'][$assetType])) {
            unset($this->assetsList['all_assets'][$assetType][$key]);
        }
    }

    public function removeTemplate(string $template)
    {
        if (array_key_exists($template, $this->assetsList['templates_assets'])) {
            unset($this->assetsList['templates_assets'][$template]);
        }
    }
}