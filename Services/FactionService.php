<?php

namespace Main\Services;
use Main\Models\Faction;
use Main\Models\Seat;

class FactionService
{

    /**
     * @param $game
     * @param string $model
     * @return array|null
     */
    public function getUniqueFactionsByGame($game, string $model = Faction::class){
        return SL::Services()->objectService->dbaseDataToObjects(SL::Services()->queryService->querySelectDistinctFactionsByGame($game),new $model);
    }

    /**
     * @param $player
     * @param string $model
     * @return mixed|null
     */
    public function getFactionByPlayer($player, string $model = Faction::class){
        if(!isset($player)){
            return null;
        }
        return SL::Services()->objectService->dbaseDataToSingleObject(SL::Services()->queryService->querySelectFactionByPlayer($player),new $model);

    }

    /**
     * @param Seat $seat
     * @param string $model
     * @return mixed|null
     */
    public function getCurrentFactionFromSeat(Seat $seat, string $model = Faction::class){
        if(!SL::Services()->validationService->validateParams(["Seat" => $seat], __METHOD__)) {
            return null;
        }
        return SL::Services()->objectService->getSingleObject(["id" => $seat->factionId], new $model, new Faction);
    }


    /**
     * @param $id
     * @param string $model
     * @param false $deleted
     * @return Faction|object|null
     */
    public function getFactionById($id, string $model = Faction::class, $deleted = false){
        $id = intval($id);
        if(!SL::Services()->validationService->validateParams(['int' => $id], __METHOD__)){
            return null;
        }
        return SL::Services()->objectService->getSingleObject(['id' => $id, 'deleted' => $deleted], new $model, new Faction);
    }


    /**
     * @param $role
     * @param string $model
     * @param false $deleted
     * @return Faction|object|null
     */
    public function getFactionByRole($role, string $model = Faction::class, $deleted = false){
        if(!isset($role) || !isset($role->factionId)){
            MessageService::getInstance()->add('error',__METHOD__.' - No valid Role or factionId provided.');
            return null;
        }
        return $this->getFactionById($role->factionId, $model, $deleted);
    }

}