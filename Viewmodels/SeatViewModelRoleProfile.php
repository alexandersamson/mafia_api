<?php

namespace Main\Viewmodels;
use Main\Models\Seat;
use Main\Services\MessageService;
use Main\Services\SL;

class SeatViewModelRoleProfile
{
    public $id = 0;
    //public $sid = 0;
    public $knowsOwnRole = false;
    public $knowsOwnFaction = false;
    public $hasRoleExposed = false;
    public $hasFactionExposed = false;
    public $hasTypeExposed = false;
    public $hasInventoryExposed = false;
    public $role = null;
    public $faction = null;
    public $factionCompanions = null;
    public $abilities = [];
    public $inventory = [];
    public $buffs = [];
    public $isAlive = false;
    public $banned = null;

    public function __construct(Seat $seat = null){
        if($seat != null){
            $game = SL::Services()->gameService->getGameById($seat->gameId);
            if(!isset($game)){
                MessageService::getInstance()->add("error", "SeatViewModelRoleProfile::__constructor - Cannot get game for seat");
                return null;
            }
            $this->id = (int)$seat->id ?? null;
            //$this->sid = $seat->sid ?? null;
            $this->knowsOwnRole = (bool)$seat->knowsOwnRole ?? null;
            $this->knowsOwnFaction = (bool)$seat->knowsOwnFaction ?? null;
            $this->hasRoleExposed = (bool)$seat->hasRoleExposed ?? null;
            $this->hasFactionExposed = (bool)$seat->hasFactionExposed ?? null;
            $this->hasTypeExposed = (bool)$seat->hasTypeExposed ?? null;
            $this->hasInventoryExposed = (bool)$seat->hasInventoryExposed ?? null;
            $this->role = (bool)$seat->knowsOwnRole === true ? (object)new RoleForOwnProfileViewModel(SL::Services()->roleService->getCurrentRoleFromSeat($seat)) ?? null : null;
            $this->faction = (bool)$seat->knowsOwnFaction === true ? (object)new FactionForOwnProfileViewModel(SL::Services()->factionService->getCurrentFactionFromSeat($seat)) ?? null : null;
            $this->factionCompanions = (array)SL::Services()->playerService->getFactionCompanionsForPlayerInGameBySeat($seat, PlayerViewModelGameOverview::class) ?? null;
            $this->abilities = (bool)$seat->knowsOwnRole === true ? (array)SL::Services()->abilityService->getAbilitiesBySeat($seat, AbilityForOwnProfileViewModel::class) ?? null : null;
            $this->inventory = (bool)$seat->knowsOwnRole === true ? (array)$seat->inventory ?? null : null; //TODO: get real inventory object
            $this->isAlive = (bool)$seat->isAlive ?? null;
            $this->buffs = (array)$seat->buffs ?? null;
            $this->banned = (bool)$seat->banned ?? null;
        }
    }
}