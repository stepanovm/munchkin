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
     * @param string $assetType Css|Js
     * @return array
     */
    public function getTemplateAssets(string $viewFile, string $assetType): array
    {
        $resArray = [];

        $resText = file(__DIR__ . "/../../../app/templates/" . $viewFile);

        if(!$resText){
            throw new \Exception('template '.$viewFile.' not found');
        }
        foreach ($resText as $row) {
            if(strpos($row, 'register'.$assetType) !== false
                    && strpos($row, 'nocompress') === false
                    && !($assetType === AssetsListManager::RES_TYPE_JS && strpos($row, 'async') === false)) {
                preg_match("~^.*?['\"](.*?)['\"].*?$~", $row, $m);
                if(!file_exists(__DIR__ . '/../../../web' . $m[1])) {
                    throw new \Exception($m[1] . ' asset file not found');
                }
                $resArray[] = $m[1];
                continue;
            }

            if(strpos($row, 'setLayout') !== false) {
                preg_match("~^.*?['\"](.*?)['\"].*?$~", $row, $m);
                $resArray = array_merge($resArray, $this->getTemplateAssets('layout/' . $m[1], $assetType));
                continue;
            }

            if(strpos($row, 'registerComponent') !== false) {
                preg_match("~^.*?['\"].*?['\"].*?['\"](.*?)['\"].*?$~", $row, $m);
                $resArray = array_merge($resArray, $this->getTemplateAssets($m[1], $assetType));
            }
        }

        return $resArray;
    }
}