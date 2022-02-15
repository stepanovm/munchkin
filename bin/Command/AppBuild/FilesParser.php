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

    /**
     * @param string $viewFileName
     * @param string $resType Css|Js
     * @return array
     */
    public function getTemplateResources(string $viewFile, string $resType): array
    {
        $resArray = [];

        $resText = file(__DIR__ . "/../../../app/templates/" . $viewFile);

        if(!$resText){
            throw new \Exception('template '.$viewFile.' not found');
        }
        foreach ($resText as $row) {
            if(strpos($row, 'register'.$resType) !== false && strpos($row, 'nocompress') === false) {
                preg_match("~^.*?['\"](.*?)['\"].*?$~", $row, $m);
                if(!file_exists(__DIR__ . '/../../../web' . $m[1])) {
                    throw new \Exception($m[1] . ' resource file not found');
                }
                $resArray[] = $m[1];
                continue;
            }

            if(strpos($row, 'setLayout') !== false) {
                preg_match("~^.*?['\"](.*?)['\"].*?$~", $row, $m);
                $resArray = array_merge($resArray, $this->getTemplateResources('layout/' . $m[1], $resType));
                continue;
            }

            if(strpos($row, 'registerComponent') !== false) {
                preg_match("~^.*?['\"].*?['\"].*?['\"](.*?)['\"].*?$~", $row, $m);
                $resArray = array_merge($resArray, $this->getTemplateResources($m[1], $resType));
                continue;
            }
        }

        return $resArray;
    }
}