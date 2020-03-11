<?php

class BabelFish extends StudIPPlugin implements SystemPlugin
{
    public function __construct()
    {
        parent::__construct();
        PageLayout::addScript($this->getPluginURL()."/assets/babelfish.js");
        $this->addStylesheet("assets/babelfish.less");
    }
}
