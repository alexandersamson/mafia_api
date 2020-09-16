<?php

namespace Main\Services;
use DateTime;
use Exception;
use Main\Models\Player;

use Main\Models\Role;
use Main\Models\Seat;
use Main\Viewmodels\PlayerViewModelPublic;
use Main\Viewmodels\PlayerViewModelPublicExtended;
use Main\Viewmodels\PlayerViewModelTokenizedPublic;
use ReallySimpleJWT\Encode;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Validate;

class PlayerService
{

    /**
     * @param $pid
     * @return Player|object|null
     */
    function getPlayerByPid($pid){
        return SL::Services()->objectService->getSingleObject(['deleted' => 0, "pid" => $pid],new Player);
    }


    /**
     * @param $token
     * @return Player|null
     */
    function getPlayerByValidToken($token)
    {
        try {
            $jwt = new Jwt($token, GlobalsService::$tokenSecret);
            $parse = new Parse($jwt, new Validate(), new Encode());
            $parsed = $parse->validate()
                ->validateExpiration()
                ->parse();
        }
        catch (Exception $e){
            MessageService::getInstance()->add('userError',$e);
            return null;
        }
        return SL::Services()->playerService->getPlayerByPid($parsed->getPayload()['user_id']);
    }


    /**
     * @param $id
     * @param false $getDeleted
     * @return object|null
     */
    function getPlayerById($id, $getDeleted = false){
        return SL::Services()->objectService->getSingleObject(['deleted' => $getDeleted, "id" => $id], new Player);
    }


    /**
     * @param $name
     * @param $pid
     * @return object|null
     */
    function getPlayerByNameAndPid($name, $pid){
        return SL::Services()->objectService->getSingleObject(['deleted' => 0, "name" => $name, "pid" => $pid], new Player);
    }


    /**
     * @param $id
     * @return string|null
     */
    public function getPlayerNameById($id){
        $player = $this->getPlayerById($id);
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]])){
            return null;
        }
        return $player->name.$player->discriminator;
    }


    /**
     * @param $player
     */
    function updatePlayerLastSeenDate($player){
        if($player != NULL) {
            $date = new DateTime();
            $lastSeen = $date->getTimestamp();
            SL::Services()->connection->updateFields(GlobalsService::getInstance()->getPlayersTable(), ["last_seen" => $lastSeen], ['pid' => $player->pid]);
        }
    }


    public function getPlayerByGame($game){

    }


    public function getAllPlayersForGameByPlayer($player){
        return SL::Services()->objectService->dbaseDataToObjects(
            SL::Services()->queryService->querySelectDistinctPlayersInGameByPlayer($player), new Player()
        );
    }


    public function getPublicCoPlayersByGame($game, $isRequestedByHost = false){
        if($game == null){
            return null;
        }
        $playerPublicVmArray = SL::Services()->objectService->dbaseDataToObjects(
            SL::Services()->queryService->querySelectDistinctPlayersByGame($game), new PlayerViewModelPublicExtended
        );
        $player = PlayerContext::getInstance()->getCurrentPlayer();
        foreach ($playerPublicVmArray as $key => $pvm){
            if($pvm->hasRoleExposed || $isRequestedByHost){
                if(!$pvm->knowsOwnRole && !$isRequestedByHost && ($player->id === $pvm->id)){
                    continue;
                }
                $playerPublicVmArray[$key]->role = SL::Services()->objectService->getSingleObject(["id" => $pvm->roleId], new Role);
            }
            if($pvm->hasFactionExposed || $isRequestedByHost){
                if(!$pvm->knowsOwnFaction && !$isRequestedByHost && ($player->id === $pvm->id)){
                    continue;
                }
                $playerPublicVmArray[$key]->faction = SL::Services()->factionService->getFactionByPlayer($pvm);
            }
            unset($playerPublicVmArray[$key]->roleId);
        }
        return $playerPublicVmArray;
    }

    /**
     * @param $player
     * @param $model
     * @return Player|PlayerViewModelTokenizedPublic|PlayerViewModelPublic|PlayerViewModelPublicExtended|null
     */
    public function convertPlayerToPlayerPublicViewModel($player, $model = PlayerViewModelPublic::class){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]],__METHOD__)){
            return null;
        }
        return new $model($player);
    }

    /**
     * @param array $players
     * @param string $model
     * @return array|null[]
     */
    public function convertPlayersToPlayersPublicViewModelsArray(array $players, $model = PlayerViewModelPublic::class){
        if(!isset($players) || !isset($players[0])){
            return [null];
        }
        $formattedPlayers = [];
        foreach ($players as $player){
            array_push($formattedPlayers, $this->convertPlayerToPlayerPublicViewModel($player, $model));
        }
        return $formattedPlayers;
    }


    public function addTokenToPublicPlayerView($token, PlayerViewModelPublic $playerViewModel){
        if(!SL::Services()->validationService->validateParams(["PlayerViewModelPublic" => [$playerViewModel], "Token" => [$token]],__METHOD__)){
            return null;
        }
        $playerTokenVm = new PlayerViewModelTokenizedPublic();
        $playerTokenVm->playerToken = $token;
        return $playerTokenVm;
    }

    /**
     * @param $player
     * @param $game
     * @return bool
     */
    public function isHostOfGameByPlayer($player, $game){
        if(SL::Services()->queryService->queryCountHostsByPlayerAndGame($player, $game) > 0){
            return true;
        }
        return false;
    }

    public function isAlive($player){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]],__METHOD__)){
            return null;
        }
        return (SL::Services()->connection->findOccurrences(GlobalsService::getInstance()->getSeatsTable(),["is_alive" => true, "player_id" => $player->id]) > 0);
    }

    /**
     * @param Seat $seat
     * @param string $model
     * @return array|null
     */
    public function getFactionCompanionsForPlayerInGameBySeat(Seat $seat, string $model){
        $players = SL::Services()->objectService->dbaseDataToObjects(SL::Services()->queryService->querySelectPlayersForSameFactionBySeat($seat), new Player());
        if($model != Player::class && is_array($players)) {
            foreach ($players as $key => $player) {
                $players[$key] = new $model($player);
            }
        }
        return $players;
    }

}