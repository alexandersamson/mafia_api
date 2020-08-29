<?php


class FactionService
{

public function getUniqueFactionsByGame($game){
    return SL::Services()->objectService->dbaseDataToObjects(SL::Services()->queryService->querySelectDistinctFactionsByGame($game),new Faction);
}

public function getFactionByPlayer($player){
    if(!isset($player)){
        return null;
    }
    return SL::Services()->objectService->dbaseDataToSingleObject(SL::Services()->queryService->querySelectFactionByPlayer($player),new Faction);

}

}