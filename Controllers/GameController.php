<?php

namespace Main\Controllers;

use Main\Services\GlobalsService;
use Main\Services\JsonBuilderService;
use Main\Services\MessageService;
use Main\Services\PlayerContext;
use Main\Services\SL;
use Main\Viewmodels\GameOverviewViewModel;

class GameController
{
    private $connection;
    private $objectService;
    private $gameService;
    private $playerService;
    private $roleService;
    private $jsonBuilder;
    private $globals;
    private $table;

    public function __construct()
    {
        $this->table = SL::Services()->globals->getGamesTable();
        $this->connection = SL::Services()->connection;
        $this->objectService = SL::Services()->objectService;
        $this->gameService = SL::Services()->gameService;
        $this->playerService = SL::Services()->playerService;
        $this->roleService = SL::Services()->roleService;
        $this->seatService = SL::Services()->seatService;
        $this->jsonBuilder = SL::Services()->jsonBuilderService;
        $this->globals = SL::Services()->globals;
        $this->messageService = SL::Services()->messageService;
    }


    /**
     * @param int $page
     * @param bool $getDeleted
     */
    public function getJoinableGamesPage($page, $getDeleted = false){
        $data = $this->gameService->getJoinableGamesPaginated($page, $getDeleted);
        if($data != null){
            $this->jsonBuilder->addPaginated($data);
        } else {
            $this->jsonBuilder->add(['error' => 'Cannot get games. Perhaps there are no games available?', $this->globals::$jbError]);
        }
    }


    /**
     * PUBLIC API METHOD
     * - Clearance needed: [Logged in]
     * - API request: create_game
     * - Payload: (string) name, (array[string]) rids
     * - Returns: (Game)  data[]
     * - Game options: isPublicListed, hasPinCode, pinCode, startPhase
     * @param $name
     * @param $rids
     * @param array|null $options
     * @return bool
     */
    public function create($name, $rids, array $options = null){
        if(!PlayerContext::getInstance()->isAuthorized()){
            return false;
        }
        if(PlayerContext::getInstance()->isInAGame()){
            $this->messageService->add('error',"(GameController::create) Can't create game: Already in a game!");
            return false;
        }
        $roles = SL::Services()->roleService->getRolesByRids($rids);
        if($roles == null){
            $this->messageService->add('error',"(GameController::create) Can't create game: Can't retrieve roles.");
            return false;
        }
        $game = SL::Services()->gameService->createNewGame($name, $roles, PlayerContext::getInstance()->getCurrentPlayer(), $options);
        if($game == null) {
            $this->messageService->add('error',"(GameController::create) Can't create game: Game created, but not set.");
            return false;
        }
        $this->jsonBuilder->add($game, GlobalsService::$jbData);
        $this->messageService->add('userSuccess', "Game created: " . $game->gid);
        return true;
    }


    /**
     * PUBLIC API METHOD
     * - API request: cp_get_game_overview
     * - Returns: (GameOverviewViewModel) data[]
     * @return bool
     */
    public function getGameOverviewForCurrentPlayer(){
        if(!PlayerContext::getInstance()->isAuthorizedAndInAGame(__METHOD__)){
            return false;
        }
        $game = SL::Services()->gameService->getGameByPlayer(PlayerContext::getInstance()->getCurrentPlayer());
        if(!isset($game)){
            MessageService::getInstance()->add('error',"(GameController::getGameOverviewForCurrentPlayer) Can't get game for current player: Could  probably not retrieve it from the db");
            return false;
        }
        $gameOverview = new GameOverviewViewModel($game);
        JsonBuilderService::getInstance()->add($gameOverview, GlobalsService::$jbData);
        return true;
    }



    public function delete($gid){
        //TODO: Deletion of game (keeps in Connection)
    }


    /**
     * PUBLIC API METHOD
     * - Clearance needed: [Administrator]
     * - API request: remove_game
     * - Payload: (string) gid
     * - Returns: (bool) game_deleted
     * @param $gid
     * @return bool
     */
    public function remove($gid){
        if(!PlayerContext::getInstance()->isAuthorized("isAdmin")){
            return false;
        }
        if(SL::Services()->gameService->removeGame(SL::Services()->gameService->getGameByGid($gid))){
            JsonBuilderService::getInstance()->add(true, GlobalsService::$jbData);
            return true;
        }
        return false;
    }

    /**
     * PUBLIC API METHOD
     * - Clearance needed: [Logged in]
     * - API request: join_game
     * - Takes payload: (string) gid, (string) pinCode
     * - Returns payload: (PlayerPacket) data
     * @param $gid
     * @param string $enteredGamePin
     * @return bool
     */
    public function join($gid, $enteredGamePin = ""){
        $game = $this->gameService->getGameByGid($gid);
        if(!is_object($game)){
            JsonBuilderService::getInstance()->add(["error" => "This game does not exist"], GlobalsService::$jbError);
            return false;
        }
        if(!$this->gameService->checkPreJoinGame($game, $enteredGamePin)){
            JsonBuilderService::getInstance()->add(["error" => "Can't join this game"], GlobalsService::$jbError);
            return false;
        }
        if($this->seatService->addPlayerToSeat($this->seatService->getRandomAvailableSeat($game), PlayerContext::getInstance()->getCurrentPlayer())) {
            JsonBuilderService::getInstance()->add(
                SL::Services()->playerPackageService->assemblePlayerPackage(
                    PlayerContext::getInstance()->getCurrentPlayer()
                ),
                GlobalsService::$jbData
            );
            return true;
        }
        return false;
    }

    /**
     * PUBLIC API METHOD
     * - Clearance needed: [Logged in, In game]
     * - API request: leave_game
     * - Payload: none
     * - Returns: (bool) leave_game
     * @return bool|true
     */
    public function leave(){
        if(!PlayerContext::getInstance()->isAuthorized()){
            return false;
        }
        if(!PlayerContext::getInstance()->isInAGame()){
            MessageService::getInstance()->add('error',"(GameController::leave) Can't leave a game you aren't playing.");
            return false;
        }
        if(SL::Services()->seatService->vacateSeat(SL::Services()->seatService->getSeatByPlayer(PlayerContext::getInstance()->getCurrentPlayer()))){
            JsonBuilderService::getInstance()->add(true, GlobalsService::$jbData);
            return true;
        }
        return false;
    }

    public function getAllGamePhases(){
        $gamePhases = SL::Services()->gamePhaseService->getAllGamePhases();
        if(isset($gamePhases)){
            JsonBuilderService::getInstance()->add($gamePhases, GlobalsService::$jbData);
            return true;
        }
        JsonBuilderService::getInstance()->add(["error" => "Cannot get game phases"], GlobalsService::$jbError);
        return false;
    }
}