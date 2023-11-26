<?php

class Team
{
    var $teamID;
    var $name;
    var $color;

    public function __construct($teamID, $name, $color)
    {
        $this->teamID = $teamID;
        $this->name = $name;
        $this->color = $color;
    }
}