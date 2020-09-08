<?php


class GameView
{
    public $id = null;              //model
    public $gid = "";               //model
    public $name = "";              //model
    public $status = "";            //model
    public $countDays = null;       //model
    public $nextPhaseTimestamp = null;//model
    public $hostKeepsTime = true;
    public $pausedTimeLeft = 0;
    public $startPhase = [];        //model
    public $currentPhase = [];    //model
    public $showGameRoles = false;  //model
    public $isPublicListed = 1;     //model
    public $hasPinCode = false;     //vm only
    public $pinCode = false;
    public $creator = [];
    public $host    = [];
    public $createdOn = 0;          //model
    public $deleted = false;        //model
    public $availableSlots = 0;     //vm only
    public $usedSlots = 0;          //vm only
    public $factions = [];          //vm only
    public $roles = [];             //vm only
    public $players = [];           //vm only


    public function __construct(Game $game)
    {
        $this->id = (int)$game->id;
        $this->gid = (string)$game->gid;
        $this->name = (string)$game->name;
        $this->status = (string)$game->status;
        $this->countDays = (int)$game->countDays;
        $this->nextPhaseTimestamp = (int)$game->nextPhaseTimestamp;
        $this->hostKeepsTime = (bool)$game->hostKeepsTime;
        $this->pausedTimeLeft = (int)$game->pausedTimeLeft;
        $this->startPhase = (object)SL::Services()->gamePhaseService->getStartPhaseForGame($game);
        $this->currentPhase = (object)SL::Services()->gamePhaseService->getCurrentPhaseForGame($game);
        $this->showGameRoles = (bool)$game->showGameRoles;
        $this->isPublicListed = (bool)$game->isPublicListed;
        $this->creator = (object)SL::Services()->playerService->convertPlayerToPlayerPublicViewModel(SL::Services()->playerService->getPlayerById($game->creatorPlayerId));
        $this->createdOn = (int)$game->createdOn;
        $this->deleted = (bool)$game->deleted;
        $this->host = (object)SL::Services()->playerService->convertPlayerToPlayerPublicViewModel(SL::Services()->roleService->getHostPlayerForGame($game));
        $this->hasPinCode = (bool)SL::Services()->gameService->hasPincodeByGame($game);
        $this->pinCode = (string)SL::Services()->gameService->gamePinCodeAccessFilterByPlayerContext($game);
        $this->availableSlots = (int)SL::Services()->seatService->getCountGameSlotsAvailable($game, true);
        $this->usedSlots = (int)SL::Services()->seatService->getCountGameSeatsUsed($game);
    }
}