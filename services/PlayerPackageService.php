<?php


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
        //public $fid = "";
        //public $originalFid = "";
        //public $abilities =[];
        //public $inventory = [];
        //public $banned = false;


        $started = false;
        $knowsOwnRole = false;
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
        if($game->showGameRoles){
            $canSeeGameRoles = true;
        }
        if($seat->hasRoleExposed){
            $hasRoleExposed = true;
        }

        //Filling up the model.
        $playerPackage->game = $game;
        $playerPackage->playersInGame = SL::Services()->playerService->getPublicCoPlayersByGame($game);
        //Fill these when game is started only
        if($started){
            $playerPackage->lastWill = $seat->lastWill;
            $playerPackage->isAlive = $seat->isAlive;
            $playerPackage->isAtHome = $seat->isAtHome;
            $playerPackage->hasRoleExposed = $seat->hasRoleExposed;
        }
        if($canSeeGameRoles){
            $playerPackage->rolesInGame = SL::Services()->roleService->getOriginalRolesByGame($game);
        }
        if($knowsOwnRole){
            $playerPackage->role = SL::Services()->roleService->getCurrentRoleFromSeat($seat);
            $playerPackage->faction = SL::Services()->factionService->getFactionByPlayer($player);
        }
        return $playerPackage;
    }
}