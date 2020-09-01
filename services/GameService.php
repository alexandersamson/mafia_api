<?php


class GameService
{

    /**
     * @param string $gid
     * @param bool $getDeleted
     * @return object|null
     */
    function getGameByGid(string $gid, bool $getDeleted = false){
        return SL::Services()->objectService->getSingleObject(
            ['deleted' => $getDeleted, "gid" => $gid],
            new Game
        );
    }

    /**
     * @param string $id
     * @param bool $getDeleted
     * @return Game|null
     */
    function getGameById(string $id, bool $getDeleted = false){
        return SL::Services()->objectService->getSingleObject(
            ['deleted' => $getDeleted, "id" => $id],
            new Game
        );
    }


    public function getGameByPlayer($player){
        return SL::Services()->objectService->dbaseDataToSingleObject(SL::Services()->queryService->querySelectGameByPlayer($player),new Game);
    }

    /**
     * @param int $page
     * @param false $getDeleted
     * @return array|null
     */
    public function getJoinableGamesPaginated($page, $getDeleted = false){
        if(!SL::Services()->validationService->validateParams(["int" => [$page], 'bool'=>[$getDeleted]])){
            return null;
        }

        list(, $caller) = debug_backtrace(false);
        if(!isset($caller['function'])){
            $caller['function'] = "unknown";
        }

        $totalCount = SL::Services()->queryService->queryCountJoinableGames($getDeleted);
        $skip = SL::Services()->paginationService->getSkipsFromPageNumber($page);
        $take = SL::Services()->paginationService->getTake();
        if(!($skip < $totalCount)) {
            return null;
        }

        $games = SL::Services()->objectService->dbaseDataToObjects(SL::Services()->queryService->querySelectJoinableGames($skip, $take, $getDeleted), new Game);
        $viewModels = [];
        foreach ($games as $game){
            array_push($viewModels, $this->prepareGameViewModel($game));
        }
        $viewModels = $this->stripPinFromGames($viewModels);
        $pagination = SL::Services()->paginationService->getPaginationObject($caller['function'], $page, count($games), $totalCount);
        return ["data" => $viewModels, strtolower(get_class(new Pagination())) => $pagination];
    }


    public function prepareGameViewModel(Game $game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]], __METHOD__)){
            return null;
        }
        $viewModel = new GameView($game);
        $roles = SL::Services()->roleService->getOriginalRolesByGame($game);
        $viewModel->factions = SL::Services()->factionService->getUniqueFactionsByGame($game);
        $viewModel->factions = SL::Services()->roleService->addPowerLevelsToFactions($viewModel->factions, $roles);
        if($game->showGameRoles){
            array_push($viewModel->roles, $roles);
        }
        return $viewModel;
    }


    /**
     * @param array $games
     * @return array|null
     */
    public function stripPinFromGames($games){
        if(!SL::Services()->validationService->validateParams(["array" => [$games], "Game" => [isset($games[0]) ? $games[0] : null]],__METHOD__)){
            return null;
        }
        if(PlayerContext::getInstance()->isAuthorized("isAdmin", false)){
            return $games;
        }
        foreach ($games as $key => $game){
            if(!PlayerContext::getInstance()->isInGame($game)) {
                unset($games[$key]->pinCode);
            }
        }
        return $games;
    }


    /**
     * @param Game $game
     * @return string
     */
    public function gamePinCodeAccessFilterByPlayerContext(Game $game){
        if(!isset($game) || !isset($game->pinCode)){
            return '';
        }
        if(PlayerContext::getInstance()->isInGame($game) || PlayerContext::getInstance()->isAuthorized("isAdmin", false)){
            return $game->pinCode;
        }
        return '';
    }


    /**
     * @param $game
     * @return bool|null
     */
    public function hasPincodeByGame($game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]],__METHOD__)){
            return null;
        }
        if(isset($game->pinCode) && !empty($game->pinCode)){
            return true;
        }
        return false;
    }


    public function hasHost($game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]],__METHOD__)){
            return null;
        }
        return (SL::Services()->queryService->queryCountOccupiedHostsForGame($game) > 0);
    }


    /**
     * @param string $name
     * @param array $roles
     * @param Player $player
     * @param array|null $options
     * @return object|null
     */
    function createNewGame(string $name, array $roles, Player $player, array $options = null){
        if(!SL::Services()->validationService->validateParams([
            "string" => [$name],
            "array" => [$roles],
            "Role" => [isset($roles[0]) ? $roles[0] : null],
            "Player" => [$player]
            ])) {
            return null;
        }
        $name = SL::Services()->formatService->formatNameString($name, [
            "maxLength" => GlobalsService::getInstance()->getMaxGameNameLength(),
            "defaultValue" => "New Game"
        ]);
        $count = count($roles);
        if(($count < (int)GlobalsService::getInstance()->getMinRolesPerGame()) || ($count > (int)GlobalsService::getInstance()->getMaxRolesPerGame())){
            MessageService::getInstance()->add('error',"Can't create game: Amount of roles supplied not allowed. [$count] role supplied. Valid [".(int)GlobalsService::getInstance()->getMinRolesPerGame()."<>".(int)GlobalsService::getInstance()->getMaxRolesPerGame()."]");
            return null;
        }
        $gameOptions = (array)SL::Services()->validationService->validateOptions(__FUNCTION__,$options);
        $game = new Game();
        $date = new DateTime();
        $game->name = $name;
        $game->gid = RandGenService::getInstance()->generateId($name);
        $game->createdOn = $date->getTimestamp();
        $game->creatorPlayerId = $player->id;
        $game->pinCode = $gameOptions['hasPinCode'] ? $gameOptions["pinCode"] : '';
        $game->status = "open";
        $game->startPhaseId = $gameOptions["startPhaseId"];
        $game->isPublicListed = $gameOptions["isPublicListed"];
        SL::Services()->connection->insertObjectIntoTable($game);
        $game = $this->getGameByGid($game->gid);
        if(!is_object($game)){
            MessageService::getInstance()->add('error',"Can't create game: retrieving stored game object from dbase botched.");
            return null;
        }
        if(is_object($this->getGameByGid($game->gid))){
            if(SL::Services()->seatService->addSeatsToGame($game,$roles)){
                if(SL::Services()->seatService->addPlayerToSeat(SL::Services()->seatService->getEmptyHostSeat($game), $player)){
                    return $game;
                }
                MessageService::getInstance()->add('error',"Can't create game: cannot attach creator to host seat.");
            }
            MessageService::getInstance()->add('error',"Can't create game: adding roles to table roles_game failed.");
            SL::Services()->seatService->removeAllSeatsForGame($game);
        }
        $this->removeGame($game);
        return null;
    }


    public function gameIsJoinable($game){
        if(SL::Services()->seatService->getCountGameSlotsAvailable($game) < 1){
            return false;
        }
        if(!GlobalsService::getInstance()->hasJoinableStatus($game->status)){
            return false;
        }
        if($game->deleted){
            return false;
        }
        return true;
    }


    public function checkPreJoinGame($game, $enteredGamePin = ""){
        if(!PlayerContext::getInstance()->isAuthorized()){
            MessageService::getInstance()->add('userError',MessageService::getInstance()->notLoggedInError);
            return false;
        }
        if(!PlayerContext::getInstance()->isAuthorized("isAdmin" && $enteredGamePin != $game->pinCode)){
            MessageService::getInstance()->add('userError',"Can't join: Wrong pincode.");
            return false;
        }
        if(!GlobalsService::getInstance()->hasJoinableStatus($game->status)){
            MessageService::getInstance()->add('userError',"Can't join: Game status is $game->status.");
            return false;
        }
        if(SL::Services()->seatService->getCountGameSlotsAvailable($game) < 1){
            MessageService::getInstance()->add('userError',"Can't join: This game is full.");
            return false;
        }
        $player = PlayerContext::getInstance()->getCurrentPlayer();
        if($player != null && is_object($player)) {
            $seatObj = SL::Services()->seatService->getSeatByPlayer($player);
        } else {
            MessageService::getInstance()->add('userError',"Can't join: Cannot retrieve current player.");
            return false;
        }
        if($seatObj != null && is_object($seatObj)){
            $currentGame = $this->getGameById($seatObj->gameId);
            if($currentGame != NULL){
                if(!$currentGame->deleted) {
                    if($game->gid == $currentGame->gid){
                        MessageService::getInstance()->add('userError', "Can't join: Already in this game.");
                        return false;
                    }
                    MessageService::getInstance()->add('userError', "Can't join: Already in another game.");
                    return false;
                }
                SL::Services()->seatService->removeAllSeatsForGame($game);
            }
            SL::Services()->seatService->removeSeatForPlayer($player);
        }
        return true;
    }


    /**
     * @param Game|object $game
     * @return bool
     */
    public function removeGame($game){
        if(!SL::Services()->validationService->validateParams(["Game" => [$game]])){
            return false;
        }
        if(SL::Services()->connection->findOccurrences(GlobalsService::getInstance()->getSeatsTable(),["game_id" => $game->id]) > 0) {
            if (!SL::Services()->seatService->removeAllSeatsForGame($game)) {
                if (!SL::Services()->seatService->removeAllSeatsForGame($game)) {
                    MessageService::getInstance()->add('warning', "(gameService::removeGame) Can't remove seats for game $game->gid after 2 attempts. Script will continue.");
                }
            }
        }
        if(SL::Services()->connection->deleteFromTable(GlobalsService::getInstance()->getGamesTable(), ["gid" => $game->gid, "id" => $game->id], "OR")){
            unset($game);
            return true;
        }
        return false;
    }





}