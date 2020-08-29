<?php


class PlayerService
{

    /**
     * @param $pid
     * @return mixed|null
     */
    function getPlayerByPid($pid){
        return SL::Services()->objectService->dbaseDataToSingleObject(SL::Services()->connection->getFromTable(GlobalsService::getInstance()->getPlayersTable(),['deleted' => 0, "pid" => $pid])[0],new Player);
    }

    function getPlayerById($id, $getDeleted = false){
        return SL::Services()->objectService->getSingleObject(['deleted' => $getDeleted, "id" => $id], new Player);
    }

    public function getPlayerNameById($id){
        $player = $this->getPlayerById($id);
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]])){
            return null;
        }
        return $player->name.$player->discriminator;
    }

    function getPlayerByNameAndPid($name, $pid){
        return SL::Services()->objectService->dbaseDataToSingleObject(SL::Services()->connection->getFromTable(GlobalsService::getInstance()->getPlayersTable(),['deleted' => 0, "name" => $name, "pid" => $pid])[0],new Player);
    }


    function updatePlayerLastSeenDate($player){
        if($player != NULL) {
            $date = new DateTime();
            $lastSeen = $date->getTimestamp();
            SL::Services()->connection->updateFields(GlobalsService::getInstance()->getPlayersTable(), ["last_seen" => $lastSeen], ['pid' => $player->pid]);
        }
    }

    public function getPublicCoPlayersByGame($game){
        if($game == null){
            return null;
        }
        $playerPublicVmArray = SL::Services()->objectService->dbaseDataToObjects(SL::Services()->queryService->querySelectDistinctPlayersByGame($game), new PlayerPublicViewModel);
        //JsonBuilderService::getInstance()->add($playerPublicVmArray,'debug');
        foreach ($playerPublicVmArray as $key => $pvm){
            if($pvm->hasRoleExposed){
                if(!$pvm->knowsOwnRole && (PlayerContext::getInstance()->getCurrentPlayer()->id === $pvm->id)){
                    continue;
                }
                $playerPublicVmArray[$key]->role = SL::Services()->objectService->getSingleObject(["id" => $pvm->roleId], new Role);
                $playerPublicVmArray[$key]->faction = SL::Services()->factionService->getFactionByPlayer($pvm);
                //TODO: Splitsen Role en Faction -> hasRoleExposed (Bestaat al) opslitsen naar hasFactionExposed (bestaat nog niet in dbase en migratie)
            }
            unset($playerPublicVmArray[$key]->roleId);
        }
        return $playerPublicVmArray;
    }

}