<?php


class RoleService
{

    /**
     * @param int $id
     * @param bool $getDeleted (= false)
     * @return object|null
     */
    public function getRoleById(int $id, bool $getDeleted = false){
        if(SL::Services()->validationService->validateParams(["int" => $id, "bool" => $getDeleted], __METHOD__)) {
            return SL::Services()->objectService->getSingleObject(["id" => $id, 'deleted' => $getDeleted], new Role);
        }
        return null;
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
        if(SL::Services()->validationService->validateParams(["validate_fid" => $fid, "bool" => $getDeleted], __METHOD__)) {
            return SL::Services()->objectService->getObjects(["fid" => $fid, 'deleted' => $getDeleted], new Role);
        }
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
     * @return object|null
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
     * @return array|null
     */
    public function getAllRoles($skip = 0, $take = 1000, $getDeleted=false){
        if(SL::Services()->validationService->validateParams(["int" => [$skip,$take],"bool"=>[$getDeleted]], __METHOD__)) {
            return SL::Services()->objectService->getObjects(['deleted' => $getDeleted], new Role, null, $skip, $take);
        }
        return null;
    }


    /**
     * @return object|null
     */
    public function getHostRole(){
        return $this->getRoleByRid(GlobalsService::getInstance()->getGameHostRoleRid());
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
            if ($role->fid == GlobalsService::getInstance()->isInertFid($role->fid)) {
                continue;
            }
            foreach ($factions as $key => $faction) {
                if(isset($factions[$key]->powerLevel) && isset($factions[$key]->fid) && $factions[$key]->fid === $role->fid){
                    $factions[$key]->powerLevel += (int)$role->balancePower;
                }
            }
        }
        return $factions;
    }
}