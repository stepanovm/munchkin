<?php

namespace bin\Command\AppBuild\Compressor;

use tubalmartin\CssMin\Minifier as CSSmin;

class CssCompressor implements AssetsCompressor
{
    public function compressAsset(string $assetBody): string
    {
        $compressor = new CSSmin;
        $compressor->setLineBreakPosition(1000);
        return $compressor->run($assetBody);
    }

}