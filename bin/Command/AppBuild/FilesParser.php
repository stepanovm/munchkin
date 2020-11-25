<?php


namespace bin\Command\AppBuild;


class FilesParser
{
    public function getControllerViews($file): array
    {
        $controllerString = file_get_contents($file);
        $pattern = "~render\\('(.*?)'~";
        if (preg_match_all($pattern, $controllerString, $matches)) {
            return array_unique($matches[1]);
        }
        return [];
    }
}