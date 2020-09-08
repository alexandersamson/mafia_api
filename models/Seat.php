<?php


class Seat
{
    public $id = "";
    public $sid = "";
    public $playerId = null;
    public $gameId = 0;
    public $roleId = null;
    public $originalRoleId = null;
    public $lastWill = "";
    public $isAlive = true;
    public $isAtHome = true;
    public $visitsPlayerId = null;
    public $knowsOwnRole = false;
    public $knowsOwnFaction = false;
    public $hasRoleExposed = false;
    public $hasFactionExposed = false;
    public $hasTypeExposed = false;
    public $hasInventoryExposed = false;
    public $factionId = null;
    public $originalFactionId = null;
    public $abilities =[];
    public $inventory = [];
    public $buffs = [];
    public $hiddenBuffs = [];
    public $banned = false;
}