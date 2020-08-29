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
            $sql = "SELECT roles.id, roles.rid, roles.name, roles.type, roles.balance_power, roles.image_url, roles.fid, roles.abilities, roles.inventory FROM mafia.seats INNER JOIN mafia.roles ON seats.role_id = roles.id WHERE seats.game_id = ? AND roles.deleted = ?;";
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

    public function querySelectDistinctFactionsByGame($game, $getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game], "bool" => [$getDeleted]],__METHOD__)){
            return null;
        }
        $excludedFids = "'".implode('\', \'', GlobalsService::getInstance()->getInertFids())."'";
        $sql = "SELECT DISTINCT factions.* FROM mafia.factions INNER JOIN mafia.seats ON factions.fid = seats.fid INNER JOIN mafia.games ON games.id = seats.game_id
                WHERE games.id = ? AND factions.deleted = ? AND factions.fid NOT IN ($excludedFids) ORDER BY factions.list_priority;";
        $stmt = SL::Services()->connection->getConnection()->prepare($sql);
        $stmt->bindParam(1, $game->id, PDO::PARAM_INT);
        $stmt->bindParam(2, $getDeleted, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function querySelectFactionByPlayer($player){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]],__METHOD__)){
            return null;
        }
        $sql = "SELECT factions.* FROM mafia.factions LEFT JOIN mafia.seats ON factions.fid = seats.fid INNER JOIN mafia.players ON seats.player_id = players.id WHERE players.id = ?;";
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
}

