<?php


class GameController
{
    private $connection;
    private $objectService;
    private $gameService;
    private $playerService;
    private $roleService;
    private $table;

    public function __construct()
    {
        $this->table = GlobalsService::getInstance()->getGamesTable();
        $this->connection = SL::Services()->connection;
        $this->objectService = SL::Services()->objectService;
        $this->gameService = SL::Services()->gameService;
        $this->playerService = SL::Services()->playerService;
        $this->roleService = SL::Services()->roleService;
        $this->seatService = SL::Services()->seatService;
    }


    /**
     * @param int $page
     * @param bool $getDeleted
     */
    public function getJoinableGamesPage($page, $getDeleted = false){
        $data = $this->gameService->getJoinableGamesPaginated($page, $getDeleted);
        if($data != null){
            if(!PlayerContext::getInstance()->isAuthorized("isAdmin", false)){
                $gamesList = $this->gameService->stripPinFromGames($data["data"]);
            }
            JsonBuilderService::getInstance()->add($data["data"], GlobalsService::$data);
            JsonBuilderService::getInstance()->add($data[strtolower(get_class(new Pagination()))], GlobalsService::$pagination);
        }
    }


    /**
     * PUBLIC API METHOD
     * - Clearance needed: [Logged in]
     * - API request: create_game
     * - Payload: (string) name, (array[string]) rids
     * - Returns: (object) create_game
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
            MessageService::getInstance()->add('error',"(GameController::create) Can't create game: Already in a game!");
            return false;
        }
        $roles = SL::Services()->roleService->getRolesByRids($rids);
        if($roles == null){
            MessageService::getInstance()->add('error',"(GameController::create) Can't create game: Can't retrieve roles.");
            return false;
        }
        $game = SL::Services()->gameService->createNewGame($name, $roles, PlayerContext::getInstance()->getCurrentPlayer(), $options);
        if($game == null) {
            MessageService::getInstance()->add('error',"(GameController::create) Can't create game: Game created, but not set.");
            return false;
        }
        JsonBuilderService::getInstance()->add($game, GlobalsService::$data);
        MessageService::getInstance()->add('userSuccess', "Game created: " . $game->gid);
        return true;
    }



    public function delete($gid){
        //TODO: Deletion of game (keeps in database)
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
            JsonBuilderService::getInstance()->add(true, GlobalsService::$data);
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
            return false;
        }
        if(!$this->gameService->checkPreJoinGame($game, $enteredGamePin)){
            return false;
        }
        if($this->seatService->addPlayerToSeat($this->seatService->getRandomAvailableSeat($game), PlayerContext::getInstance()->getCurrentPlayer())) {
            JsonBuilderService::getInstance()->add(
                SL::Services()->playerPackageService->assemblePlayerPackage(
                    PlayerContext::getInstance()->getCurrentPlayer()
                ),
                GlobalsService::$data
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
            JsonBuilderService::getInstance()->add(true, GlobalsService::$data);
            return true;
        }
        return false;
    }
}