<?php

namespace bin\Command\AppBuild\Compressor;

use WebSharks\JsMinifier\Core;

class JsCompressor implements AssetsCompressor
{
    public function compressAsset(string $assetBody): string
    {
        return Core::compress($assetBody);
    }

}