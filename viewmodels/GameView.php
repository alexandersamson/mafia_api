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
        $this->creator = SL::Services()->playerService->convertPlayerToPlayerPublicViewModel(SL::Services()->playerService->getPlayerById($game->creatorPlayerId));
        $this->createdOn = $game->createdOn;
        $this->deleted = $game->deleted;
        $this->host = SL::Services()->playerService->convertPlayerToPlayerPublicViewModel(SL::Services()->roleService->getHostPlayerForGame($game));
        $this->hasPinCode = SL::Services()->gameService->hasPincodeByGame($game);
        $this->pinCode = SL::Services()->gameService->gamePinCodeAccessFilterByPlayerContext($game);
        $this->availableSlots = SL::Services()->seatService->getCountGameSlotsAvailable($game, true);
        $this->usedSlots = SL::Services()->seatService->getCountGameSeatsUsed($game);
    }
}