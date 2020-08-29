<?php

//Package to return on request to any player in a game lobby or in a ongoing game.
class PlayerPackage
{
    public $game = null; //Game object
    public $role = null; // Role object
    public $faction = null; // Faction object
    public $isAlive = true;
    public $isAtHome = true;
    public $knowsOwnRole = false;
    public $hasRoleExposed = false;
    public $hasInventoryExposed = false;
    public $lastWill = '';
    public $gameEvents = []; // gameEvent objects
    public $visited = []; // stripped Player objects
    public $visitors = []; //(stripped) Player objects
    public $abilities = []; // Ability objects
    public $inventory = []; // Item objects + count
    public $playersInGame = []; // (stripped) Player objects (name, discriminator, alive/dead, rid (if dead)
    public $rolesInGame = []; // Role objects
    public $banned = false;





}