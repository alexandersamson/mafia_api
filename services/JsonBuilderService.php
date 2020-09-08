<?php


class JsonBuilderService
{
    private static $instance = null;

    public $mainArray =[];

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new JsonBuilderService();
        }
        return self::$instance;
    }

    public function add($subArray, $chapter = null){
        if($chapter == null || $chapter == ""){
            array_push($this->mainArray, $subArray);
        }
        else if(!isset($this->mainArray[$chapter])){
            $this->mainArray[$chapter] = [];
            array_push($this->mainArray[$chapter], $subArray);
        } else {
            array_push($this->mainArray[$chapter],$subArray);
        }
    }

    public function addPaginated($dataArray){
        JsonBuilderService::getInstance()->add($dataArray[GlobalsService::$data], GlobalsService::$data);
        JsonBuilderService::getInstance()->add($dataArray[strtolower(get_class(new Pagination()))], GlobalsService::$pagination);
    }

    private function addDebugMessages(){
        $this->add(MessageService::getInstance()->consumeAll(),'messages');
    }


    private function addMeta(){
        $ts = new DateTime();
        $this->add([
            'version' => GlobalsService::$apiVersion,
            'apiName' => GlobalsService::$appName,
            'apiLink' => GlobalsService::$publicLink,
            'timestamp' => $ts->getTimestamp()
        ],'meta');
    }

    public function ConsumeJson(){
        $this->addDebugMessages();
        $this->addMeta();
        foreach ($this->mainArray as $key => $value){
            if(is_array($value) && count($value) < 2){
                if(isset($value[0]) && is_array($value[0])) {
                    $this->mainArray[$key] = $value[0];
                }
            }
        }
        $tempArray = $this->mainArray;
        unset($this->mainArray);
        return json_encode($tempArray);
    }
}