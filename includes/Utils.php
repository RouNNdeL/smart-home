<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 16:54
 */
class Utils
{
    /**
     * @var Utils
     */
    private static $instance;
    const DEFAULT_LANG = "en";
    const AVAILABLE_LANGUAGES = ["en", "pl"];

    public $strings;
    public $lang;

    /**
     * Utils constructor.
     * @param null $lang
     */
    public function __construct($lang = null)
    {
        if($lang === null)
            $lang = isset($_COOKIE["lang"]) ? $_COOKIE["lang"] : self::DEFAULT_LANG;
        $this->lang = $lang;
        $this->loadStrings();
    }

    private function loadStrings()
    {
        $lang = $this->lang;
        $path = $_SERVER["DOCUMENT_ROOT"] . "/_lang/$lang.json";
        $file = file_get_contents($path);
        if($file == false)
        {
            $this->lang = self::DEFAULT_LANG;
            $this->loadStrings();
            return;
        }
        $this->strings = json_decode($file, true);
    }

    public function _getString(string $name)
    {
        if($this->strings != null && isset($this->strings[$name]))
        {
            return $this->strings[$name];
        }

        //return null;
        //Only for development purposes
        return "_" . $name;
    }

    public static function sanitizeString(string $string)
    {
        $sanitized_string = preg_replace('!\s+!', ' ', $string);
        $sanitized_string = preg_replace("!\s!", "_", $sanitized_string);
        $sanitized_string = strtolower($sanitized_string);
        return preg_replace("![^\sa-z0-9]!", "", $sanitized_string);
    }

    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getString(string $name)
    {
        return self::getInstance()->_getString($name);
    }
}