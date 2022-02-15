<?php


namespace Lynxx\AssetsHelpers;


abstract class ResourceManager {

    /**
     * return string html-code for page <b>head</b> tag
     */
    abstract protected function getFilepathsHtml();
    abstract protected function getCompressedFileHtml();
    abstract protected function prepareAndGetCompressedFileHtml();


    public function getHtml()
    {
        if(Config::get('application_mode') === Config::APP_MODE_DEV){
            return $this->getFilepathsHtml();
        }

        $compressed_file = $this->home_dir . $this->min_target_dir . $this->compressed_filename;
        if(file_exists($compressed_file)
            && filemtime($compressed_file) >= $this->allResourcesLastModified){
            return $this->getCompressedFileHtml();
        } else {
            return $this->prepareAndGetCompressedFileHtml();
        }
    }

    /**
     * check all files in list.
     * set object fileds: <b>compressed_version</b>, <b>compressed_filename</b>
     * @return type
     */
    public function processResourceFiles()
    {
        $str = '';
        foreach ($this->resource_file_paths as $resource_path){
            $last_modified = filemtime($this->home_dir . $resource_path);
            if($this->allResourcesLastModified < $last_modified){
                $this->allResourcesLastModified = $last_modified;
            }
            $this->compressed_version += $this->mWebResources->getResourceVersion($resource_path, $this->resource_type, $last_modified);
            $str .= preg_replace(array('/\/css\//', '/\.css/', '/\/js\//', '/\.js/'), '', $resource_path);
        }
        $this->compressed_filename = md5($str).'.'.$this->resource_type;
    }

}
