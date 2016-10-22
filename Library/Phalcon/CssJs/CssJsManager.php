<?php
namespace Phalcon\CssJs;
/*
 * Copyright (c) 2016, Leo van Elburg <leo@lvesoft.nl>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

use Phalcon\Mvc\User\Component;
use Phalcon\Config;

/**
 * LveSoft\CssJs\CssJsManager
 * CssJsManager centrally manages css and js  files.
 * 
 * Css and JS files ar merged and packed, to be served to the client, thus minimizing client - server
 * communication.
 * 
 * A note om merging and caching of files. The generated filename will be:
 * - MD5(concat of all filenames including location + timestamp file modified)_appname.css 
 * (or .js). The concatenation occurs in order of the reported files. So with 2 sequenses of files 
 * with the same file but in a different order there will be 2 result files. Ie the second request 
 * will not be served from cache! (Which is logical, given de cascading nature of these type of files!)
 *
 * @author Leo van Elburg <leo@lvesoft.nl>
 */
class CssJsManager extends Component {
    /**
     * Array of css files to load (and/or merge) for this request.
     * @var array
     */
    private $_cssArr = [];
    /**
     * Array of js files to load (and/or merge) for this request.
     * @var array
     */
    private $_jsArr = [];
    /**
     * Indicates wheter or not merged files must be cached and served from cache. If SetMergeFiles(false)
     * is set, the value of the property has no effect.
     * @var bool
     */
    private $_UseCache = false;
    /**
     * Indicate wheter or not files (js and css) must be merged and pakked.
     * @var bool
     */
    private $_MergeFiles = false;
    /**
     * Directory where public available own css files are stored and merged and compressed css files
     * will be stored.
     * @var string 
     */
    private $_cssDir;
    /**
     * Directory where public available own js files are stored and merged and compressed js files
     * will be stored.
     * @var string 
     */
    private $_jsDir;
    
    /**
     * Get the js files embedded in <script> tags.
     * Depending on the settings of the instance it will be a concatenated file and / or served from 
     * cache or a string with the added files in <script> tags.
     * @param string $fileName  This sgtring will be part of the created concatenated file.
     * @return string 
     */
    public function jsAsHtml ($fileName = '') {
        return $this->prepareOutput('js', $fileName);
    }
    
    /**
     * Get the css files embedded in <link> tags.
     * Depending on the settings of the instance it will be a concatenated file and / or served from 
     * cache or a string with the added files in <link> tags.
     * @param string $fileName  This sgtring will be part of the created concatenated file.
     * @return string 
     */
    public function cssAsHtml($fileName = '') {
        return $this->prepareOutput('css', $fileName);
    }
    
    /**
     * This method creates the output depending on the settings of the instance, for the requested 
     * output type ($filetype is 'css' or 'js')
     * @param string $filetype
     * @param string $file
     * @return string
     */
    private function prepareOutput($filetype = 'css', $file = '') {
        $config = $this->di->getConfig();
        if ($filetype == 'css') {
            $skel = '<link rel="stylesheet" href="http://%s">';
            $fArr = $this->_cssArr;
            $prefix = $this->_cssDir;
        }
        else {
            $skel = '<script src="http://%s"></script>';
            $fArr = $this->_jsArr;
            $prefix = $this->_jsDir;
        }
        $res = '';
        if (!$this->_MergeFiles) {
            foreach ($this->fArr as $tFile) {
                $res .= sprintf($skel, $tFile) . PHP_EOL;
            }
            return $res;
        }
        else {
            $name = $this->getFileName($filetype, $file);
            $fileName = $config->CssJsManager->publicDir . $prefix . '/' . $name;
            $res = $config->application->publicUrl . $prefix . '/' . $name;
            if (!file_exists($fileName)) {
                $content = '';
                foreach ($fArr as $tFile) {
                    $content .= file_get_contents($tFile) . PHP_EOL;
                }
                $content = $this->compress($content);
                $fp = fopen($fileName, 'w');
                fwrite($fp, $content);
                fclose($fp);
            }
            return sprintf($skel, $res);
        }
    }
    
    protected function compress($buffer) {
            /* remove comments */
            $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
            /* remove tabs, spaces, newlines, etc. */
            $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
            return $buffer;
        }
    
    /**
     * Returns the added js files as an array.
     * @return array 
     */
    public function jsAsArray() {
        return $this->_jsArr;
    }
    
    /**
     * Returns the added css files as an array.
     * @return array 
     */
    public function cssAsArray() {
        return $this->_cssArr;
    }
    
    /**
     * This function adds an (array of) css file(s) to the files to load this request.
     * 
     * The css file can be relative (css/file.css) or absolute (http://a.b.c/bootstrap.min.css).
     * If the file does not exist or does not have content, a warning is written to logging system.
     * Relatives path are finally outputted as absolute paths so they are expected to be at:
     * You'reRoot.com/public/... (normaly css/...)
     * 
     * TODO: check if resource exist and write warning if not. 
     * @param array|string $cssArr
     */
    public function addCss($cssArr) {
        if (is_array($cssArr)) {
            $this->_cssArr = array_merge($this->_cssArr, $cssArr);
        }
        else {
            if (is_string($cssArr)) {
                $this->_cssArr[] = $cssArr;
            }
            else {
                if ((is_object($cssArr)) and ($cssArr instanceof Config)) {
                    $this->_cssArr = array_merge($this->_cssArr, $cssArr->toArray());
                }
            }
        }
    }
    
    /**
     * This function adds an (array of) js file(s) to the files to load this request.
     * 
     * The js file can be relative (js/file.js) or absolute (http://a.b.c/bootstrap.min.js).
     * If the file does not exist or does not have content, a warning is written to logging system.
     * Relatives path are finally outputed as absolute paths so they are expected to be at:
     * You'reRoot.com/public/... (normaly js/...)
     * 
     * TODO: check if resource exist and write warning if not. 
     * @param array|string $jsArr
     */
    public function addJs($jsArr) {
        if (is_array($jsArr)) {
            $this->_jsArr = array_merge($this->_jsArr, $jsArr);
        }
        else {
            if (is_string($jsArr)) {
                $this->_jsArr[] = $jsArr;
            }
        }
    }
    
    public function setPublicJsDir($directory) {
        $this->_jsDir = $directory;
    }
    
    public function setPublicCssDir($directory) {
        $this->_cssDir = $directory;
    }
    
    public function getPublicJsDir() {
        return $this->_jsDir;
    }
    
    public function getPublicCssDir() {
        return $this->_cssDir;
    }

    /**
     * Set whether to merge the JS an CSS files to one file when the output is request.
     * @param bool $merge
     */
    public function setMergeFiles($merge){
        if (is_bool($merge)) {
            $this->_MergeFiles = $merge;
        }
    }
    
    /**
     * Set wheter to serve te result from an possibly cached file or to forge recreation of the file.
     * @param type $cache
     */
    public function setUseCache($cache){
        if (is_bool($cache)) {
            $this->_UseCache = $cache;
        }        
    }
    
    /**
     * Get whether the object will merge the files when the output is requested.
     * @return bool
     */
    public function getMergeFiles(){
        return $this->_MergeFiles;
    }
    
    /**
     * Gets whether the the object will serve a possible cached file or forcefully recreates the result file.
     * @return bool
     */
    public function getUseCache(){
        return $this->_UseCache;
    }
    
    /**
     * This function crates an unique filename for a merged CSS or JS File.
     * 
     * When $fileType is 'js' the name for the resulting js will be created otherwise the JS filename
     * is created.
     * 
     * $controler is a string that will be part of the output filename. Suggest to use the current
     * controler name or an empty string.
     * 
     * The resulting filename will be in de form of:
     *  a1a2a3a4a5a6a7a8a9a0b1b2b3b4b5b6_controler.js or a1a2a3a4a5a6a7a8a9a0b1b2b3b4b5b6_controler.js
     * 
     * a1a2a3a4a5a6a7a8a9a0b1b2b3b4b5b6 is an md5 digest of the concatenation of all required JS or
     * CSS file + their lastmodified timestamp, thus ensuring uniqueness an rebuild once a file is
     * modified.
     * 
     * @param string $fileType      either 'css' or 'js'
     * @param string $controller
     * @return string
     */
    protected function getFileName($fileType = 'css', $controller = '') {
        /**
         * @var array
         */
        $filesArray = [];
        $fileType = strtolower($fileType);
        $controller = strtolower($controller);
        if ($fileType == 'js') {
            $filesArray = $this->_jsArr;
        }
        else {
            $filesArray = $this->_cssArr;
        }
        $fns = [];
        foreach ($filesArray as $file) {
            if ((strtoupper(substr($file, 0, 6)) != 'HTTP://') and (strtoupper(substr($file, 0, 7)) != 'HTTPS://')) {
                if (file_exists($file)) {
                    $fns[] = $file . filemtime($file);  
                }
            }
            else {
                if ($this->url_exists($file)) {
                    $fns[] = $file . $this->filemtime_remote($file);
                }
            }
        }
        return trim(md5(implode($fns))) . '_' . trim($controller) . '.' .trim($fileType);
    }

    /**
     * Checks whether a requested URL of a css or js file actually results in a file.
     * @param string $url
     * @return bool
     */
    private function url_exists($url){
        $hdrs = @get_headers($url); 
        return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$hdrs[0]) : false; 
    }
    
    /**
     * Gives the last modified timastamp (as a unix timestamp) for a given url.
     * @param string $uri
     * @return int
     */
    private function filemtime_remote($uri)
    {
        $uri = parse_url($uri);
        $handle = @fsockopen($uri['host'],80);
        if(!$handle)
            return 0;

        fputs($handle,"HEAD $uri[path] HTTP/1.1\r\nHost: $uri[host]\r\n\r\n");
        $result = 0;
        while(!feof($handle))
        {
            $line = fgets($handle,1024);
            if(!trim($line))
                break;

            $col = strpos($line,':');
            if($col !== false)
            {
                $header = trim(substr($line,0,$col));
                $value = trim(substr($line,$col+1));
                if(strtolower($header) == 'last-modified')
                {
                    $result = strtotime($value);
                    break;
                }
            }
        }
        fclose($handle);
        return $result;
    }
}
