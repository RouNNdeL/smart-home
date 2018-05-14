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

    public $strings;
    public $lang;

    /**
     * Utils constructor.
     */
    public function __construct()
    {
        $lang = isset($_COOKIE["lang"]) ? $_COOKIE["lang"] : self::DEFAULT_LANG;
        $this->lang = $lang;
        $this->loadStrings();
    }

    private function loadStrings()
    {
        $lang = $this->lang;
        $path = $_SERVER["DOCUMENT_ROOT"]."/_lang/$lang.json";
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
        return "_".$name;
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