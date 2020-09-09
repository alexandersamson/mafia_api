<?php

class GlobalsService
{

    private static $instance = null;


    // --- START CONFIG VARS ---

    //APP
    public static $appName = 'Mafia API';
    public static $apiVersion = '0';
    public static $publicLink = 'http://mafia.api';
    public static $publicApiKey  = "0";
    public static $appSalt = "0";

    //DEBUG
    public static $debug = false;

    //RNG
    public static $genSalt = "0";

    //DB CONNECTION
    public static $dbServername = "dbServer:dbPort";
    public static $dbUsername = "dbUser";
    public static $dbPassword = "dbPassword";
    public static $dbDatabase =  "dbDatabsase";

    //CORS
    public static $corsAllowOrigin = 'http://localhost:4200';

    //SWT Token settings
    public static $tokenSecret = '';
    public static $tokenExpiresAfter = 0;
    public static $tokenName = '';
    public static $tokenNameAlt = '';

    //JSON Builder settings
    public static $jbData = 'data';
    public static $jbError = 'error';
    public static $jbMeta = 'meta';
    public static $jbMessages = 'messages';
    public static $jbPagination = 'pagination';

    // ---  END CONFIG VARS ---



    public static $gameRoleExposeToPlayerStatuses = ['started','paused','ended'];
    public static $factionBaseColor = '#808080';

    private $delimiter = ";";
    private $resultsPerPage = 10;
    private $playersTable = "players";
    private $gamesTable = "games";
    private $rolesTable = "roles";
    private $seatsTable = "seats";
    private $levelSuperAdmin = "isSuperadmin";
    private $levelAdmin = "isAdmin";
    private $levelModerator = "isModerator";
    private $fids = [
        "host",
        "town",
        "mafia",
        "thirdp",
        "cultist",
        "mason",
        "alien",
        "neutral",
        "yakuza",
        "zombie",
        "werewolf"
    ];
    private $roles = [
        "host",
        "citizen",
        "mafia",
        "gfather",
        "doctor",
        "igator",
        "skiller",
        "veteran",
        "janitor",
        "hunter",
        "vilante",
        "bmaker",
        "wwolf",
        "hunter",
        "bmaker",
        "jester",
        "mmurder",
        "lookout",
        "consig",
        "notary",
        "mayor",
        "vet",
        "snifdog"

    ];
    private $maxRolesPerGame = 32;
    private $minRolesPerGame = 3;
    private $validGamePhases = ["day","vote","sunset","night","sunrise"];
    private $maxGameNameLength = 32;
    private $gameHostRoleRid = "host";
    private $gameStatusJoinableArray = ["open"];
    private $apiRequests = [
        "get_public_api_key" => ["description" => "Gets a public API key for the Mafia API.", "payload"=>[]],
        "get_player_package" => ["description" => "Gets all the info for the current logged-in player they need during gameplay", "payload"=>[]],
        "get_role_by_rid" => ["description" => "Gets a role by its rid", "payload"=>["rid"=>"string"]],
        "get_roles_by_fid" => ["description" => "Gets roles by their faction fid", "payload"=>["fid"=>"string"]],
        "get_all_roles" => ["description" => "Gets all available roles", "payload"=>[]],
        "get_initial_roles_for_game" => ["description" => "Gets all initially set roles for a game", "payload"=>["gid"=>"string"]],
        "get_available_roles_for_game" => ["description" => "Gets all roles, which are yet unoccupied, for a game", "payload"=>["gid"=>"string"]],
        "get_used_roles_for_game" => ["description" => "Gets all roles, which are occupied, for a game", "payload"=>["gid"=>"string"]],
        "get_joinable_games_page" => ["description" => "Gets all roles, which are occupied, for a game", "payload"=>["page"=>"integer | null"]],
        "create_game" => ["description" => "Creates a game and then joins it as game host", "payload"=>["name"=>"string","roles"=>"array[string]","options"=>"array[object] | null"],"remarks"=>["requires"=>"Being logged in"]],
        "join_game" => ["description" => "Joins a game", "payload"=>["gid"=>"string"],"remarks"=>["requires"=>"Being logged in"]],
        "get_player_by_name_and_pid" => ["description" => "Gets a player by provided name and pid. Doesn't log the player in.", "payload"=>["name"=>"string","pid"=>"string"]],
        "get_player_by_pid" => ["description" => "Gets a player object by provided pid.", "payload"=>["name"=>"string","pid"=>"string"],"remarks"=>["requires"=>"Being logged in with [administrator: true] authorization or higher"]],
        "log_in_player" => ["description" => "Logs in a player by setting a session variable, returns a player object when a login attempt is valid", "payload"=>["name"=>"string","pid"=>"string"]],
        "create_player" => ["description" => "Creates a new user", "payload"=>["name"=>"string"],"remarks"=>["requires"=>"Admin rights or being logged off","be aware"=>"The combination of user [name] and [pid] is enough to log in via the [log_in_player] call. Store the [pid] somewhere safe."]],

    ];

    private function __construct()
    {
        $ini_arr = parse_ini_file("settings/settings.ini");
        print_r($ini_arr);
        foreach ($ini_arr as $key => $value){
            try{
                self::${$key} = $value;
                print_r(self::${$key});
            } catch (Exception $e){
                print_r($e);
            }
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new GlobalsService();
        }
        return self::$instance;
    }

    public function getPlayersTable()
    {
        return $this->playersTable;
    }

    public function getGamesTable()
    {
        return $this->gamesTable;
    }

    public function getRolesTable()
    {
        return $this->rolesTable;
    }

    public function getSeatsTable()
    {
        return $this->seatsTable;
    }

    public function getSuperadmin()
    {
        return $this->levelSuperAdmin;
    }

    public function getAdministrator()
    {
        return $this->levelAdmin;
    }

    public function getModerator()
    {
        return $this->levelModerator;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function getFids()
    {
        return $this->fids;
    }

    public function isFid($value){
        return in_array(strtolower($value),$this->fids);
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function isRole($rid){
        return in_array(strtolower($rid),$this->roles);
    }

    public function getMaxGameNameLength(){
        return $this->maxGameNameLength;
    }

    public function getGameHostRoleRid(){
        return $this->gameHostRoleRid;
    }

    public function getGameStatusJoinableArray(){
        return $this->gameStatusJoinableArray;
    }

    public function hasJoinableStatus($value){
        return in_array(strtolower($value),$this->gameStatusJoinableArray);
    }

    public function getResultsPerPage(){
        return $this->resultsPerPage;
    }

    public function getResultsPerPageDouble(){
        return $this->resultsPerPage * 2;
    }

    public function getValidGamePhases(){
        return $this->validGamePhases;
    }

    public function isValidGamePhase($value){
        return in_array(strtolower($value),$this->validGamePhases);
    }


    /**
     * @param string $fid
     * @return bool
     */
    public function isInertFactionByFid($fid){
        return SL::Services()->queryService->queryCountInertFactionsByFid($fid) > 0;
    }


    public function getApiRequests(){
        return $this->apiRequests;
    }

    public function getMaxRolesPerGame(){
        return $this->maxRolesPerGame;
    }

    public function getMinRolesPerGame(){
        return $this->minRolesPerGame;
    }


}