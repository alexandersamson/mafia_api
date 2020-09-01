<?php


class PlayerExtendedViewModelPublic extends PlayerViewModelPublic
{
    //Extends from PlayerViewModelPublic

    //public $id = null;
    //public $name= "";
    //public $discriminator = "";
    //public $createdOn = 0;
    //public $lastSeen = 0;
    //public $gamesPlayed = 0;
    //public $gamesHosted = 0;
    //public $isSuperadmin = false;
    //public $isAdmin = false;
    //public $isModerator = false;

    public $createdOn = 0;
    public $gamesPlayed = 0;
    public $gamesHosted = 0;
    public $roleId = null;
    public $knowsOwnRole = false;
    public $hasRoleExposed = false;
    public $hasFactionExposed = false;
    public $hasTypeExposed = false;
    public $hasInventoryExposed = false;
    public $role = null;
    public $faction = null;
    public $inventory = [];
    public $buffs = [];
    public $isAlive = true;
}