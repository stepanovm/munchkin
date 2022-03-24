<?php

namespace tests\bin\Command\AppBuild\Compressor;

use bin\Command\AppBuild\Compressor\AssetsCompressor;
use bin\Command\AppBuild\Compressor\JsCompressor;
use PHPUnit\Framework\TestCase;

class JsCompressorTest extends TestCase
{
    public function testCompressAsset()
    {
        $jsStr = "window.addEventListener('load', function(){ alert('test'); } );";
        self::assertInstanceOf(AssetsCompressor::class, $compressor = new JsCompressor());
        self::assertIsString($compressor->compressAsset($jsStr));
    }
}