<?php


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
}