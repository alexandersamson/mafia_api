<?php


class GameView
{
    public $id = null;              //model
    public $gid = "";               //model
    public $name = "";              //model
    public $status = "";            //model
    public $countDays = null;       //model
    public $countNights = null;     //model
    public $startPhase = "";        //model
    public $currentPhase = null;    //model
    public $showGameRoles = false;   //model
    public $isPublicListed = 1;     //model
    public $pinCode = "";           //model
    public $creatorPlayerId = null; //model
    public $creatorName = "";       //vm only
    public $createdOn = 0;          //model
    public $deleted = false;        //model
    public $availableSlots = 0;     //vm only
    public $usedSlots = 0;          //vm only
    public $factions = [];          //vm only


    public function __construct(Game $game)
    {
        $this->id = $game->id;
        $this->gid = $game->gid;
        $this->name = $game->name;
        $this->status = $game->status;
        $this->countDays = $game->countDays;
        $this->countNights = $game->countNights;
        $this->startPhase = $game->startPhase;
        $this->currentPhase = $game->currentPhase;
        $this->showGameRoles = $game->showGameRoles;
        $this->isPublicListed = $game->isPublicListed;
        $this->pinCode = $game->pinCode;
        $this->creatorPlayerId = $game->creatorPlayerId;
        $this->createdOn = $game->createdOn;
        $this->deleted = $game->deleted;
    }
}