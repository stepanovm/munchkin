<?php


namespace bin\Command\AppBuild;


use phpDocumentor\Reflection\Types\Boolean;

class ResourcesListManager
{
    const RES_TYPE_JS = 'Js';
    const RES_TYPE_CSS = 'Css';

    private array $compressedList;
    private array $resourcesList;


    public function __construct()
    {
        $this->resourcesList = $this->initResourcesList();
    }

    /**
     * @return array
     */
    public function getResourcesList(): array
    {
        return $this->resourcesList;
    }


    private function initResourcesList(): array
    {
        $emptyList = ['Css' => [], 'Js' => []];
        try {
            if ($r = unserialize(@file_get_contents('resources'))) {
                return $r;
            } else {
                return $emptyList;
            }
        } catch (\Throwable $ex) {
            return $emptyList;
        }
    }


    public function isModified(string $resource, string $resType): bool
    {
        $lastModified = filemtime(__DIR__ . '/../../../web' . $resource);

        $list =& $this->resourcesList[$resType];

        // new resource
        if (!array_key_exists($resource, $list)) {
            $list[$resource] = [
                'modifiedTime' => $lastModified,
                'version' => 1
            ];
        }

        // already checked
        if (array_key_exists('modifiedTime', $list[$resource])) {
            return true;
        }

        // check res date
        if ($list[$resource]['lastModified'] < $lastModified) {
            $list[$resource]['modifiedTime'] = $lastModified;
            $list[$resource]['version']++;
            return true;
        }

        return false;
    }


    public function isInCompressedList(string $template): bool
    {
        return false;
    }

    public function compressResources(string $template, array $resources): void
    {

    }

    public function processResources(string $template, array $templateResources, string $resType)
    {
        $needCompression = false;
        foreach ($templateResources as $resource) {
            if($this->isModified($resource, $resType)) {
                $needCompression = true;
            }
        }

        if ($needCompression || !$this->isInCompressedList($template)) {
            $this->compressResources($template, $templateResources);
        }
    }

}