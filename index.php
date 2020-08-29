<?php
require 'database/Connection.php';
require 'database/migrations.php';
require 'models/Game.php';
require 'models/Player.php';
require 'models/Role.php';
require 'models/Seat.php';
require 'models/Faction.php';
require 'models/Pagination.php';
require 'viewmodels/GameView.php';
require 'viewmodels/PlayerPublicViewModel.php';
require 'viewmodels/PlayerPackage.php';
require 'services/SL.php';
require 'services/GlobalsService.php';
require 'services/StartupContext.php';
require 'services/PlayerContext.php';
require 'services/JsonBuilderService.php';
require 'services/MessageService.php';
require 'services/RandGenService.php';
require 'services/ValidationService.php';
require 'services/QueryService.php';
require 'services/PaginationService.php';
require 'services/FormatService.php';
require 'services/RoleService.php';
require 'services/ObjectService.php';
require 'services/PlayerService.php';
require 'services/GameService.php';
require 'services/SeatService.php';
require 'services/FactionService.php';
require 'services/PlayerPackageService.php';
require 'controllers/AppController.php';
require 'controllers/JsonPostValidationController.php';
require 'controllers/GameController.php';
require 'controllers/PlayerController.php';
require 'controllers/RoleController.php';

session_start();

//'instantiate' Context Services
SL::Services(); //Service Loader class
StartupContext::getInstance();
PlayerContext::getInstance();

if(isset($_POST['api'])){
    $_POST = json_decode($_POST['api'], true);
} else {
    $_POST = json_decode(file_get_contents('php://input'), true);
}

$jsonPostValidationController = new JsonPostValidationController();
$roleController = new RoleController();
$gameController = new GameController();
$playerController = new PlayerController();
$appController = new AppController();

if($jsonPostValidationController->validate($_POST)){
    checkForReturningPlayer();
    parsePostData();
} else {
    MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
}

function checkForReturningPlayer(){
    if(isset($_POST["player"]["name"]) && isset($_POST["player"]["pid"]))
    {
        PlayerContext::getInstance()->logInPlayer($_POST["player"]["name"], $_POST["player"]["pid"]);
    }
}

function parsePostData(){
    global $roleController;
    global $gameController;
    global $playerController;
    global $appController;

    $temp = [];
    foreach ($_POST["payload"] as $key => $value){
        $temp[SL::Services()->objectService->fromCamelCase($key)] = $value;
    }
    $payload = $temp;
    $request = SL::Services()->objectService->fromCamelCase($_POST["request"]);

    if($request == "get_public_api_key"){
            if(!$appController->getPublicApiKey()) {
                MessageService::getInstance()->add("error", __FUNCTION__." failed.");
                MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
            }
    }

    if($request == "get_role_by_rid"){
        if(isset($payload["rid"])){
            JsonBuilderService::getInstance()->add($roleController->getRoleByRid($payload["rid"]), GlobalsService::$data);
        } else {
            MessageService::getInstance()->add("error","Corrupted JSON data: Missing [rid] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if($request == "get_roles_by_fid"){
        if(isset($payload["fid"])){
            JsonBuilderService::getInstance()->add($roleController->getRolesByFid($payload["fid"]), GlobalsService::$data);
        } else {
            MessageService::getInstance()->add("error","Corrupted JSON data: Missing [fid] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if($request == "get_all_roles"){
        foreach ($roleController->getAllRoles() as $key => $value){
            JsonBuilderService::getInstance()->add($value,GlobalsService::$data);
        }
    }

    if($request == "get_initial_roles_for_game"){
        if(isset($payload["gid"])){
            foreach ($roleController->getInitialRolesForGame($payload["gid"]) as $key => $value) {
                JsonBuilderService::getInstance()->add($value, GlobalsService::$data);
            }
        } else {
            MessageService::getInstance()->add("error","Corrupted JSON data: Missing [gid] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if($request == "get_available_roles_for_game"){
        if(isset($payload["gid"])){
            foreach ($roleController->getAvailableRolesForGame($payload["gid"]) as $key => $value) {
                JsonBuilderService::getInstance()->add($value, GlobalsService::$data);
            }
        } else {
            MessageService::getInstance()->add("error","Corrupted JSON data: Missing [gid] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if($request == "get_used_roles_for_game"){
        if(isset($payload["gid"])){
            foreach ($roleController->getUsedRolesForGame($payload["gid"]) as $key => $value) {
                JsonBuilderService::getInstance()->add($value, GlobalsService::$data);
            }
        } else {
            MessageService::getInstance()->add("error","Corrupted JSON data: Missing [gid] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if($request == "get_joinable_games_page"){
            $gameController->getJoinableGamesPage(isset($payload["page"]) ? $payload["page"] : 1);
    }
    
    if($request == "create_game") {
        if (isset($payload["name"]) && isset($payload["roles"])) {
            $gameController->create($payload["name"], $payload["roles"], isset($payload["options"]) ? $payload["options"] : null);
        }
    }

    if($request == "join_game"){
        if(isset($payload["gid"]))
        {
            if($gameController->join($payload["gid"],isset($payload["entered_game_pin"]) ? $payload["entered_game_pin"] : "")){
                MessageService::getInstance()->add("userSuccess", "You have joined the game!");
            }
        } else {
            MessageService::getInstance()->add("error","Corrupted JSON data: Missing [gid] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if($request == "get_player_by_name_and_pid"){
        if(isset($payload["name"]) && isset($payload["pid"])){
            JsonBuilderService::getInstance()->add($playerController->getPlayerByNameAndPid($payload["name"],$payload["pid"]), GlobalsService::$data);
        } else {
            MessageService::getInstance()->add("error","Corrupted JSON data: Missing [name] and/or [pid] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if($request == "get_player_package"){
        $playerController->getPlayerPackage();
    }

    if($request == "create_player"){
        if(isset($payload["name"]))
        {
            $player = $playerController->create($payload["name"]);
            if($player) {
                PlayerContext::getInstance()->setCurrentPlayerByObject($player);
            }
        } else {
            MessageService::getInstance()->add("error","Corrupted JSON data: Missing [name] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }
}
SL::Services()->connection->close();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
print_r(JsonBuilderService::getInstance()->ConsumeJson());

