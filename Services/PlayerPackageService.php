<?php

namespace Main\Services;
use Main\Viewmodels\PlayerPackage;

class PlayerPackageService
{
    public function assemblePlayerPackage($player){
        $playerPackage = new PlayerPackage();
        //PlayerPackage
        //public $game = null; //Game object
        //public $role = null; // Role object
        //public $faction = null; // Faction object
        //public $isAlive = true;
        //public $isAtHome = true;
        //public $visited = []; // stripped Player objects
        //public $visitors = []; //(stripped) Player objects
        //public $abilities = []; // Ability objects
        //public $inventory = []; // Item objects + count
        //public $allPlayers = []; // (stripped) Player objects (name, discriminator, alive/dead, rid (if dead)
        //public $gameRoles = []; // Role objects
        //public $banned = false;
        //public $hasRoleExposed = false;
        //public $lastWill = '';
        //public $gameEvents = []; // gameEvent objects
        //SEAT
        //public $id = "";
        //public $sid = "";
        //public $playerId = null;
        //public $gameId = 0;
        //public $roleId = null;
        //public $originalRoleId = null;
        //public $lastWill = "";
        //public $isAlive = true;
        //public $isAtHome = true;
        //public $visitsPlayerId = null;
        //public $knowsOwnRole = false;
        //public $hasRoleExposed = false;
        //public $factionId = "";
        //public $originalFactionId = "";
        //public $abilities =[];
        //public $inventory = [];
        //public $banned = false;


        $isHost = false;
        $started = false;
        $knowsOwnRole = false;
        $knowsOwnFaction = false;
        $canSeeGameRoles = false;
        $hasRoleExposed = false;
        $hasInventoryExposed = false;
        $seat = SL::Services()->seatService->getSeatByPlayer($player);
        if($seat == null){
            MessageService::getInstance()->add('error',"PlayerPackageService::assemblePlayerPackage - Cannot get seat for player {$player->name}{$player->discriminator}.");
            return null;
        }
        $game = SL::Services()->gameService->getGameById($seat->gameId);
        if($game == null){
            MessageService::getInstance()->add('error',"PlayerPackageService::assemblePlayerPackage - Cannot get game for player {$player->name}{$player->discriminator}.");
            return null;
        }
        if($game->deleted){
            MessageService::getInstance()->add('error',"PlayerPackageService::assemblePlayerPackage - Game has been deleted, cannot retrieve PlayerPackage for player {$player->name}{$player->discriminator}.");
            return null;
        }
        if($seat->banned){
            MessageService::getInstance()->add('error',"PlayerPackageService::assemblePlayerPackage - Seat was banned for {$player->name}{$player->discriminator}.");
            return null;
        }
        if(in_array($game->status, GlobalsService::$gameRoleExposeToPlayerStatuses)){
            $started = true;
        }
        if($seat->knowsOwnRole){
            $knowsOwnRole = true;
        }
        if($seat->knowsOwnFaction){
            $knowsOwnFaction = true;
        }
        if($game->showGameRoles){
            $canSeeGameRoles = true;
        }
        if(PlayerContext::getInstance()->isHostOfGame($game)){
            $isHost = true;
        }

        //Filling up the model.
        $playerPackage->game = new GameView($game);
        $playerPackage->isHost = $isHost;
        $playerPackage->game->players = SL::Services()->playerService->getPublicCoPlayersByGame($game, $isHost);
        //Fill these when game is started only
        if($started || $isHost){
            $playerPackage->lastWill = $seat->lastWill;
            $playerPackage->isAlive = $seat->isAlive;
            $playerPackage->isAtHome = $seat->isAtHome;
            $playerPackage->knowsOwnRole = $seat->knowsOwnRole;
            $playerPackage->knowsOwnFaction = $seat->knowsOwnFaction;
            $playerPackage->hasRoleExposed = $seat->hasRoleExposed;
            $playerPackage->hasFactionExposed = $seat->hasFactionExposed;
            $playerPackage->hasTypeExposed = $seat->hasTypeExposed;
            $playerPackage->hasInventoryExposed = $seat->hasInventoryExposed;
        }
        if($canSeeGameRoles || $isHost){
            $playerPackage->game->roles = SL::Services()->roleService->getOriginalRolesByGame($game);
        }
        if($knowsOwnRole || $isHost){
            $playerPackage->role = SL::Services()->roleService->getCurrentRoleFromSeat($seat);
        }
        if($knowsOwnFaction || $isHost){
            $playerPackage->faction = SL::Services()->factionService->getFactionByPlayer($player);
        }
        return $playerPackage;
    }
}