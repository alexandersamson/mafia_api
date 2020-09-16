<?php

namespace Main\Viewmodels;
class PlayerViewModelPublicExtended extends PlayerViewModelPublic
{
    public $createdOn = 0;
    public $gamesPlayed = 0;
    public $gamesHosted = 0;
    public $roleId = null;
    public $knowsOwnRole = false;
    public $knowsOwnFaction = false;
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