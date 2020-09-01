<?php


class PlayerViewModelPublic
{
    public $id = null;
    public $name= "";
    public $discriminator = "";
    public $lastSeen = 0;
    public $isSuperadmin = false;
    public $isAdmin = false;
    public $isModerator = false;
}