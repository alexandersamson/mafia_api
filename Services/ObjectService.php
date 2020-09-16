<?php

namespace Main\Services;
class ObjectService
{

    private static ?ObjectService $instance = null;

    private function __construct(){}

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ObjectService();
        }
        return self::$instance;
    }

    /**
     * Returns a single object from the Connection.
     * Returns [object] if found, returns [null] if not found or on error
     * @param array $query
     * @param object $model
     * @param mixed $table (= null)
     * @param string $andOr (= "AND")
     * @return mixed|null
     */
    public function getSingleObject(array $query, object $model, $table = null, string $andOr = "AND"){
        $table = $this->getTable($model, $table);
        $object = $this->dbaseDataToSingleObject(
            SL::Services()->connection->getFromTable($table, $query, $andOr, 1)[0] ?? null, $model
        );
        if($object != NULL){
            return $object;
        }
        return null;
    }

    /**
     * Returns an array of objects from the Connection.
     * Returns [array] if found, returns [null] if nothing found or on error
     * @param array $query
     * @param object $model
     * @param mixed $table
     * @param int $skip
     * @param int $take
     * @param string $andOr
     * @return array|null
     */
    public function getObjects(array $query, object $model, $table = null, int $skip = 0, int $take = 1000, string $andOr = "AND"){
        $table = $this->getTable($model, $table);
        $data = SL::Services()->connection->getFromTable($table, $query, $andOr, $take, $skip);
        $objects = [];
        if(is_array($data)){
            foreach ($data as $object) {
                array_push($objects, $this->dbaseDataToSingleObject($object, new $model));
            }
        }
        if(!isset($objects[0])){
            return null;
        }
        return $objects;
    }


    /**
     * @param object $model
     * @param $table
     * @return string
     */
    public function getTable(object $model, $table){
        if($table == NULL){
            $table = $this->getPlurals($this->fromCamelCase(SL::Services()->formatService->getClassName($model)));
        } else if(is_object($table)){
            $table = $this->getPlurals($this->fromCamelCase(SL::Services()->formatService->getClassName($model)));
        } else if(!is_string($table)){
            $table = $this->getPlurals($this->fromCamelCase(SL::Services()->formatService->getClassName($model)));
            MessageService::getInstance()->add('error',__METHOD__.' - No valid table provided; using model instead.');
        }
        return $table;
    }

    /**
     * @param $data
     * @param $model
     * @return mixed|null
     */
    public function dbaseDataToSingleObject($data, $model){
        if($data == NULL){
            return null;
        }
        foreach ($data as $key => $value){
            $nKey = $this->toCamelCase($key);
            if(property_exists($model, $nKey)) {
                if(is_array($model->{$this->toCamelCase($key)})){
                    $value = explode(GlobalsService::getInstance()->getDelimiter(), $value);
                }
                $model->{$nKey} = $value;
            }
        }
        return $model;
    }

    public function dbaseDataToObjects($data, $model){
        if($data == NULL){
            return null;
        }
        $objects = [];
        if(is_array($data)){
            foreach ($data as $object) {
                array_push($objects, $this->dbaseDataToSingleObject($object, new $model));
            }
        }
        if(!isset($objects[0])){
            return null;
        }
        return $objects;
    }

    public function toCamelCase($string)
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }

    function fromCamelCase($input) {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    function getPlurals($input){
        if($input[-1] === 'y'){
            return substr($input, 0, strlen($input)-1).'ies';
        }
        return $input.'s';
    }


    ///TO dbase
    /**
     * @param $object
     * @return array
     */
    function prepareObjectForDbase($object){
        if($object == NULL){
            return null;
        }
        $data = get_object_vars($object);
        foreach ($data as $key => $value){
            $data = $this->changeKey($data, $key, $this->fromCamelCase($key));
        }
        return $data;
    }

    function changeKey( $array, $old_key, $new_key ) {
        if( ! array_key_exists( $old_key, $array ) )
            return $array;
        $keys = array_keys( $array );
        $keys[ array_search( $old_key, $keys ) ] = $new_key;
        return array_combine( $keys, $array );
    }

}