<?php


class PlayerViewModelGameOverview
{
    public $id = null;
    public $name= "";
    public $discriminator = "";
    public $lastSeen = 0;
    public $isSuperadmin = false;
    public $isAdmin = false;
    public $isModerator = false;
    public $isHost = false;
    public $isAlive = false;

    public function __construct(Player $player){
        $this->id = $player->id;
        $this->name= $player->name;
        $this->discriminator = $player->discriminator;
        $this->lastSeen = (int)$player->lastSeen;
        $this->isSuperadmin = (bool)$player->isSuperadmin;
        $this->isAdmin = (bool)$player->isAdmin;
        $this->isModerator = (bool)$player->isModerator;
        $this->isHost = (bool)SL::Services()->playerService->isHostOfGameByPlayer($player, SL::Services()->gameService->getGameByPlayer($player));
        $this->isAlive = (bool)SL::Services()->playerService->isAlive($player);
    }
}