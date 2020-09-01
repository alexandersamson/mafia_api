<?php


class Game
{
    public $id = null;
    public $gid = "";
    public $name = "";
    public $status = "";
    public $countDays = null;
    public $hostKeepsTime = true;
    public $pausedTimeLeft = 0;
    public $nextPhaseTimestamp = null;
    public $startPhaseId = 0;
    public $currentPhaseId = 0;
    public $showGameRoles = false;
    public $isPublicListed = 1;
    public $pinCode = "";
    public $creatorPlayerId = null;
    public $createdOn = 0;
    public $deleted = false;

    public function __construct(){
    }
}