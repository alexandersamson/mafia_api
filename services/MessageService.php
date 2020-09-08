<?php


class MessageService
{
    private static $instance = null;

    public $showSystemDebugging = true; //
    public $showUserDebugging = true; //
    public $showOnlyErrors = true; //
    public $messages = [];
    public $messageTypes = [
        "error" => "System Error",
        "warning" => "System Warning",
        "debug" => "System Debug",
        "info" => "System Info",
        "userInfo" => "Info",
        "userSuccess" => "Success",
        "userWarning" => "Warning",
        "userError" => "Error",
        "userRestricted" => "Restricted"];

    public $genericUserError = "Something went wrong. Try again or contact an administrator.";
    public $notLoggedInError = "You are not logged in. Please log in or create a new user.";
    public $notAuthorizedError = "You are not authorized to do this.";

    private function __construct()
    {
        if(GlobalsService::$debug) {
            $this->add("userWarning", "System debugging is ON. To turn off debugging, set 'GlobalsService::\$debug' to false in file Globals.php", true);
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new MessageService();
        }
        return self::$instance;
    }

    public function add($type, $message, $bypass = false){
        if(GlobalsService::$debug) {
            if ($bypass == true ||
                (($this->showSystemDebugging || substr($type, 0, 4) == 'user') &&
                ($this->showUserDebugging || substr($type, 0, 4) !== 'user')))
            {
                if($bypass == true || !$this->showOnlyErrors || $type === 'error' || $type === 'userError') {
                    array_push($this->messages, ["type" => $this->messageTypes[$type], "message" => $message]);
                }
            }
        }
    }

    public function consumeAll(){
        if(GlobalsService::$debug) {
            $tempMessages = $this->messages;
            unset($this->messages);
            return $tempMessages;
        }
        return null;
    }
}