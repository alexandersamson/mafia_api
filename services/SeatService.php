<?php


class SeatService
{

    /**
     * @param int $id
     * @return object|null
     */
    public function getSeatById(int $id){
        if(!SL::Services()->validationService->validateParams(["int" => [$id]], __METHOD__)){
            return null;
        }
        return SL::Services()->objectService->getSingleObject(["id" => $id], new Seat);
    }

    /**
     * @param string $sid
     * @return object|null
     */
    public function getSeatBySid(string $sid){
        if(!SL::Services()->validationService->validateParams(["string" => [$sid]], __METHOD__)){
            return null;
        }
        return SL::Services()->objectService->getSingleObject(["sid" => $sid],new Seat);
    }

    /**
     * @param Game $game
     * @return array|null
     */
    public function getSeatsByGame(Game $game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]], __METHOD__)){
            return null;
        }
        return SL::Services()->objectService->getObjects(["game_id" => $game->id], new Seat);
    }


    /**
     * @param Player $player
     * @return object|null
     */
    public function getSeatByPlayer(Player $player){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]], __METHOD__)){
            return null;
        }
        return SL::Services()->objectService->getSingleObject(["player_id" => $player->id], new Seat);
    }

    /**
     * @param $game
     * @param $player
     * @param Role|object|null $role
     * @return bool
     */
    public function createSeat($game, Role $role = null, $player = null){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]])){
            return false;
        }

        $seat = new Seat();
        $seat->sid = RandGenService::getInstance()->generateId($game->gid);
        $seat->gameId = $game->id;

        if($role != null){
            if(!SL::Services()->validationService->validateParams(["Role" => [$role]])){
                return false;
            }
            $seat->roleId = $role->id;
            $seat->originalRoleId = $role->id;
            $seat->fid = $role->fid;
            $seat->originalFid = $role->fid;
            $seat->abilities = $role->abilities;
            $seat->inventory = $role->inventory;
        }

        if($player != null) {
            if(!SL::Services()->validationService->validateParams(["Player" => [$player]])){
                return false;
            }
            $seat->playerId = $player->id;
        }
        if(SL::Services()->connection->insertObjectIntoTable($seat)){
            return true;
        }
        MessageService::getInstance()->add('error', "createSeat (SeatService.php): Could not insert new seat.");
        return false;
    }


    /**
     * @param Game|object $game
     * @param array $roles (Role)
     * @return bool
     */
    public function addSeatsToGame(Game $game, array $roles)
    {
        if(!SL::Services()->validationService->validateParams([
            "Game" => [$game],
            "array" => [$roles],
            "Role" => [isset($roles[0]) ? $roles[0] : null]], __METHOD__))
        {
            return false;
        }

        $countRoles = count($roles);
        foreach($roles as $role){
           if(is_object($role)){
               $this->createSeat($game, $role);
           }
        }
        if($this->getCountHostSeats($game) === 0) {
            if($this->provideGameWithHostSeat($game)){
                $countRoles ++;
            }
        }

        $seats = SL::Services()->objectService->getObjects(["game_id" => $game->id], new Seat);
        if($seats == null){
            MessageService::getInstance()->add('error', "addSeatsToGame (SeatService.php): Could not create [seat] array.");
            return false;
        }
        if($countRoles == count($seats)) {
            MessageService::getInstance()->add('info', count($seats)."/".$countRoles." seats added to the game");
        } else {
            MessageService::getInstance()->add('warning', count($seats)."/".$countRoles." seats added to the game");
        }
        return true;
    }

    /**
     * @param Seat $seat
     * @return bool|true
     */
    public function resetSeat(Seat $seat){
        if(!SL::Services()->validationService->validateParams(["Seat" => [$seat]], __METHOD__)) {
             return false;
        }
        $role = SL::Services()->objectService->getSingleObject(["id" => $seat->roleId], new Role);
        if(!SL::Services()->validationService->validateParams(["Role" => [$role]], __METHOD__)) {
            return false;
        }
        $seat->playerId = null;
        $seat->roleId = $seat->originalRoleId;
        $seat->lastWill = "";
        $seat->isAlive = true;
        $seat->isAtHome = true;
        $seat->visitsPlayerId = null;
        $seat->knowsOwnRole = false;
        $seat->hasRoleExposed = false;
        $seat->fid = $seat->originalFid;
        $seat->inventory = $role->inventory;
        $seat->abilities = $role->abilities;
        $seat->banned = false;
        return SL::Services()->connection->updateFields(GlobalsService::getInstance()->getSeatsTable(),
            [
                "player_id" => $seat->playerId,
                "role_id" => $seat->roleId,
                "last_will" => $seat->lastWill,
                "is_at_home" => $seat->isAtHome,
                "visits_player_id" => $seat->visitsPlayerId,
                "knows_own_role" => $seat->knowsOwnRole,
                "has_role_exposed" => $seat->hasRoleExposed,
                "fid" => $seat->fid,
                "inventory" => $seat->inventory,
                "abilities" =>  $seat->abilities,
                "banned" => $seat->banned
            ],
            ["id" => $seat->id]);
    }

    /**
     * @param $seat
     * @return bool|true
     */
    public function vacateSeat($seat){
        if(!SL::Services()->validationService->validateParams(["Seat" => [$seat]], __METHOD__)){
            return false;
        }
        return SL::Services()->connection->updateFields(GlobalsService::getInstance()->getSeatsTable(),["player_id" => NULL],["id" => $seat->id]);
    }

    /**
     * @param $game
     * @return bool
     */
    public function removeAllSeatsForGame($game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]], __METHOD__)){
            return false;
        }
        return SL::Services()->connection->deleteFromTable(GlobalsService::getInstance()->getSeatsTable(),["game_id" => $game->id]);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function removeSeatForPlayer($player){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]], __METHOD__)){
            return false;
        }
            return SL::Services()->connection->deleteFromTable(
                GlobalsService::getInstance()->getSeatsTable(),
                ["player_id" => $player->id]
            );
    }


    /**
     * @param Seat $seat
     * @param Role $role
     * @return bool
     */
    public function addRoleToSeat(Seat $seat, Role $role){
        if(!SL::Services()->validationService->validateParams(["Role" => [$role], "Seat" => [$seat]], __METHOD__)){
            return false;
        }
        return SL::Services()->connection->updateFields(GlobalsService::getInstance()->getSeatsTable(),
            [
                "role_id" => $role->id,
                "original_role_id" => $role->id,
                "fid" => $role->fid,
                "original_fid" => $role->fid,
                "abilities" => $role->abilities,
                "inventory" => $role->inventory
            ],
            [
                "id" => $seat->id
            ]);
    }

    /**
     * @param $game
     * @return int|null
     */
    public function getCountHostSeats($game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]])) {
            return null;
        }
        $role = SL::Services()->roleService->getHostRole();
        if(SL::Services()->validationService->validateParams(["Role" => [$role]])) {
            return SL::Services()->connection->findOccurrences(GlobalsService::getInstance()->getSeatsTable(),["game_id" => $game->id, "role_id" => $role->id]);
        }
        return null;
    }

    /**
     * @param Game|object $game
     * @return object|null
     */
    public function getEmptyHostSeat($game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]])) {
            return null;
        }
        $role = SL::Services()->roleService->getHostRole();
        if(SL::Services()->validationService->validateParams(["Role" => [$role]])) {
            return SL::Services()->objectService->getSingleObject(["game_id" => $game->id, "player_id" => "IS NULL", "role_id" => $role->id], new Seat);
        }
        return null;
    }

    /**
     * @param $game
     * @return bool
     */
    public function provideGameWithHostSeat($game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]])) {
            return false;
        }
        if($this->getCountHostSeats($game) === 0){
            return $this->createSeat($game,SL::Services()->roleService->getHostRole());
        }
        return true; //true if there's already one
    }

    /**
     * @param Game|object $game
     * @return mixed|null
     */
    public function getRandomAvailableSeat(Game $game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]])){
            return null;
        }
        if($this->getCountGameSlotsAvailable($game) < 1){
            MessageService::getInstance()->add('error', "getRandomAvailableSeat (SeatService.php): No available seats.");
            return null;
        }
        $seats = $this->getAvailableSeatsByGame($game);
        if(is_array($seats)) {
            return $seats[rand(0, count($seats) - 1)];
        }
        return null;
    }

    /**
     * @param Seat|object $seat
     * @param Player $player
     * @return object|null
     */
    public function addPlayerToSeat($seat, $player){
        if(!SL::Services()->validationService->validateParams(["Seat" => [$seat], "Player" => [$player]])){
            return null;
        }
        if(SL::Services()->connection->updateFields(GlobalsService::getInstance()->getSeatsTable(),["player_id" => $player->id],["id" => $seat->id])){
            return $this->getSeatById($seat->id);
        }
        return null;
    }



    /**
     * @param Game $game
     * @return array|null
     */
    public function getAvailableSeatsByGame(Game $game){
        if(!is_object($game)){
            return null;
        }
        return SL::Services()->objectService->getObjects(["game_id" => $game->id, "player_id" => "IS NULL"], new Seat);
    }


    /**
     * @param Game $game
     * @return int|null
     */
    public function getCountSeatsByGame(Game $game){
        if(!is_object($game)){
            return null;
        }
        return SL::Services()->connection->findOccurrences(GlobalsService::getInstance()->getSeatsTable(),["game_id" => $game->id]);
    }


    /**
     * @param Game $game
     * @return int|null
     */
    public function getCountTotalGameSlots(Game $game){
        if(!is_object($game)){
            return null;
        }
        $totalRoles = $this->getCountSeatsByGame($game);
        if($totalRoles !== null){
            return $totalRoles;
        }
        return null;
    }

    /**
     * @param Game $game
     * @return int|null
     */
    public function getCountGameSlotsAvailable(Game $game, $excludeHost = false){
        if(!is_object($game)){
            return null;
        }
        $seats = SL::Services()->connection->findOccurrences(GlobalsService::getInstance()->getSeatsTable(), ["game_id" => $game->id, "player_id" => "IS NULL"]);
        if($excludeHost && $seats != null && $seats > 0){
            $seats --;
        }
        return $seats;
    }

    /**
     * @param Game $game
     * @return int|null
     */
    public function getCountGameSeatsUsed(Game $game){
        if(!is_object($game)){
            return null;
        }
        $totalRoles = $this->getCountSeatsByGame($game);
        $availableRoles = $this->getCountGameSlotsAvailable($game);
        if( $totalRoles == null || $availableRoles == null){
            return null;
        }
        return ($totalRoles-$availableRoles);
    }

}