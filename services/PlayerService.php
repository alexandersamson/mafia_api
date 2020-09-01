<?php


class PlayerService
{

    /**
     * @param $pid
     * @return object|null
     */
    function getPlayerByPid($pid){
        return SL::Services()->objectService->getSingleObject(['deleted' => 0, "pid" => $pid],new Player);
    }


    /**
     * @param $token
     * @return Player|null
     */
    function getPlayerByValidToken($token){
        $date = new DateTime();
        $timestamp = $date->getTimestamp();
        return SL::Services()->objectService->dbaseDataToSingleObject(
            SL::Services()->queryService->querySelectPlayerByUnexpiredToken($token, $timestamp)
            , new Player()
        );
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


    public function getPublicCoPlayersByGame($game, $isRequestedByHost = false){
        if($game == null){
            return null;
        }
        $playerPublicVmArray = SL::Services()->objectService->dbaseDataToObjects(
            SL::Services()->queryService->querySelectDistinctPlayersByGame($game), new PlayerExtendedViewModelPublic
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
     * @return PlayerViewModelPublic|null
     */
    public function convertPlayerToPlayerPublicViewModel($player){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]],__METHOD__)){
            return null;
        }
        $playerVm = new PlayerViewModelPublic();
        $playerVm->id = $player->id;
        $playerVm->name = $player->name;
        $playerVm->discriminator = $player->discriminator;
        $playerVm->isModerator = $player->isModerator;
        $playerVm->isAdmin = $player->isAdmin;
        $playerVm->isSuperadmin = $player->isSuperadmin;
        $playerVm->lastSeen = $player->lastSeen;
        return $playerVm;
    }


    public function addTokenToPublicPlayerView($token, $playerViewModel){
        if(!SL::Services()->validationService->validateParams(["PlayerViewModelPublic" => [$playerViewModel], "Token" => [$token]],__METHOD__)){
            return null;
        }
        $playerTokenVm = new PlayerViewModelTokenizedPublic();
        $playerTokenVm->playerToken = $token;
        return $playerTokenVm;
    }

}