<?php

namespace Main\Services;
use Main\Models\Ability;
use Main\Models\Faction;
use Main\Models\Player;
use Main\Models\Role;
use Main\Viewmodels\FactionViewModel;
use Main\Viewmodels\RoleForPublicListing;

class RoleService
{

    /**
     * @param int $id
     * @param bool $getDeleted (= false)
     * @return Role|null
     */
    public function getRoleById(int $id, bool $getDeleted = false){
        if(SL::Services()->validationService->validateParams(["int" => $id, "bool" => $getDeleted], __METHOD__)) {
            return SL::Services()->objectService->getSingleObject(["id" => $id, 'deleted' => $getDeleted], new Role);
        }
        return null;
    }


    /**
     * @param array $roles
     * @param string $model
     * @return array|null
     */
    public function convertRolesToViewModels(Array $roles, $model = RoleForPublicListing::class){
        if(!isset($roles) || !is_array($roles)){
            MessageService::getInstance()->add('error', __METHOD__.' - No valid Array (Role objects) provided');
            return null;
        }
        $rolesVms = [];
        foreach ($roles as $role) {
            array_push($rolesVms, $this->convertRoleToViewModel($role));
        }
        return $rolesVms;
    }


    /**
     * @param Role $role
     * @param string $model
     * @return Role|RoleForPublicListing|object|null
     */
    public function convertRoleToViewModel(Role $role, $model = RoleForPublicListing::class){
        if(!isset($role)){
            MessageService::getInstance()->add('error', __METHOD__.' - No valid Role object provided');
            return null;
        }
        return new $model($role);
    }


    /**
     * @param string $rid
     * @param bool $getDeleted (=false)
     * @return object|null
     */
    public function getRoleByRid(string $rid, bool $getDeleted = false){
        if(SL::Services()->validationService->validateParams(["validate_rid" => $rid, "bool" => $getDeleted], __METHOD__)) {
            return SL::Services()->objectService->getSingleObject(["rid" => $rid, 'deleted' => $getDeleted], new Role);
        }
        return null;
    }

    /**
     * @param array $rids
     * @param bool $getDeleted (= false)
     * @return array|null
     */
    public function getRolesByRids(array $rids, bool $getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["bool" => $getDeleted], __METHOD__)) {
            return null;
        }
        $rids = $this->getValidatedRids($rids);
        if($rids == null){
            return null;
        }
        $roles = [];
        foreach ($rids as $rid) {
            array_push($roles, SL::Services()->objectService->getSingleObject(["rid" => $rid, 'deleted' => $getDeleted], new Role));
        }
        return $roles;
    }


    /**
     * @param string $fid
     * @param bool $getDeleted
     * @return array|null
     */
    public function getRolesByFid($fid, $getDeleted = false){
        SL::Services()->objectService->dbaseDataToObjects(SL::Services()->queryService->querySelectRolesByFid($fid, $getDeleted), new Role);
        return null;
    }

    /**
     * - Warning! Returns Roles stripped from Description and ImageUrl
     * @param $game
     * @return array|null
     */
    public function getOriginalRolesByGame($game){
        return SL::Services()->objectService->dbaseDataToObjects(SL::Services()->queryService->querySelectRolesWithoutDescriptionByGame($game),new Role);
    }

    /**
     * @param $game
     * @return array|null
     */
    public function getUniqueOriginalRolesFromGame($game){
        if(!SL::Services()->validationService->validateParams(["Game" => $game], __METHOD__)) {
            return null;
        }
        $roles = $this->getOriginalRolesByGame($game);
        if($roles == null || !isset($roles[0])){
            return null;
        }
        return array_unique($roles);
    }


    /**
     * @param $seat
     * @return object|null
     */
    public function getOriginalRoleFromSeat($seat){
        if(!SL::Services()->validationService->validateParams(["Seat" => $seat], __METHOD__)) {
            return null;
        }
        return SL::Services()->objectService->getSingleObject(["id" => $seat->roleId], new Role);
    }


    /**
     * @param $seat
     * @return Role|null
     */
    public function getCurrentRoleFromSeat($seat){
        if(!SL::Services()->validationService->validateParams(["Seat" => $seat], __METHOD__)) {
            return null;
        }
        return SL::Services()->objectService->getSingleObject(["id" => $seat->roleId], new Role);
    }


    /**
     * @param int $skip
     * @param int $take
     * @param false $getDeleted
     * @param bool $excludeInerts
     * @return array|null
     */
    public function getAllRoles($skip = 0, $take = 1000, $getDeleted=false, $excludeInerts=true){
        if(SL::Services()->validationService->validateParams(["int" => [$skip, $take], "bool"=>[$getDeleted, $excludeInerts]], __METHOD__)) {
            if($excludeInerts){
                return SL::Services()->objectService->dbaseDataToObjects(
                    SL::Services()->queryService->querySelectAllNonInertRoles($skip, $take, $getDeleted
                    ),
                    new Role
                );
            } else {
                return SL::Services()->objectService->getObjects(['deleted' => $getDeleted], new Role, null, $skip, $take);
            }
        }
        return null;
    }


    /**
     * @param $role
     * @param string $model
     * @param bool $deleted
     * @return array|null
     */
    public function getAbilitiesForRole($role, $model = Ability::class, bool $deleted = false){
        if(!isset($role)){
            MessageService::getInstance()->add('error',__METHOD__.' - No valid Role object provided.');
            return null;
        }
        return SL::Services()->abilityService->getAbilitiesByRole($role, $model, $deleted);
    }


    /**
     * @param array $roles
     * @param string $model
     * @param bool $deleted
     */
    public function attachAbilitiesToRoles(Array $roles, $model = Ability::class, bool $deleted = false){
        if(!isset($roles) || !is_array($roles)){
            MessageService::getInstance()->add('error',__METHOD__.' - No valid array (Roles) provided.');
            return null;
        }
        /* @var $role Role */
        foreach ($roles as &$role){
            $role->abilities = SL::Services()->abilityService->getAbilitiesByRole($role, $model, $deleted);
        }
        return $roles;
    }


    /**
     * @param $role
     * @param string $model
     * @param bool $deleted
     * @return Faction|FactionViewModel|object|null
     */
    public function getFactionForRole($role, $model = Faction::class, bool $deleted = false){
        if(!isset($role)){
            MessageService::getInstance()->add('error',__METHOD__.' - No valid Role object provided.');
            return null;
        }
            return SL::Services()->factionService->getFactionByRole($role, $model, $deleted);
    }


    /**
     * @param array $roles
     * @param string $model
     * @param bool $deleted
     * @return array|null
     */
    public function attachFactionToRoles(Array $roles, $model = Faction::class, bool $deleted = false){
        if(!isset($roles) || !is_array($roles)){
            MessageService::getInstance()->add('error',__METHOD__.' - No valid array (Roles) provided.');
            return null;
        }
        /* @var $role Role */
        foreach ($roles as &$role){
            $role->factionId = SL::Services()->factionService->getFactionByRole($role, $model, $deleted);
        }
        return $roles;
    }


    /**
     * @return object|null
     */
    public function getHostRole(){
        return $this->getRoleByRid(GlobalsService::getInstance()->getGameHostRoleRid());
    }


    /**
     * @param $game
     * @return Player|null
     */
    public function getHostPlayerForGame($game){
        return SL::Services()->objectService->dbaseDataToSingleObject(SL::Services()->queryService->querySelectHostPlayerByGame($game), new Player());
    }


    /**
     * @param $rolesArray
     * @param bool $shortenDescriptions
     * @return array
     */
    public function getUniqueRoleObjects($rolesArray, $shortenDescriptions = true){
        $countedValues = array_count_values($rolesArray);
        $combinedData = [];
        foreach ($countedValues as $key => $value){
            $role = $this->getRoleByRid($key);
            if($shortenDescriptions && strlen($role->description) > 32) {
                $role->description = substr($role->description, 0, 32)."...";
            }
            $combinedData[$key] = ["count" => $value, "total_power" => ($value * $role->balancePower), "role" => $role];
        }
        return $combinedData;
    }


    /**
     * @param array $rids
     * @return array|null
     */
    public function getValidatedRids(array $rids){
        if(!is_array($rids)){
            return null;
        }
        $newRids = [];
        foreach ($rids as $rid) {
            if(GlobalsService::getInstance()->isRole($rid)){
                array_push($newRids, $rid);
            }
        }
        if(isset($newRids[0]) && $newRids[0] != NULL) {
            return $newRids;
        }
        return null;
    }


    /**
     * @param $game
     * @return array|null
     */
    public function getOriginalFactionsFromGame($game){
        if(!SL::Services()->validationService->validateParams(["Game" => $game], __METHOD__)) {
            return null;
        }
        $seats = SL::Services()->seatService->getSeatsByGame($game);
        if($seats == null || !isset($seats[0])){
            return null;
        }
        $roles = $this->getOriginalRolesByGame($game);
        if($roles == null || !isset($roles[0])){
            return null;
        }
        $factions = [];
        foreach ($roles as $role){
            array_push($factions, $role->fid);
        }
        return array_unique($factions);
    }


    /**
     * @param array $factions
     * @param null $roles
     * @return array|null
     */
    public function addPowerLevelsToFactions($factions, $roles){
        foreach ($roles as $role){
            foreach ($factions as $key => $faction) {
                if(isset($factions[$key]->powerLevel) && isset($factions[$key]->id) && !$faction->isInert && $factions[$key]->id === $role->factionId){
                    $factions[$key]->powerLevel += (int)$role->balancePower;
                }
            }
        }
        return $factions;
    }
}


