<?php
require 'database/Connection.php';
require 'database/migrations.php';
require 'models/Game.php';
require 'models/Player.php';
require 'models/Role.php';
require 'models/Seat.php';
require 'models/Faction.php';
require 'models/Pagination.php';
require 'models/PlayerToken.php';
require 'models/GamePhase.php';
require 'models/Ability.php';
require 'viewmodels/GameView.php';
require 'viewmodels/GameOverviewViewModel.php';
require 'viewmodels/GameViewModelSmallest.php';
require 'viewmodels/PlayerViewModelPublic.php';
require 'viewmodels/PlayerViewModelPublicExtended.php';
require 'viewmodels/PlayerViewModelGameOverview.php';
require 'viewmodels/PlayerViewModelTokenizedPublic.php';
require 'viewmodels/PlayerPackage.php';
require 'viewmodels/GamePhaseSmallViewModel.php';
require 'viewmodels/SeatViewModelRoleProfile.php';
require 'viewmodels/RoleForOwnProfileViewModel.php';
require 'viewmodels/RoleForPublicListing.php';
require 'viewmodels/FactionForOwnProfileViewModel.php';
require 'viewmodels/FactionForPublicListing.php';
require 'viewmodels/AbilityForOwnProfileViewModel.php';
require 'viewmodels/AbilityForPublicListing.php';
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
require 'services/GamePhaseService.php';
require 'services/AbilityService.php';
require 'controllers/AppController.php';
require 'controllers/JsonPostValidationController.php';
require 'controllers/GameController.php';
require 'controllers/PlayerController.php';
require 'controllers/RoleController.php';
require 'controllers/AbilityController.php';

header("Access-Control-Allow-Origin: ".GlobalsService::$corsAllowOrigin);

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


//The only [GET] method available. Only used to obtain a Public API key.
if(isset($_GET['get_public_api_key']) || isset($_GET['getPublicApiKey'])){
    $appController->getPublicApiKey();
    SL::Services()->connection->close();
    print_r(JsonBuilderService::getInstance()->ConsumeJson());
    exit();
}


if($jsonPostValidationController->validate($_POST)){
    tryLogin();
    parsePostData();
} else {
    MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
}



header("Content-Type: application/json; charset=UTF-8");


function tryLogin(){
    if(isset($_POST[GlobalsService::$tokenJsonPropertyNameAlt]) || isset($_POST[GlobalsService::$tokenJsonPropertyName])){
        if(isset($_POST[GlobalsService::$tokenJsonPropertyNameAlt])){
            $_POST[GlobalsService::$tokenJsonPropertyName] = $_POST[GlobalsService::$tokenJsonPropertyNameAlt];
        }
        PlayerContext::getInstance()->logInPlayer($_POST[GlobalsService::$tokenJsonPropertyName]);
    }
}


function parsePostData()
{
    global $roleController;
    global $gameController;
    global $playerController;
    global $appController;

    if(!is_array($_POST["payload"])){
        $tempPL = $_POST["payload"];
        $_POST["payload"] = [];
        array_push($_POST["payload"], $tempPL);
    }

    $temp = [];
    foreach ($_POST["payload"] as $key => $value) {
        $temp[SL::Services()->objectService->fromCamelCase($key)] = $value;
    }
    $payload = $temp;
    $request = SL::Services()->objectService->fromCamelCase($_POST["request"]);

    if ($request == "get_public_api_key") {
        if (!$appController->getPublicApiKey()) {
            MessageService::getInstance()->add("error", __FUNCTION__ . " failed.");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if ($request == "get_role_by_rid") {
        if (isset($payload["rid"])) {
            JsonBuilderService::getInstance()->add($roleController->getRoleByRid($payload["rid"]), GlobalsService::$data);
        } else {
            MessageService::getInstance()->add("error", "Corrupted payload: Missing [rid] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if ($request == "get_roles_by_fid") {
        if (isset($payload["fid"])) {
            JsonBuilderService::getInstance()->add($roleController->getRolesByFid($payload["fid"]), GlobalsService::$data);
        } else {
            MessageService::getInstance()->add("error", "Corrupted payload: Missing [fid] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if ($request == "get_all_roles") {
        $roleController->getAllRoles();
    }

    if ($request == "get_initial_roles_for_game") {
        if (isset($payload["gid"])) {
            foreach ($roleController->getInitialRolesForGame($payload["gid"]) as $key => $value) {
                JsonBuilderService::getInstance()->add($value, GlobalsService::$data);
            }
        } else {
            MessageService::getInstance()->add("error", "Corrupted payload: Missing [gid] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if ($request == "get_available_roles_for_game") {
        if (isset($payload["gid"])) {
            foreach ($roleController->getAvailableRolesForGame($payload["gid"]) as $key => $value) {
                JsonBuilderService::getInstance()->add($value, GlobalsService::$data);
            }
        } else {
            MessageService::getInstance()->add("error", "Corrupted payload: Missing [gid] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if ($request == "get_used_roles_for_game") {
        if (isset($payload["gid"])) {
            foreach ($roleController->getUsedRolesForGame($payload["gid"]) as $key => $value) {
                JsonBuilderService::getInstance()->add($value, GlobalsService::$data);
            }
        } else {
            MessageService::getInstance()->add("error", "Corrupted payload: Missing [gid] in the payload");
        }
    }

    //cp = current player
    if ($request == "cp_get_game_overview") {
        if(!$gameController->getGameOverviewForCurrentPlayer()){
            JsonBuilderService::getInstance()->add(["error" => "Cannot retrieve game. Are you logged in?"], GlobalsService::$error);
        }
    }

    //cp = current player
    if ($request == "cp_get_players_game_overview") {
        if(!$playerController->getPlayerOverviewForCurrentPlayersGame()){
            JsonBuilderService::getInstance()->add(["error" => "Cannot retrieve players. Are you logged in and in a game?"], GlobalsService::$error);
        }
    }

    //cp = current player
    if ($request == "cp_get_role_details") {
        $playerController->getRoleDetailsForCurrentPlayersSeat();
    }


    if ($request == "get_joinable_games_page") {
        $gameController->getJoinableGamesPage(isset($payload["page"]) ? $payload["page"] : 1);
    }

    if ($request == "create_game") {
        if (isset($payload["name"]) && isset($payload["roles"])) {
            $gameController->create($payload["name"], $payload["roles"], isset($payload["options"]) ? $payload["options"] : null);
        } else {
            MessageService::getInstance()->add("error", "Corrupted payload: Missing [name] and/or [roles] in the payload");
        }
    }

    if ($request == "join_game") {
        if (isset($payload["gid"])) {
            if ($gameController->join($payload["gid"], isset($payload["entered_game_pin"]) ? $payload["entered_game_pin"] : "")) {
                MessageService::getInstance()->add("userSuccess", "You have joined the game!");
            }
        } else {
            MessageService::getInstance()->add("error", "Corrupted payload: Missing [gid] in the payload");
        }
    }

    if ($request == "get_player_by_token" || $request == "player_by_token") {
        if (isset($_POST['playerToken'])) {
            $playerController->getPlayerByToken($_POST['playerToken']);
        } else {
            MessageService::getInstance()->add("error", "Corrupted JSON: Missing [playerToken] in the JSON object");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }

    if ($request == "get_available_game_phases" || $request == "get_all_game_phases") {
            $gameController->getAllGamePhases();
    }

    if ($request == "get_player_package") {
        $playerController->getPlayerPackage();
    }

    if ($request == "create_player") {
        if (isset($payload["name"])) {
            if (!$playerController->create($payload["name"])) {
                MessageService::getInstance()->add("error", "Cannot create player");
            }
        } else {
            MessageService::getInstance()->add("error", "Corrupted payload: Missing [name] in the payload");
            MessageService::getInstance()->add("userError", MessageService::getInstance()->genericUserError);
        }
    }
}
SL::Services()->connection->close();
usleep ( rand(100000,200000));
print_r(JsonBuilderService::getInstance()->ConsumeJson());

