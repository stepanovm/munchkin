<?php


namespace bin\Command\AppBuild;


use bin\Command\AppBuild\Compressor\AssetsCompressor;
use Psr\Container\ContainerInterface;

class AssetsListManager
{
    const RES_TYPE_JS = 'Js';
    const RES_TYPE_CSS = 'Css';

    private AssetsList $assetsList;
    private ContainerInterface $container;


    public function __construct(ContainerInterface $container, AssetsList $assetsList)
    {
        $this->assetsList = $assetsList;
        $this->container = $container;
    }


    public function getCompressedAssetFilepath(string $template, string $assetType): string
    {
        return '/' . mb_strtolower($assetType) . '/min/' . md5($template . $assetType) . '.' . mb_strtolower($assetType);
    }

    public function compressResources(string $template, array $assets, string $assetType): void
    {
        $assetFilesBody = "";
        foreach ($assets as $asset) {
            $assetFilesBody .= file_get_contents(__DIR__ . '/../../../web' . $asset);
        }

        /** @var AssetsCompressor $compressor */
        $compressor = $this->container->get('compressor_' . $assetType);

        if (empty($assetFilesBody)) {
            return;
        }

        file_put_contents(__DIR__ . '/../../../web' . $this->getCompressedAssetFilepath($template, $assetType),
            $compressor->compressAsset($assetFilesBody));
    }


    public function resolveTemplateAssets(string $template, array $templateAssets, string $assetType)
    {
        $needCompression = false;
        foreach ($templateAssets as $asset) {
            if ($this->assetsList->isAssetModified($asset, $assetType)
                    || !$this->assetsList->exitsAssetInTemplateList($template, $asset, $assetType)) {
                $needCompression = true;
            }
        }

        if ($needCompression) {
            $this->assetsList->updateTemplate($template, $assetType, $templateAssets);
            $this->compressResources($template, $templateAssets, $assetType);
        }
    }

    public function resolveAssetsList()
    {
        $this->assetsList->resolveAssetsList();
    }

    public function getCompressedAssetInfo(string $template, string $assetType): array
    {
        return [
            'filename' => $this->getCompressedAssetFilepath($template, $assetType),
            'version' => $this->assetsList->getTemplateAssetVersion($template, $assetType)
        ];
    }

    public function getAssetsList(): AssetsList
    {
        return $this->assetsList;
    }
}