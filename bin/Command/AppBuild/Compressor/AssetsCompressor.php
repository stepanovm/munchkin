<?php

namespace bin\Command\AppBuild\Compressor;

interface AssetsCompressor
{
    public function compressAsset(string $assetBody): string;
}