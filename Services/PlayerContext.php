<?php

namespace Main\Services;
use DateTime;
use Main\Models\Player;

class PlayerContext
{
    private $currentPlayer;
    private static $instance = null;


    private function __construct()
    {
        $this->currentPlayer = null;
    }

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new PlayerContext();
        }
        return self::$instance;
    }


    public function logInPlayer($token){
        if(!SL::Services()->validationService->validateParams(["token_string" => [$token]], __METHOD__)){
            MessageService::getInstance()->add("error", "PlayerContext::logInPlayer - Invalid token provided; cannot login.");
            return false;
        }
        $player = SL::Services()->playerService->getPlayerByValidToken($token);
        if($player){
            if($this->setCurrentPlayer($player)){
                return true;
            }
            MessageService::getInstance()->add("error", "PlayerContext::logInPlayer - Player was found, but cannot be set as currentPlayer.");
            return false;
        }
        MessageService::getInstance()->add("error", "PlayerContext::logInPlayer - No player found by the provided token; cannot login.");
        return false;
    }


    public function setCurrentPlayer($player){
        if(!SL::Services()->validationService->validateParams(["Player" => [$player]], __METHOD__)){
            return false;
        }
        if($player) {
            if($player->deleted || $player->blocked){
                if($player->blocked){
                    MessageService::getInstance()->add("debug", "Current player has been blocked.");
                }
                if($player->deleted){
                    MessageService::getInstance()->add("debug", "Current player has been deleted.");
                }
                $this->currentPlayer = null;
                return false;
            }
            $date = new DateTime();
            $player->lastSeen = $date->getTimestamp();
            SL::Services()->playerService->updatePlayerLastSeenDate($player);
            $this->currentPlayer = $player;
            return true;
        }
        return false;
    }


    /**
     * @return Player|null
     */
    public function getCurrentPlayer(){
        if(
            isset($this->currentPlayer) &&
            isset($this->currentPlayer->name) &&
            isset($this->currentPlayer->pid) &&
            SL::Services()->validationService->validateParams([
                "player_name_string" => [$this->currentPlayer->name],
                "hex_id_string" => [$this->currentPlayer->pid]
            ], __METHOD__))
        {
            $player = SL::Services()->playerService->getPlayerByPid($this->currentPlayer->pid);
            if(!SL::Services()->validationService->validateParams(["Player" => [$player]],__METHOD__)){
                MessageService::getInstance()->add("error", "PlayerContext::getCurrentPlayer - Current player was stored, but does not validly exist in the dbase any more.");
                $this->currentPlayer = null;
                return null;
            }
            if($player->deleted || $player->blocked){
                if($player->blocked){
                    MessageService::getInstance()->add("error", "Current player has been blocked.");
                }
                if($player->deleted){
                    MessageService::getInstance()->add("error", "Current player has been deleted.");
                }
                $this->currentPlayer = null;
                return null;
            }
            $this->currentPlayer = $player;
            return $this->currentPlayer;
        }
        MessageService::getInstance()->add("error", "PlayerContext::getCurrentPlayer - Current player cannot be found or is invalid.");
        $this->currentPlayer = null;
        return null;
    }


    /**
     * @param string|null $level
     * @param bool $verbose
     * @return bool
     */
    public function isAuthorized($level = null, $verbose = true)
    {
        $player = $this->getCurrentPlayer();
        if(!isset($player)) {
            MessageService::getInstance()->add("userError", MessageService::getInstance()->notLoggedInError);
            return false;
        }
        if ($level == null) {
            return true;
        } else {
            if ($level == GlobalsService::getInstance()->getSuperadmin() && $player->isSuperadmin) {
                return true;
            }
            if ($level == GlobalsService::getInstance()->getAdministrator() && ($player->isSuperadmin || $player->isAdmin)) {
                return true;
            }
            if ($level == GlobalsService::getInstance()->getModerator() && ($player->isSuperadmin || $player->isAdmin || $player->isModerator)) {
                return true;
            }
        }
        if($verbose) {
            MessageService::getInstance()->add("userError", MessageService::getInstance()->notAuthorizedError);
        }
        return false;
    }

    public function isInAGame(){
        if(SL::Services()->connection->findOccurrences(
            GlobalsService::getInstance()->getSeatsTable(),
            ["player_id" => $this->currentPlayer->id]
        ) > 0){
            return true;
        }
        return false;
    }


    public function isAuthorizedAndInAGame($callingMethod = 'unknown'){
        if(!$this->isAuthorized()){
            JsonBuilderService::getInstance()->add(['error' => "You are not logged in"], GlobalsService::$jbError);
            MessageService::getInstance()->add('error',"($callingMethod) Can't get players for current player: User probably not logged in");
            return false;
        }
        if(!$this->isInAGame()){
            JsonBuilderService::getInstance()->add(['error' => "Yo are not in a game"], GlobalsService::$jbError);
            MessageService::getInstance()->add('error',"($callingMethod) Can't get players for current player: User probably not in a game");
            return false;
        }
        return true;
    }


    public function isInGame($game){
        if(SL::Services()->queryService->queryCountPlayerIsInSpecificGame($this->currentPlayer, $game) > 0){
            return true;
        }
        return false;
    }


    public function isHostOfGame($game){
        if(SL::Services()->queryService->queryCountHostsByPlayerAndGame($this->currentPlayer, $game) > 0){
            return true;
        }
        return false;
    }


    public function unsetCurrentPlayer(){
        MessageService::getInstance()->add("debug", "Logging out: ".$this->currentPlayer->name.$this->currentPlayer->discriminator);
        $this->currentPlayer = null;
    }
}