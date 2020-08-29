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


    //This method is to be used for public API calls

    /**
     * @param $name
     * @param $pid
     * @param false $getRoleInfo
     * @param false $getLoginDetails
     * @return Player|object
     */
    function getPlayerByNameAndPid($name, $pid, $getLoginDetails = false){
        $player = $this->objectService->getSingleObject(['deleted' => 0, "name" => $name, "pid" => $pid],new Player);
        if(!$getLoginDetails){
            if(isset($player->password)) {
                unset($player->password);
            }
            if(isset($player->email)) {
                unset($player->email);
            }
        }
        return $player;
    }


    function create($name){
        if(PlayerContext::getInstance()->isAuthorized(null, false)){
            MessageService::getInstance()->add('error',"Cannot create player: Already logged in");
            return false;
        }
        if($name != NULL)
        {
            $name = SL::Services()->formatService->formatNameString($name,["validateUserName" => true]);
            if($name == null){
                MessageService::getInstance()->add('error',"Cannot create player: invalid username.");
                return false;
            }
            $date = new DateTime();
            $player = new Player();
            $player->name = $name;
            $player->pid = RandGenService::getInstance()->generateId($name);
            $player->discriminator = RandGenService::getInstance()->getValidDiscriminator($name,"");
            $player->createdOn = $date->getTimestamp();
            $player->lastSeen = $date->getTimestamp();
            $this->connection = new Connection();
            $this->connection->insertObjectIntoTable($player);
            $player = $this->getPlayerByNameAndPid($player->name, $player->pid);
            PlayerContext::getInstance()->setCurrentPlayerByObject($player);
            JsonBuilderService::getInstance()->add($player,GlobalsService::$data);
            MessageService::getInstance()->add('userSuccess',"Player created and logged in: ".$player->name.$player->discriminator);
            return $player;
        }
        MessageService::getInstance()->add('error',"Cannot create player: No name set.");
        return false;
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