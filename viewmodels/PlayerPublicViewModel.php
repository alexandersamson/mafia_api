<?php


class PlayerPublicViewModel
{
    public $id = null;
    public $name= "";
    public $discriminator = "";
    public $lastSeen = 0;
    public $gamesPlayed = 0;
    public $gamesHosted = 0;
    public $isSuperadmin = false;
    public $isAdmin = false;
    public $isModerator = false;

    public $roleId = null;
    public $knowsOwnRole = false;
    public $hasRoleExposed = false;
    public $hasInventoryExposed = false;
    public $role = null;
    public $faction = null;
    public $inventory = [];
    public $isAlive = true;
}