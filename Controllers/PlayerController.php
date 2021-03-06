<?php

namespace Main\Controllers;

use DateTime;
use Main\Connection\Connection;
use Main\Models\Player;
use Main\Services\GlobalsService;
use Main\Services\JsonBuilderService;
use Main\Services\MessageService;
use Main\Services\ObjectService;
use Main\Services\PlayerContext;
use Main\Services\RandGenService;
use Main\Services\SL;
use Main\Viewmodels\PlayerViewModelGameOverview;
use Main\Viewmodels\PlayerViewModelPublic;
use Main\Viewmodels\SeatViewModelRoleProfile;

class PlayerController
{
    private ?Connection $connection;
    private ?ObjectService $objectService;
    private string $table;

    public function __construct()
    {
        $this->connection = SL::Services()->connection;
        $this->objectService = SL::Services()->objectService;
        $this->table = SL::Services()->globals->getPlayersTable();
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
                $playerView = SL::Services()->playerService->convertPlayerToPlayerPublicViewModel($player, PlayerViewModelPublic::class);
                JsonBuilderService::getInstance()->add($playerView, GlobalsService::$jbData);
                return true;
            }
        }
        JsonBuilderService::getInstance()->add(["error" => "Player not found"], GlobalsService::$jbData);
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
            JsonBuilderService::getInstance()->add(["error" => "Cannot get player"], GlobalsService::$jbData);
            return false;
        }
        if(!$getLoginDetails){
            $playerView = SL::Services()->playerService->convertPlayerToPlayerPublicViewModel($player);
        }
        JsonBuilderService::getInstance()->add($playerView, GlobalsService::$jbData);
        return true;
    }




    /**
     * PUBLIC API METHOD
     * cp_get_players_game_overview
     */
    public function getPlayerOverviewForCurrentPlayersGame(){
        if(!PlayerContext::getInstance()->isAuthorizedAndInAGame(__METHOD__)){
            return false;
        }
        $playersVM = SL::Services()->playerService->convertPlayersToPlayersPublicViewModelsArray(
            SL::Services()->playerService->getAllPlayersForGameByPlayer(
                PlayerContext::getInstance()->getCurrentPlayer()
            ), PlayerViewModelGameOverview::class
        );
        if(!isset($playersVM) || !isset($playersVM[0])){
            MessageService::getInstance()->add('error',"(GameController::getPlayerOverviewForCurrentPlayersGame) Can't get players for current player: Probably no players in that game");
            return false;
        }
        JsonBuilderService::getInstance()->add($playersVM, GlobalsService::$jbData);
        return true;
    }


    public function getRoleDetailsForCurrentPlayersSeat(){
        if(!PlayerContext::getInstance()->isAuthorizedAndInAGame(__METHOD__)){
            return false;
        }
        $seat = SL::Services()->seatService->getSeatByPlayer(PlayerContext::getInstance()->getCurrentPlayer());
        if(!isset($seat)){
            MessageService::getInstance()->add('error', __METHOD__." - Cannot get current player seat from dbase");
            JsonBuilderService::getInstance()->add(["error" => "Could not find player or player data for current player"], GlobalsService::$jbError);
            return false;
        }
        $seatVm = new SeatViewModelRoleProfile($seat);
        if(isset($seatVm)){
            JsonBuilderService::getInstance()->add($seatVm, GlobalsService::$jbData);
            return true;
        }
        JsonBuilderService::getInstance()->add(["error" => MessageService::getInstance()->genericUserError], GlobalsService::$jbError);
        return false;
    }


    /**
     * PUBLIC API METHOD
     * @param $name
     * @return bool
     */
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
        $token = RandGenService::getInstance()->generateToken($player->pid);
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
        $playerView = SL::Services()->playerService->convertPlayerToPlayerPublicViewModel($player, PlayerViewModelPublic::class);
        $playerView = SL::Services()->playerService->addTokenToPublicPlayerView($token, $playerView);
        JsonBuilderService::getInstance()->add($playerView, GlobalsService::$jbData);
        MessageService::getInstance()->add('userSuccess',"PlayerController::create - Player created and logged in: ".$playerView->name.$playerView->discriminator);
        return true;
    }

    /**
     * PUBLIC API METHOD
     * @return bool
     */
    public function getPlayerPackage(){
        if(!PlayerContext::getInstance()->isAuthorized()){
            MessageService::getInstance()->add('error',MessageService::getInstance()->notLoggedInError);
            return false;
        }
        $player = PlayerContext::getInstance()->getCurrentPlayer();
        if(isset($player)){
            JsonBuilderService::getInstance()->add(SL::Services()->playerPackageService->assemblePlayerPackage($player),GlobalsService::$jbData);
            return true;
        }
        MessageService::getInstance()->add('error',"PlayerController::getPlayerPackage - cannot assemble package.");
        return false;
    }

}