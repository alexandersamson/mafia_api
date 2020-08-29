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
    public $hasRoleExposed = false;
    public $fid = "";
    public $originalFid = "";
    public $abilities =[];
    public $inventory = [];
    public $banned = false;
}