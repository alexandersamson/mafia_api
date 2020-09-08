<?php


class ValidationService
{

    public function validateParams(array $params, $callingMethod = 'Unknown'){
        if(!is_array($params)){
            MessageService::getInstance()->add('error',"[$callingMethod] - Invalid validator array.");
            return false;
        }
        if(count($params) == 0){
            MessageService::getInstance()->add('error',"[$callingMethod] - Empty validator array.");
            return false;
        }
        foreach ($params as $type => $paramArray) {
            if (!is_array($paramArray)) {
                $paramArray = [$paramArray];
            }
            foreach ($paramArray as $param) {
                if (strtolower($type) === "string") {
                    if (!is_string($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [string]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    return true;
                } else if (strtolower($type) === "bool" || strtolower($type) === "boolean") {
                    if (!is_bool($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [bool]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    return true;
                } else if (strtolower($type) === "int" || strtolower($type) === "integer") {
                    if (!is_int($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [integer]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    return true;
                } else if (strtolower($type) === "array" || strtolower($type) === "[]") {
                    if (!is_array($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [array]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    return true;
                } else if (strtolower($type) === "obj" || strtolower($type) === "object") {
                    if (!is_object($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [object]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    return true;
                } else if (strtolower($type) === "validate_role" || strtolower($type) === "validate_rid") {
                    if (!is_string($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [string]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    if (!GlobalsService::getInstance()->isRole($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [valid rid]. Got invalid rid");
                        return false;
                    }
                    return true;
                } else if (strtolower($type) === "validate_fid" || strtolower($type) === "is_valid_fid") {
                    if (!is_string($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [string]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    if (!GlobalsService::getInstance()->isFid($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [valid fid]. Got invalid fid");
                        return false;
                    }
                    return true;
                } else if (strtolower($type) === "validate_string_game_phase") {
                    if (!is_string($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [string]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    if (!GlobalsService::getInstance()->isValidGamePhase($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [valid game phase]. Got invalid game phase");
                        return false;
                    }
                    return true;
                } else if (strtolower($type) === "validate_string_pin_code") {
                    if (!is_string($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [string]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    if (strlen($param) < 4 || strlen($param) > 10) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [strlen 4-10]. Got invalid strlen");
                        return false;
                    }
                    if (!preg_match('/^[0-9]*$/', $param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [numeric chars]. Got invalid chars");
                        return false;
                    }
                    return true;
                } else if (strtolower($type) === "player_name_string") {
                    if (!is_string($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [string]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    if (strlen($param) < 2 || strlen($param) > 20) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [strlen 2-20]. Got [strlen" . strlen($param) . "]");
                        return false;
                    }
                    if (!preg_match('/^[0-9a-zA-Z_ -]*$/', $param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [valid username chars]. Got invalid chars");
                        return false;
                    }
                    return true;
                } else if (strtolower($type) === "hex_id_string" || strtolower($type) === "hex_64_string") {
                    if (!is_string($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [string]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    if (strlen($param) !=  64) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [strlen 64]. Got [strlen" . strlen($param) . "]");
                        return false;
                    }
                    if (!preg_match('/^[0-9a-fA-F]*$/', $param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [HEX chars]. Got invalid chars");
                        return false;
                    }
                    return true;
                } else if (strtolower($type) === "token_string" || strtolower($type) === "token_string") {
                    if (!is_string($param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [string]. Got [" . gettype($param) . "]");
                        return false;
                    }
                    if (strlen($param) !=  128) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [strlen 128]. Got [strlen" . strlen($param) . "]");
                        return false;
                    }
                    if (!preg_match('/^[0-9a-fA-F]*$/', $param)) {
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [HEX chars]. Got invalid chars");
                        return false;
                    }
                    return true;
                } else {
                    if (is_object($param)) {
                        if(((get_class($param) === "PlayerExtendedViewModelPublic") || (get_class($param) === "PlayerViewModelPublic") || (get_class($param) === "PlayerViewModelTokenizedPublic")) && (strtolower($type) === 'player')){
                            return true;
                        }
                        if(((get_class($param) === "GameView") || (get_class($param) === "GameViewModelSmallest") || (get_class($param) === "Game")) && (strtolower($type) === 'game')){
                            return true;
                        }
                        if (strtolower(get_class($param)) === strtolower($type)) {
                            return true;
                        }
                        MessageService::getInstance()->add('error', "[$callingMethod] - Expected [object " . $type . "]. Got [" . get_class($param) . "]");
                    }
                }
                return false;
            }
        }
        return true;
    }


    public function validateOptions(string $method, $options){
        if($method == "createNewGame"){
            $defaultOptions["isPublicListed"] = true;
            $defaultOptions["hasPinCode"] = true;
            $defaultOptions["pinCode"] = RandGenService::getInstance()->generateGamePin();
            $defaultOptions["startPhaseId"] = 1;
            if($options == null){
                return $defaultOptions;
            }
            $validators = [
                "isPublicListed" => "bool",
                "hasPinCode" => "bool",
                "pinCode" => "validate_string_pin_code",
                "startPhaseId" => "integer"
            ];
            foreach ($defaultOptions as $key => $value) {
                if(!isset($options[$key])){
                    continue;
                }
                if($this->validateParams([$validators[$key] => [$options[$key]]])){
                    $defaultOptions[$key] = $options[$key];
                } else {
                    MessageService::getInstance()->add("userWarning","Provided game option [$options[$key]] has an invalid value. Default value has been used instead.");
                    continue;
                }
            }
            return $defaultOptions;
        }
        return null;
    }

    public function getApiKey(){
        return GlobalsService::getInstance()->getApiKey();
    }
}