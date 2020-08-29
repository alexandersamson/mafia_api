<?php


class PlayerContext
{
    private $currentPlayer;
    private static $instance = null;


    private function __construct()
    {
        if (!isset($_SESSION['playercontext']['player'])){
            $_SESSION['playercontext']['player'] = new Player();
        }
        $this->currentPlayer = &$_SESSION['playercontext']['player'];
    }

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new PlayerContext();
        }
        return self::$instance;
    }

    public function checkForUpdate(){
        if($this->currentPlayer->pid != NULL) {
            $comparePlayer = SL::Services()->playerService->getPlayerByNameAndPid($this->currentPlayer->name, $this->currentPlayer->pid);
            if($comparePlayer != $this->currentPlayer){
                if($comparePlayer->pid != $this->currentPlayer->pid){
                    $this->unsetCurrentPlayer();
                } else {
                    $this->currentPlayer = SL::Services()->playerService->getPlayerByPid($this->currentPlayer->pid);
                }
            }
        }
    }

    //TODO: routine aanpassen -> nieuwe inloggegevens overrulen altijd een sessie. Dit is nu omgedraaid
    public function logInPlayer($name, $pid){
        if($this->currentPlayer->pid != NULL){
            $player = SL::Services()->playerService->getPlayerByNameAndPid($this->currentPlayer->name, $this->currentPlayer->pid);
            if($player){
                $this->currentPlayer = $player;
                MessageService::getInstance()->add("info", "Player logged in via session: {$player->name}{$player->discriminator}!");
                return;
            }
        }
        if($this->setCurrentPlayerByNameAndPid($name, $pid)){
            MessageService::getInstance()->add("userInfo", "Logged in as {$this->currentPlayer->name}{$this->currentPlayer->discriminator}." );
            return;
        }
        MessageService::getInstance()->add("debug", "Not logged in.");
    }

    public function setCurrentPlayerByObject($player){
        return $this->setCurrentPlayerByNameAndPid($player->name, $player->pid);
    }

    public function setCurrentPlayerByNameAndPid($name, $pid){
        $this->currentPlayer = SL::Services()->playerService->getPlayerByNameAndPid($name, $pid);
        if($this->currentPlayer) {
            if($this->currentPlayer->deleted || $this->currentPlayer->blocked){
                if($this->currentPlayer->blocked){
                    MessageService::getInstance()->add("debug", "Current player has been blocked.");
                }
                if($this->currentPlayer->deleted){
                    MessageService::getInstance()->add("debug", "Current player has been deleted.");
                }
                $this->currentPlayer = new Player();
                return false;
            }
            $date = new DateTime();
            $this->currentPlayer->lastSeen = $date->getTimestamp();
            SL::Services()->playerService->updatePlayerLastSeenDate($this->currentPlayer);
            return true;
        }
        return false;
    }

    public function getCurrentPlayer(){
        if(isset($this->currentPlayer->name) && isset($this->currentPlayer->pid)) {
            if ($this->currentPlayer->name != null && $this->currentPlayer->pid != null) {
                $player = SL::Services()->playerService->getPlayerByPid($this->currentPlayer->pid);
                if(!$player){
                    $this->currentPlayer = null;
                    return null;
                }
                $this->currentPlayer = $player;
                if($this->currentPlayer->deleted || $this->currentPlayer->blocked){
                    if($this->currentPlayer->blocked){
                        MessageService::getInstance()->add("debug", "Current player has been blocked.");
                    }
                    if($this->currentPlayer->deleted){
                        MessageService::getInstance()->add("debug", "Current player has been deleted.");
                    }
                    $this->currentPlayer = null;
                    return null;
                }
                return $this->currentPlayer;
            }
        }
        return null;
    }

    /**
     * @param string $level
     * @param bool $verbose
     * @return bool
     */
    public function isAuthorized($level = "", $verbose = true)
    {
        $player = $this->getCurrentPlayer();
        if($player == null) {
            if($verbose){
                MessageService::getInstance()->add("userError", MessageService::getInstance()->notLoggedInError);
            }
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

    public function unsetCurrentPlayer(){
        MessageService::getInstance()->add("debug", "Logging out: ".$this->currentPlayer->name.$this->currentPlayer->discriminator);
        $this->currentPlayer = new Player();
    }
}