<?php


class QueryService
{

    public function querySelectJoinableGames($skip = 0, $take = 1000, $getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["int"=> [$skip, $take], "bool" => [$getDeleted]],__METHOD__)){
            return null;
        }
        $statuses = $ids = implode(', ', GlobalsService::getInstance()->getGameStatusJoinableArray());
        $sql = "SELECT DISTINCT games.* FROM mafia.games LEFT JOIN mafia.seats ON seats.game_id = games.id 
                WHERE seats.player_id IS NULL AND games.deleted = ? AND games.is_public_listed = true AND games.status 
                IN (?) ORDER BY games.id DESC LIMIT ?, ?;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $getDeleted, PDO::PARAM_STR);
        $stmt->bindParam(2, $statuses, PDO::PARAM_STR);
        $stmt->bindParam(3, $skip, PDO::PARAM_INT);
        $stmt->bindParam(4, $take, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(isset($data[0])){
            MessageService::getInstance()->add("debug","(QueryService::querySelectJoinableGames) MYSQL getFromTable: " . implode(';',$data[0]));
            return $data;
        }
        return null;
    }

    public function queryCountJoinableGames($getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["bool" => [$getDeleted]],__METHOD__)){
            return null;
        }
        $statuses = "'".implode('\', \'', GlobalsService::getInstance()->getGameStatusJoinableArray())."'";
        $sql = "SELECT DISTINCT COUNT(DISTINCT games.id) FROM mafia.games LEFT JOIN mafia.seats ON seats.game_id = games.id WHERE seats.player_id IS NULL AND games.deleted = ? AND games.is_public_listed = true AND games.status IN ($statuses)";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $getDeleted, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function querySelectRolesByGame($game, $getDeleted = false, $fullFetch = true){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game], "bool" => [$getDeleted]],__METHOD__)){
            return null;
        }
        if($fullFetch){
            $sql = "SELECT roles.* FROM mafia.seats INNER JOIN mafia.roles ON seats.role_id = roles.id WHERE seats.game_id = ? AND roles.deleted = ?;";
        } else {
            $sql = "SELECT roles.id, roles.rid, roles.name, roles.type, roles.balance_power, roles.image_url, roles.faction_id, roles.abilities, roles.inventory FROM mafia.seats INNER JOIN mafia.roles ON seats.role_id = roles.id WHERE seats.game_id = ? AND roles.deleted = ?;";
        }
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $game->id, PDO::PARAM_INT);
        $stmt->bindParam(2, $getDeleted, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(isset($data[0])){
            MessageService::getInstance()->add("debug","(QueryService::querySelectRolesByGame) MYSQL getFromTable: " . implode(';',$data[0]));
            return $data;
        }
        return null;
    }

    public function querySelectRolesWithoutDescriptionByGame($game, $getDeleted = false){
        return $this->querySelectRolesByGame($game, $getDeleted, false);
    }

    public function querySelectDistinctFactionsByGame($game, $excludeInertFactions = true, $getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game], "bool" => [$getDeleted]],__METHOD__)){
            return null;
        }
        $fisInertSearch = $excludeInertFactions ? 'AND factions.is_inert = 0' : '';
        $sql = "SELECT DISTINCT factions.* FROM mafia.factions 
                INNER JOIN mafia.seats ON factions.id = seats.faction_id 
                INNER JOIN mafia.games ON games.id = seats.game_id 
                WHERE games.id = ? ".$fisInertSearch." AND factions.deleted = ? ORDER BY factions.list_priority;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $game->id, PDO::PARAM_INT);
        $stmt->bindParam(2, $getDeleted, PDO::PARAM_BOOL);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        MessageService::getInstance()->add("debug","(QueryService::querySelectDistinctFactionsByGame) MYSQL getFromTable: " . $sql);
        if(isset($data[0])){
            MessageService::getInstance()->add("debug","(QueryService::querySelectDistinctFactionsByGame) MYSQL getFromTable: " . implode(';',$data[0]));
            return $data;
        }
        return null;
    }




    public function querySelectDistinctPlayersByGame($game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]],__METHOD__)){
            return null;
        }
        $sql = "SELECT DISTINCT players.*, seats.is_alive, seats.role_id, seats.knows_own_role, seats.has_role_exposed FROM mafia.players INNER JOIN mafia.seats ON players.id = seats.player_id WHERE seats.game_id = ?;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $game->id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(isset($data[0])){
            MessageService::getInstance()->add("debug","(QueryService::querySelectDistinctPlayersByGame) MYSQL getFromTable: " . implode(';',$data[0]));
            return $data;
        }
        return null;
    }


    //Gets coplayers of a player in a game by this player
    public function querySelectDistinctPlayersInGameByPlayer($player){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]],__METHOD__)){
            return null;
        }
        $sql = "SELECT players.* FROM mafia.players LEFT JOIN mafia.seats ON players.id = seats.player_id WHERE ( SELECT seats.game_id FROM seats WHERE seats.player_id = ?) = seats.game_id;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $player->id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(isset($data[0])){
            MessageService::getInstance()->add("debug","(QueryService::querySelectDistinctPlayersInGameByPlayer) MYSQL getFromTable: " . implode(';',$data[0]));
            return $data;
        }
        return null;
    }


    public function querySelectFactionByPlayer($player){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]],__METHOD__)){
            return null;
        }
        $sql = "SELECT factions.* FROM mafia.factions LEFT JOIN mafia.seats ON factions.id = seats.faction_id INNER JOIN mafia.players ON seats.player_id = players.id WHERE players.id = ?;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $player->id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(isset($data[0])){
            MessageService::getInstance()->add("debug","(QueryService::querySelectFactionByPlayer) MYSQL getFromTable: " . implode(';',$data[0]));
            return $data[0];//[0] because of single object to return.
        }
        return null;
    }

    //checks if player has a valid token
    public function querySelectPlayerByUnexpiredToken($token, $timestamp, $deleted = false){
        if(!SL::Services()->validationService->validateParams(["string" => [$token], "integer" => [$timestamp], "bool" => [$deleted]],__METHOD__)){
            return null;
        }
        $sql = "SELECT * FROM `players` WHERE players.token = ? AND players.deleted = ? AND players.token_expires_on > ?;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $token, PDO::PARAM_STR);
        $stmt->bindParam(2, $deleted, PDO::PARAM_BOOL);
        $stmt->bindParam(3, $timestamp, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(isset($data[0])){
            MessageService::getInstance()->add("debug","(QueryService::querySelectPlayerByUnexpiredToken) MYSQL getFromTable: " . implode(';',$data[0]));
            return $data[0];//[0] because of single object to return.
        }
        return null;
    }

    // Checks if the player is a host of given game (returns 1 or more)
    public function queryCountHostsByPlayerAndGame($player, $game, $getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player], "Game" => [$game], "bool" => [$getDeleted]],__METHOD__)){
            return null;
        }
        $hostRid = GlobalsService::getInstance()->getGameHostRoleRid();
        $sql = "SELECT count(*) FROM mafia.players 
                LEFT JOIN mafia.seats ON players.id = seats.player_id 
                LEFT JOIN mafia.roles ON seats.role_id = roles.id 
                LEFT JOIN mafia.games ON seats.game_id = games.id 
                WHERE roles.rid = ? AND players.id = ? AND games.id = ? AND games.deleted = ? AND players.deleted = ?;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $hostRid, PDO::PARAM_STR);
        $stmt->bindParam(2, $player->id, PDO::PARAM_INT);
        $stmt->bindParam(3, $game->id, PDO::PARAM_INT);
        $stmt->bindParam(4, $getDeleted, PDO::PARAM_BOOL);
        $stmt->bindParam(5, $getDeleted, PDO::PARAM_BOOL);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }


    public function querySelectHostPlayerByGame($game, $getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game], "bool" => [$getDeleted]],__METHOD__)){
            return null;
        }
        $hostRid = GlobalsService::getInstance()->getGameHostRoleRid();
        $sql = "SELECT players.* FROM mafia.players 
                LEFT JOIN mafia.seats ON players.id = seats.player_id 
                LEFT JOIN mafia.roles ON seats.role_id = roles.id 
                WHERE seats.game_id = ? AND roles.rid = ? AND roles.deleted = ? AND players.deleted = ?;
";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $game->id, PDO::PARAM_INT);
        $stmt->bindParam(2, $hostRid, PDO::PARAM_STR);
        $stmt->bindParam(3, $getDeleted, PDO::PARAM_BOOL);
        $stmt->bindParam(4, $getDeleted, PDO::PARAM_BOOL);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(isset($data[0])){
            MessageService::getInstance()->add("debug","(QueryService::querySelectHostPlayerByGameId) MYSQL getFromTable: " . implode(';',$data[0]));
            return $data[0];//[0] because of single object to return.
        }
        return null;
    }


    public function queryCountInertFactionsByFid($fid, $getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["validate_fid" => [$fid], "bool" => [$getDeleted]],__METHOD__)){
            return null;
        }
        $sql = "SELECT COUNT(*) FROM `factions` WHERE is_inert = true AND fid = ? AND deleted = ?;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $fid, PDO::PARAM_STR);
        $stmt->bindParam(2, $getDeleted, PDO::PARAM_BOOL);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }


    public function querySelectRolesByFid($fid, $getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["validate_fid" => [$fid], "bool" => [$getDeleted]],__METHOD__)){
            return null;
        }
        $sql = "SELECT roles.* FROM mafia.roles LEFT JOIN mafia.factions ON roles.faction_id = factions.id WHERE factions.fid = ? AND roles.deleted = ?";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $fid, PDO::PARAM_STR);
        $stmt->bindParam(2, $getDeleted, PDO::PARAM_BOOL);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(isset($data[0])){
            MessageService::getInstance()->add("debug","(QueryService::querySelectHostPlayerByGameId) MYSQL getFromTable: " . implode(';',$data[0]));
            return $data;
        }
        return null;
    }


    public function calculatePowerLevelForRolesOfTypeInGame($role, $game = false){
        if(!SL::Services()->validationService->validateParams(["Role" => [$role], "Game" => [$game]],__METHOD__)){
            return null;
        }
        $sql = "SELECT SUM(roles.balance_power) FROM mafia.roles LEFT JOIN mafia.seats ON roles.id = seats.role_id WHERE seats.game_id = ? AND roles.id = ?;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $game->id, PDO::PARAM_INT);
        $stmt->bindParam(2, $role->id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }


    public function queryCountPlayerIsInSpecificGame($player, $game, $getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player], "Game" => [$game], "bool" => [$getDeleted]],__METHOD__)){
            return null;
        }
        $sql = "SELECT count(*) FROM mafia.players LEFT JOIN mafia.seats ON players.id = seats.player_id WHERE seats.game_id = ? AND players.id = ? AND players.deleted = ?;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $player->id, PDO::PARAM_INT);
        $stmt->bindParam(2, $game->id, PDO::PARAM_INT);
        $stmt->bindParam(3, $getDeleted, PDO::PARAM_BOOL);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }


    //Returns the amount of host roles in seats which are occupied by players
    public function queryCountOccupiedHostsForGame($game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]],__METHOD__)){
            return null;
        }
        $hostRid = GlobalsService::getInstance()->getGameHostRoleRid();
        $sql = "SELECT COUNT(*) FROM mafia.seats LEFT JOIN mafia.roles ON seats.role_id = roles.id WHERE seats.player_id IS NOT NULL AND seats.game_id = ? AND roles.rid = ?;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $game->id, PDO::PARAM_INT);
        $stmt->bindParam(2, $hostRid, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }


    //Returns the game where the player is in (if any)
    public function querySelectGameByPlayer($player, $getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]],__METHOD__)){
            return null;
        }
        $sql = "SELECT games.* FROM mafia.games LEFT JOIN mafia.seats ON games.id = seats.game_id WHERE seats.player_id = ? AND games.deleted = ?;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $player->id, PDO::PARAM_INT);
        $stmt->bindParam(2, $getDeleted, PDO::PARAM_BOOL);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(isset($data[0])){
            MessageService::getInstance()->add("debug","(QueryService::queryGetGameByPlayer) MYSQL getFromTable: " . implode(';',$data[0]));
            return $data[0];//[0] because of single object to return.
        }
        return null;
    }




}

