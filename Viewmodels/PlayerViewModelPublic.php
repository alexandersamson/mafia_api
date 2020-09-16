<?php

namespace Main\Viewmodels;
use Main\Models\Player;
use Main\Services\SL;

class PlayerViewModelPublic
{
    public $id = null;
    public $name= "";
    public $discriminator = "";
    public $lastSeen = 0;
    public $isSuperadmin = false;
    public $isAdmin = false;
    public $isModerator = false;
    public $inGame = null;
    public $isHost = false;

    public function __construct(Player $player = null){
        if(isset($player)) {
            $this->id = $player->id;
            $this->name= $player->name;
            $this->discriminator = $player->discriminator;
            $this->lastSeen = $player->lastSeen;
            $this->isSuperadmin = (bool)$player->isSuperadmin;
            $this->isAdmin = (bool)$player->isAdmin;
            $this->isModerator = (bool)$player->isModerator;
            $this->inGame = new GameViewModelSmallest(SL::Services()->gameService->getGameByPlayer($player)) ?? null;
            $this->isHost = SL::Services()->playerService->isHostOfGameByPlayer($player, $this->inGame) ?? null;
        }
    }
}