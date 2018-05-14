<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 08/08/2017
 * Time: 18:42
 */
require_once("Data.php");

class Navbar
{
    private $tabs;
    private $active;
    private $highlight;

    function __construct()
    {
        $this->active = -1;
        $this->tabs = array();
    }

    public function toHtml()
    {
        $html = "";
        $html .= "<nav id=\"main-navbar\" class=\"navbar navbar-toggleable-sm navbar-light bg-faded\">
<a class=\"navbar-brand\" href=\"/\">LED Controller</a>
                    <button class=\"navbar-toggler navbar-toggler-right\" type=\"button\" data-toggle=\"collapse\" data-target=\"#navbarNav\" aria-controls=\"navbarNav\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">
                        <span class=\"navbar-toggler-icon\"></span>
                    </button>
                    <div class=\"collapse navbar-collapse\" id=\"navbarNav\">
                    <ul class=\"nav nav-pills flex-wrap\">";
        foreach($this->tabs as $i => $tab)
        {
            $url = $tab["url"];
            $text = $tab["text"];
            $class = $tab["class"];
            $html .= "<li role=\"presentation\" class=\"nav-item pr-md-1\"><a class=\"$class nav-link " .
                ($i == $this->active ? " active" :
                    ($i == $this->highlight ? " highlight\"" : "")) . "\" href=\"$url\">$text</a></li>";
        }
        $html .= "</ul>
                </div>
                </nav>";
        return $html;
    }

    public function addLink(string $text, string $url, $class = "")
    {
        array_push($this->tabs, array("text" => $text, "url" => $url, "class" => $class));
    }

    /**
     * @param int $active
     */
    public function setActive(int $active)
    {
        $this->active = $active;
    }

    public function initDefault()
    {
        $data = Data::getInstance();
        $profiles = $data->getProfiles();
        //$this->addLink(Utils::getString("global_options"), "/main");
        $this->highlight = $data->getHighlightIndex();
        foreach($profiles as $i => $profile)
        {
            $this->addLink($profile->getName(), "/profile/" . $i);
        }
        if(sizeof($profiles) < Data::MAX_OVERALL_COUNT)
        {
            $this->addLink("<span class=\"oi oi-plus\" style=\"top: 2px\"></span>&nbsp;" .
                Utils::getString("add_profile"),
                "/profile/new", "bold");
        }
    }
}