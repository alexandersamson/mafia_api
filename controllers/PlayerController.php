<?php


class PlayerController
{
    private $connection;
    private $objectService;
    private $table;

    public function __construct()
    {
        $this->connection = SL::Services()->connection;
        $this->objectService = SL::Services()->objectService;
        $this->table = GlobalsService::getInstance()->getPlayersTable();
    }


    /**
     * PUBLIC API METHOD
     * @param $token
     * @return bool
     */
    function getPlayerByToken($token){
        if(isset($token)) {
            $player = SL::Services()->playerService->getPlayerByValidToken($token);
            if (isset($player)) {
                $playerView = SL::Services()->playerService->convertPlayerToPlayerPublicViewModel($player);
                JsonBuilderService::getInstance()->add($playerView, GlobalsService::$data);
                return true;
            }
        }
        JsonBuilderService::getInstance()->add(["error" => "Player not found"], GlobalsService::$data);
        return false;
    }


    //This method is to be used for public API calls
    /**
     * PUBLIC API METHOD
     * @param $name
     * @param $pid
     * @param false $getRoleInfo
     * @param false $getLoginDetails
     * @return bool
     */
    function getPlayerByNameAndPid($name, $pid, $getLoginDetails = false){
        $player = SL::Services()->playerService->getPlayerByNameAndPid($name,$pid);
        if($player == null){
            JsonBuilderService::getInstance()->add(["error" => "Cannot get player"], GlobalsService::$data);
            return false;
        }
        if(!$getLoginDetails){
            $playerView = SL::Services()->playerService->convertPlayerToPlayerPublicViewModel($player);
        }
        JsonBuilderService::getInstance()->add($playerView, GlobalsService::$data);
        return true;
    }


    function create($name){
        if(PlayerContext::getInstance()->isAuthorized(null, false)){
            MessageService::getInstance()->add('error',"Cannot create player: Already logged in");
            return false;
        }
        if($name == NULL) {
            MessageService::getInstance()->add('error',"PlayerController::create - Cannot create player: No name set.");
            return false;
        }
        $name = SL::Services()->formatService->formatNameString($name,["validateUserName" => true]);
        if($name == null){
            MessageService::getInstance()->add('error',"PlayerController::create - Cannot create player: invalid username.");
            return false;
        }
        $date = new DateTime();
        $player = new Player();
        $player->name = $name;
        $player->pid = RandGenService::getInstance()->generateId($name);
        $player->discriminator = RandGenService::getInstance()->getValidDiscriminator($name,"");
        $player->createdOn = $date->getTimestamp();
        $player->lastSeen = $date->getTimestamp();
        $token = RandGenService::getInstance()->generateToken($name.$player->discriminator);
        $player->token = $token->token;
        $player->tokenExpiresOn = $token->expiresOn;
        if(!$this->connection->insertObjectIntoTable($player)){
            MessageService::getInstance()->add('error',"PlayerController::create - Cannot insert new player into dbase.");
            return false;
        }
        $player = SL::Services()->objectService->getSingleObject(["token" => $player->token], new Player);
        if(!isset($player)){
            MessageService::getInstance()->add('error',"PlayerController::create - Cannot retrieve new player from dbase.");
            return false;
        }
        if(!PlayerContext::getInstance()->setCurrentPlayer($player)) {
            MessageService::getInstance()->add('error',"PlayerController::create - Cannot set new player as currentPlayer (session var).");
            return false;
        }
        $player = PlayerContext::getInstance()->getCurrentPlayer();
        if($player == null){
            MessageService::getInstance()->add('error',"PlayerController::create - Cannot get created player from currentPlayer (session var).");
            return false;
        }
        $playerView = SL::Services()->playerService->convertPlayerToPlayerPublicViewModel($player);
        $playerView = SL::Services()->playerService->addTokenToPublicPlayerView($token, $playerView);
        JsonBuilderService::getInstance()->add($playerView, GlobalsService::$data);
        MessageService::getInstance()->add('userSuccess',"PlayerController::create - Player created and logged in: ".$playerView->name.$playerView->discriminator);
        return true;
    }

    public function getPlayerPackage(){
        if(!PlayerContext::getInstance()->isAuthorized()){
            MessageService::getInstance()->add('error',MessageService::getInstance()->notLoggedInError);
            return false;
        }
        $player = PlayerContext::getInstance()->getCurrentPlayer();
        if(isset($player)){
            JsonBuilderService::getInstance()->add(SL::Services()->playerPackageService->assemblePlayerPackage($player),GlobalsService::$data);
            return true;
        }
        MessageService::getInstance()->add('error',"PlayerController::getPlayerPackage - cannot assemble package.");
        return false;
    }

}