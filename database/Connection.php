<?php


class Connection
{
    private $migrations = [];
    private $connection;
    private $objectService;

    public function __construct()
    {
        $this->connect();
    }


    public function connect(){
        try {
            $this->connection = new PDO('mysql:host='.GlobalsService::$dbServername.';dbname='.GlobalsService::$dbDatabase, GlobalsService::$dbUsername, GlobalsService::$dbPassword);
            // set the PDO error mode to exception
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //MessageService::getInstance()->add("debug","MYSQL: Connected successfully");
        } catch (PDOException $e) {
            MessageService::getInstance()->add("error", "MYSQL: Connection failed: " . $e->getMessage());
        }
    }

    public function checkConnection(){
        if($this->connection == NULL){
            $this->connect();
        }
    }

    /**
     * @param $object
     */
    public function insertObjectIntoTable($object){
        $this->checkConnection();
        $table = SL::Services()->objectService->fromCamelCase(get_class($object)).'s';
        $data = SL::Services()->objectService->prepareObjectForDbase($object);
        $fieldString = '(';
        $placeholderString = '(';
        $preparedData = [];
        foreach ($data as $key => $value){
            if($key == 'id'){
                continue;
            }
            if(!$this->startsWith($key, "_")){
                $fieldString .= $key.', ';
                $placeholderString .= ' ?,';
                if(is_array($value)){
                    $value = implode(GlobalsService::getInstance()->getDelimiter(), $value);
                }
                array_push($preparedData, $value);
            }
        }
        $fieldString = rtrim($fieldString, ", ").')';
        $placeholderString = rtrim($placeholderString, ",");
        $placeholderString .= ' )';
        $sql = "INSERT INTO $table $fieldString VALUES $placeholderString";
        $pStatement = $this->connection->prepare($sql);
        $pStatement = $this->bindValues($data,$pStatement);
        try {
            $pStatement->execute($preparedData);
            MessageService::getInstance()->add("debug","MYSQL Inserted: ".$sql);
            return true;
        } catch(PDOException $e) {
            MessageService::getInstance()->add("error","MYSQL: Insertion->Query failed: ".$e->getMessage()."[ $sql ]");
            return false;
        }
    }


    public function deleteFromTable($table, $data, $andOr = "AND"){
        $this->checkConnection();
        $sql = "DELETE FROM $table WHERE (";
        foreach ($data as $key => $value){
            $sql .= "$key = '$value' ".$andOr." ";
        }
        $sql = rtrim($sql, (" ".$andOr." ")).')';
        try {
            $result = $this->connection->prepare($sql);
            $result->execute();
            MessageService::getInstance()->add("debug","MYSQL Deleted: ".$sql);
            return true;
        } catch(PDOException $e) {
            MessageService::getInstance()->add("error","MYSQL: getFromTable->Query failed: ".$e->getMessage()." [ $sql ]");
            return false;
        }
    }

    //TODO: Prepared Statements for the methods below.
    /**
     * @param $table
     * @param $dataToUpdate
     * @param $dataToMatch
     * @param string $andOr
     * @return true|false
     */
    public function updateFields($table, $dataToUpdate, $dataToMatch, $andOr = "AND"){
        $this->checkConnection();
        $sql = "UPDATE $table SET ";
        foreach ($dataToUpdate as $key => $value){
            if($value === "IS NULL") {
                $sql .= "$key IS NULL, ";
            } else {
                $sql .= "$key = '$value', ";
            }
        }
        $sql = rtrim($sql, (", ")).' WHERE ';
        foreach ($dataToMatch as $keyM => $valueM){
            if(is_array($valueM)){
                $valueString = implode(GlobalsService::getInstance()->getDelimiter(), $valueM);
            } else {
                $valueString = $valueM;
            }
            $sql .= "$keyM = '$valueM' ".$andOr." ";
        }
        $sql = rtrim($sql, (" ".$andOr." "));
        //MessageService::getInstance()->add("debug","MYSQL: " . $sql);
        try {
            $result = $this->connection->prepare($sql);
            $result->execute();
            return true;
        } catch(PDOException $e) {
            MessageService::getInstance()->add("error","MYSQL: updateFields->Query failed: " . $e->getMessage(). " [" .$sql."]");
            return false;
        }
    }

    /**
     * @param $table
     * @param $data
     * @param string $andOr
     * @param int $take (= 1000)
     * @param int $skip (= 0)
     * @return array|false
     */
    public function getFromTable($table, $data, $andOr = "AND", int $take = 1000, int $skip = 0){
        if(!is_int($take)){
            $take = 1000;
        }
        if(!is_int($skip)){
            $skip = 0;
        }
        $this->checkConnection();
        $sql = "SELECT * FROM $table WHERE (";
        foreach ($data as $key => $value){
            if($value === "IS NULL"){
                $sql .= "$key IS NULL ".$andOr." ";
            } else {
                $sql .= "$key = '$value' " . $andOr . " ";
            }
        }
        $sql = rtrim($sql, (" ".$andOr." ")).") LIMIT $take OFFSET $skip";
        try {
            $result = $this->connection->prepare($sql);
            $result->execute();
            $data = $result->fetchAll(PDO::FETCH_ASSOC);
            if(isset($data[0])){
                //MessageService::getInstance()->add("debug","MYSQL getFromTable: " . implode(';',$data[0]));
                return $data;
            }
            return false;
        } catch(PDOException $e) {
            MessageService::getInstance()->add("error","MYSQL: getFromTable->Query failed: " . $e->getMessage() . " [" .$sql."]");
            return false;
        }
    }

    /**
     * @param $table
     * @param $data
     * @param string $andOr
     * @return integer|null
     */
    public function findOccurrences($table, $data, $andOr = "AND"){
        $this->checkConnection();
        $sql = "SELECT count(*) FROM $table WHERE (";
        foreach ($data as $key => $value){
            if($value === "IS NULL"){
                $sql .= "$key IS NULL ".$andOr." ";
            } else {
                $sql .= "$key = '$value' " . $andOr . " ";
            }
        }
        $sql = rtrim($sql, (" ".$andOr." ")).')';
        try {
            $result = $this->connection->prepare($sql);
            $result->execute();
            $count = $result->fetchColumn();
            return (int)$count;
        } catch(PDOException $e) {
            MessageService::getInstance()->add("error","MYSQL: findOccurrences->Query failed: " . $e->getMessage());
            return null;
        }
    }

    public function runMigrations($migrations){
        $this->checkConnection();
        foreach ($migrations as $key => $value){
            $sql = "SELECT count(*) FROM `migrations` WHERE migration_number = ?";
            $result = $this->connection->prepare($sql);
            $result->execute([$key]);
            if($result->fetchColumn() == 0){
                try {
                    $result = $this->connection->prepare($value);
                    $result->execute();
                    try {
                        $description = substr($value, 0, 63);
                        $sql = "INSERT INTO `migrations` (migration_number, description) VALUES (?, ?)";
                        $result = $this->connection->prepare($sql);
                        $result->execute([$key, $description]);
                        MessageService::getInstance()->add("debug","MYSQL: runMigrations->Success: Number:".$key." ($description)");
                    } catch(PDOException $e) {
                        MessageService::getInstance()->add("error","MYSQL: runMigrations->Query failed [migration: $key]: " . $e->getMessage());
                    }
                } catch(PDOException $e) {
                    MessageService::getInstance()->add("error","MYSQL: runMigrations->Query failed [migration: $key]: " . $e->getMessage());
                }
            }
        }
    }

    private function startsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        return substr( $haystack, 0, $length ) === $needle;
    }


    public function bindValues($data, $pStatement){

        foreach($data as $key => $value){
            if(!$this->startsWith($key, "_")) {
                if(is_array($value)){
                    $value = implode(GlobalsService::getInstance()->getDelimiter(),$value);
                }
                if (is_int($value)) {
                    $param = PDO::PARAM_INT;
                } else if (is_bool($value)) {
                    $param = PDO::PARAM_BOOL;
                } else if (is_null($value)) {
                    $param = PDO::PARAM_NULL;
                } else if (is_string($value)) {
                    $param = PDO::PARAM_STR;
                } else {
                    $param = false;
                }
                if ($param) {
                    $pStatement->bindValue("$key", $value, $param);
                }else{
                    $pStatement->bindValue("$key", $value, PDO::PARAM_STR);
                };
            }
        }
        return $pStatement;
    }


    public function close(){
        $this->connection = null;
    }

    /**
     * @return PDO
     */
    public function getConnection(){
        $this->checkConnection();
        return $this->connection;
    }


}