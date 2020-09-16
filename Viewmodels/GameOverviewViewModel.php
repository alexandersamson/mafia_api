<?php

namespace Main\Viewmodels;
use Main\Models\Game;
use Main\Services\SL;

class GameOverviewViewModel
{
    public $gid = "";               //model
    public $name = "";              //model
    public $status = "";            //model
    public $countDays = null;       //model
    public $nextPhaseTimestamp = null;     //model
    public $hostKeepsTime = true;
    public $pausedTimeLeft = 0;
    public $startPhase = null;     //model
    public $currentPhase = null;    //model
    public $isPublicListed = 1;     //model
    public $showGameRoles = 0;
    public $hasPinCode = false;     //vm only
    public $availableSlots = 0;     //vm only
    public $usedSlots = 0;          //vm only


    public function __construct(Game $game)
    {
        $this->gid = $game->gid;
        $this->name = $game->name;
        $this->status = $game->status;
        $this->countDays = $game->countDays ?? 0;
        $this->hostKeepsTime = (bool)$game->hostKeepsTime ?? true;
        $this->nextPhaseTimestamp = $game->nextPhaseTimestamp ?? 0;
        $this->pausedTimeLeft = $game->pausedTimeLeft ?? 0;
        $this->startPhase = new GamePhaseSmallViewModel(SL::Services()->gamePhaseService->getStartPhaseForGame($game)) ?? null;
        $this->currentPhase = new GamePhaseSmallViewModel(SL::Services()->gamePhaseService->getCurrentPhaseForGame($game)) ?? null;
        $this->showGameRoles = $game->showGameRoles;
        $this->isPublicListed = $game->isPublicListed;
        $this->hasPinCode = SL::Services()->gameService->hasPincodeByGame($game);
        $this->availableSlots = SL::Services()->seatService->getCountGameSlotsAvailable($game, true);
        $this->usedSlots = SL::Services()->seatService->getCountGameSeatsUsed($game);
    }

}