<?php
namespace Main\Controllers;

use Main\Services\GlobalsService;
use Main\Services\JsonBuilderService;
use Main\Services\RoleService;
use Main\Services\SL;

class RoleController
{
    private $connection;
    private $objectService;
    private $gameService;
    private $table;

    public function __construct()
    {
        $this->table = SL::Services()->globals->getRolesTable();
        $this->objectService = SL::Services()->objectService;
        $this->connection = SL::Services()->connection;
        $this->gameService = SL::Services()->gameService;
        $this->roleService = SL::Services()->roleService;
        $this->jsonBuilder = SL::Services()->jsonBuilderService;
    }


    /**
     * PUBLIC API METHOD
     * - Clearance needed: [None]
     * - API request: get_role_by_rid
     * - Payload:     (string) rid, (bool)[optional] deleted
     * @param string $rid
     * @param bool $deleted (false)
     * @return bool
     */
    public function getRoleByRid($rid, $deleted=false){
        $role = $this->roleService->getRoleByRid($rid, $deleted);
        if(!is_object($role) || $role == NULL) {
            return false;
        }
        $this->jsonBuilder->add($role,__FUNCTION__);
        return true;
    }

    /**
     * PUBLIC API METHOD
     * - Clearance needed: [None]
     * - API request: get_roles_by_faction_fid
     * - Payload:     (string) faction
     * @param string $factionFid
     * @return bool
     */
    public function getRolesByFid($fid){
        $roles = $this->roleService->getRolesByFid($fid);
        if(!is_array($roles) || $roles[0] == NULL) {
            return false;
        }
        $this->jsonBuilder->add($roles,GlobalsService::$jbData);
        return true;
    }

    /**
     * PUBLIC API METHOD
     * - Clearance needed: [None]
     * - API request: get_all_roles
     * @param int $skip
     * @param int $take
     * @param bool $excludeInerts
     * @return bool
     */
    public function getAllRoles($skip = 0, $take = 1000, $excludeInerts = true){
        $roles = $this->roleService->getAllRoles($skip = 0, $take = 1000, false, $excludeInerts);
        $rolesVms = SL::Services()->roleService->convertRolesToViewModels($roles);
        if(isset($rolesVms)){
            JsonBuilderService::getInstance()->add($rolesVms, GlobalsService::$jbData);

            return true;
        }
        JsonBuilderService::getInstance()->add(['error' => 'Cannot get roles'], GlobalsService::$jbError);
        return false;
    }

    /**
     * @param null $gid
     * @param null $game
     * @return array|false
     */
    public function getInitialRolesForGame($gid = NULL, $game = NULL){
        $rolesService = new RoleService();
        if($game != NULL){
            if(isset($game->initialRoles)){
                return $rolesService->getUniqueRoleObjects($game->initialRoles);
            }
        }
        if($gid == NULL){
            return false;
        }
        $game = $this->gameService->getGameByGid($gid);
        return $rolesService->getUniqueRoleObjects($game->initialRoles);
    }

    /**
     * @param null $gid
     * @param null $game
     * @return array|false
     */
    public function getAvailableRolesForGame($gid = NULL, $game = NULL){
        $rolesService = new RoleService();
        if($game != NULL){
            if(isset($game->rolesUnused)){
                return $rolesService->getUniqueRoleObjects($game->rolesUnused);
            }
        }
        if($gid == NULL){
            return false;
        }

        $game = $this->gameService->getGameByGid($gid);
        return $rolesService->getUniqueRoleObjects($game->rolesUnused);
    }

    /**
     * @param null $gid
     * @param null $game
     * @return array|false
     */
    public function getUsedRolesForGame($gid = NULL, $game = NULL){
        $rolesService = new RoleService();
        if($game != NULL){
            if(isset($game->rolesInUse)){
                return $rolesService->getUniqueRoleObjects($game->rolesInUse);
            }
        }
        if($gid == NULL){
            return false;
        }
        $game = $this->gameService->getGameByGid($gid);
        return $rolesService->getUniqueRoleObjects($game->rolesInUse);
    }


}