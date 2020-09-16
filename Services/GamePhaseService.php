<?php

namespace Main\Services;
use Main\Models\Game;
use Main\Models\GamePhase;

class GamePhaseService
{
    public function getCurrentPhaseForGame(Game $game){
        if(!isset($game)){
            return null;
        }
        return SL::Services()->objectService->getSingleObject(["id" => $game->currentPhaseId], new GamePhase());
    }

    public function getStartPhaseForGame(Game $game){
        if(!isset($game)){
            return null;
        }
        return SL::Services()->objectService->getSingleObject(["id" => $game->startPhaseId], new GamePhase());
    }

    public function getAllGamePhases(){
        return SL::Services()->objectService->getObjects(['deleted' => false], new GamePhase());
    }
}