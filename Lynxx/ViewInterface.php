<?php


namespace Lynxx;


interface ViewInterface
{
    public function registerJs(string $path): void;
    public function registerJsAsync(string $path): void;

    public function registerCss(string $path): void;

    public function render(string $view);

    public function  registerHeadsTag(string $tag): void;
}