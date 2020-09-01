<?php


class MessageService
{
    private static $instance = null;

    public $showDebugging = true; //turn DEBUG info on/off
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
        if($this->showDebugging){
            $this->add("userWarning","Debugging is ON. To turn off debugging, set '\$showDebugging' to false in file MessageService.php");
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

    public function add($type, $message){
        if($this->showDebugging) {
            array_push($this->messages, ["type" => $this->messageTypes[$type], "message" => $message]);
        }
    }

    public function consumeAll(){
        $tempMessages = $this->messages;
        unset($this->messages);
        return $tempMessages;
    }
}