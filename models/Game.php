<?php


class Game
{
    public $id = null;
    public $gid = "";
    public $name = "";
    public $status = "";
    public $countDays = null;
    public $countNights = null;
    public $startPhase = "";
    public $currentPhase = null;
    public $showGameRoles = false;
    public $isPublicListed = 1;
    public $pinCode = "";
    public $creatorPlayerId = null;
    public $createdOn = 0;
    public $deleted = false;

    public function __construct(){
    }
}