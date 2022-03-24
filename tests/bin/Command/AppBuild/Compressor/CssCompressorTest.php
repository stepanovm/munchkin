<?php

namespace tests\bin\Command\AppBuild\Compressor;

use bin\Command\AppBuild\Compressor\AssetsCompressor;
use bin\Command\AppBuild\Compressor\CssCompressor;
use PHPUnit\Framework\TestCase;

class CssCompressorTest extends TestCase
{
    public function testCompressAsset()
    {
        $jsStr = ".lynx-header {
            font-size: 26px;
        }";
        self::assertInstanceOf(AssetsCompressor::class, $compressor = new CssCompressor());
        self::assertIsString($compressor->compressAsset($jsStr));
    }
}