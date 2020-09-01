<?php


class RandGenService
{
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new RandGenService();
        }
        return self::$instance;
    }

    public function generateGamePin()
    {
        $pin = '';
        $pin .= rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9);
        return $pin;
    }

    /**
     * @param string $salt
     * @return PlayerToken
     */
    public function generateToken($salt = ''){
        $token = new PlayerToken();
        $token->token = hash("sha3-512", $this->generateId($salt).$this->generateId($salt));
        $date = new DateTime();
        $token->expiresOn =  $date->getTimestamp() + GlobalsService::$tokenExpiresAfter;
        return $token;
    }

    public function generateId($salt = ''){
        if($salt == ''){
            $salt = GlobalsService::getInstance()->getGenSalt();
        }
        return hash("sha256",
            rand(0,999).
            rand(0,999). rand(0,999). rand(0,999). rand(0,999).
            rand(0,999). rand(0,999). rand(0,999). rand(0,999).
            uniqid('',true)
            .$salt
            .rand(0, 9) .rand(0, 9) .rand(0, 9) .rand(0, 9)
            .rand(0, 9) .rand(0, 9) .rand(0, 9) .rand(0, 9)
            .rand(0, 9) .rand(0, 9) .rand(0, 9) .rand(0, 9)
            .rand(0, 9) .rand(0, 9) .rand(0, 9) .rand(0, 9)
            .rand(0, 9) .rand(0, 9) .rand(0, 9) .rand(0, 9)
            .rand(0, 9) .rand(0, 9) .rand(0, 9) .rand(0, 9)
            .rand(0, 9) .rand(0, 9) .rand(0, 9) .rand(0, 9)
            .rand(0, 9) .rand(0, 9) .rand(0, 9) .rand(0, 9));
    }


    public function getValidDiscriminator($name, $discriminator){
        if(strlen($discriminator) != 5 && substr($discriminator,0,1) != "#"){
            $discriminator = '#'.rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9);
        }
        $results = 1;
        $iterator = 5000;
        $discriminatorCache = [];
        while($results > 0) {
            if($iterator == 0){
                return false;
            }
            $iterator --;
            $results = SL::Services()->connection->findOccurrences('players', ["name" => $name, "discriminator" => $discriminator]);
            if($results == 0){
                break;
            }
            array_push($discriminatorCache,$discriminator);
            while(in_array($discriminator,$discriminatorCache,true) == true) {
                $discriminator = '#'.rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9);
            }
        }
        unset($discriminatorCache);
        return $discriminator;
    }
}