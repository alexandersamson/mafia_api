<?php
namespace Main;

define('_VALIDENTRY', 1);

require_once 'Services/CheckValidAccess.php';
require __DIR__ . '/vendor/autoload.php';

use Main\Controllers\AppController;
use Main\Controllers\GameController;
use Main\Controllers\JsonPostValidationController;
use Main\Controllers\PlayerController;
use Main\Controllers\RoleController;
use Main\Services\SL;
use Main\Services\StartupContext;
use Main\Services\PlayerContext;

require 'Connection/Connection.php';
require 'Connection/migrations.php';
require 'Models/Game.php';
require 'Models/Player.php';
require 'Models/Role.php';
require 'Models/Seat.php';
require 'Models/Faction.php';
require 'Models/Pagination.php';
require 'Models/PlayerToken.php';
require 'Models/GamePhase.php';
require 'Models/Ability.php';
require 'Viewmodels/GameView.php';
require 'Viewmodels/GameOverviewViewModel.php';
require 'Viewmodels/GameViewModelSmallest.php';
require 'Viewmodels/PlayerViewModelPublic.php';
require 'Viewmodels/PlayerViewModelPublicExtended.php';
require 'Viewmodels/PlayerViewModelGameOverview.php';
require 'Viewmodels/PlayerViewModelTokenizedPublic.php';
require 'Viewmodels/PlayerPackage.php';
require 'Viewmodels/GamePhaseSmallViewModel.php';
require 'Viewmodels/SeatViewModelRoleProfile.php';
require 'Viewmodels/RoleForOwnProfileViewModel.php';
require 'Viewmodels/RoleForPublicListing.php';


SL::Services(); //Service Loader class

/** @var string $corsAllowOrigin */
header("Access-Control-Allow-Origin: ".SL::Services()->globals::$corsAllowOrigin);

StartupContext::getInstance();
PlayerContext::getInstance();

if(isset($_POST['api'])){
    $_POST = json_decode($_POST['api'], true);
} else {
    $_POST = json_decode(file_get_contents('php://input'), true);
}

$jsonPostValidationController = new JsonPostValidationController();

$appController = new AppController();
$globals = SL::Services()->globals;
$jsonBuilder = SL::Services()->jsonBuilderService;
$messageService = SL::Services()->messageService;
$connection = SL::Services()->connection;


//The only [GET] method available. Only used to obtain a Public API key.
if(isset($_GET['get_public_api_key']) || isset($_GET['getPublicApiKey'])){
    $appController->getPublicApiKey();
    $connection->close();
    print_r($jsonBuilder->ConsumeJson());
    exit();
}


if($jsonPostValidationController->validate($_POST)){
    tryLogin();
    parsePostData();
} else {
    $messageService->add("userError", $messageService->genericUserError);
}



header("Content-Type: application/json; charset=UTF-8");


function tryLogin(){
    global $globals;
    if(isset($_POST[$globals::$tokenNameAlt]) || isset($_POST[$globals::$tokenName])){
        if(isset($_POST[$globals::$tokenNameAlt])){
            $_POST[$globals::$tokenName] = $_POST[$globals::$tokenNameAlt];
        }
        PlayerContext::getInstance()->logInPlayer($_POST[$globals::$tokenName]);
    }
}


function parsePostData()
{
    $roleController = new RoleController();
    $gameController = new GameController();
    $playerController = new PlayerController();
    $globals = SL::Services()->globals;
    global $appController;
    global $messageService;
    global $jsonBuilder;

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
            $messageService->add("error", __FUNCTION__ . " failed.");
            $messageService->add("userError", $messageService->genericUserError);
        }
    }

    if ($request == "get_role_by_rid") {
        if (isset($payload["rid"])) {
            $jsonBuilder->add($roleController->getRoleByRid($payload["rid"]), $globals::$jbData);
        } else {
            $messageService->add("error", "Corrupted payload: Missing [rid] in the payload");
            $messageService->add("userError", $messageService->genericUserError);
        }
    }

    if ($request == "get_roles_by_fid") {
        if (isset($payload["fid"])) {
            $jsonBuilder->add($roleController->getRolesByFid($payload["fid"]), $globals::$jbData);
        } else {
            $messageService->add("error", "Corrupted payload: Missing [fid] in the payload");
            $messageService->add("userError", $messageService->genericUserError);
        }
    }

    if ($request == "get_all_roles") {
        $roleController->getAllRoles();
    }

    if ($request == "get_initial_roles_for_game") {
        if (isset($payload["gid"])) {
            foreach ($roleController->getInitialRolesForGame($payload["gid"]) as $key => $value) {
                $jsonBuilder->add($value, $globals::$jbData);
            }
        } else {
            $messageService->add("error", "Corrupted payload: Missing [gid] in the payload");
            $messageService->add("userError", $messageService->genericUserError);
        }
    }

    if ($request == "get_available_roles_for_game") {
        if (isset($payload["gid"])) {
            foreach ($roleController->getAvailableRolesForGame($payload["gid"]) as $key => $value) {
                $jsonBuilder->add($value, $globals::$jbData);
            }
        } else {
            $messageService->add("error", "Corrupted payload: Missing [gid] in the payload");
            $messageService->add("userError", $messageService->genericUserError);
        }
    }

    if ($request == "get_used_roles_for_game") {
        if (isset($payload["gid"])) {
            foreach ($roleController->getUsedRolesForGame($payload["gid"]) as $key => $value) {
                $jsonBuilder->add($value, $globals::$jbData);
            }
        } else {
            $messageService->add("error", "Corrupted payload: Missing [gid] in the payload");
        }
    }

    //cp = current player
    if ($request == "cp_get_game_overview") {
        if(!$gameController->getGameOverviewForCurrentPlayer()){
            $jsonBuilder->add(["error" => "Cannot retrieve game. Are you logged in?"], $globals::$jbError);
        }
    }

    //cp = current player
    if ($request == "cp_get_players_game_overview") {
        if(!$playerController->getPlayerOverviewForCurrentPlayersGame()){
            $jsonBuilder->add(["error" => "Cannot retrieve players. Are you logged in and in a game?"], $globals::$jbError);
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
            $messageService->add("error", "Corrupted payload: Missing [name] and/or [roles] in the payload");
        }
    }

    if ($request == "join_game") {
        if (isset($payload["gid"])) {
            if ($gameController->join($payload["gid"], isset($payload["entered_game_pin"]) ? $payload["entered_game_pin"] : "")) {
                $messageService->add("userSuccess", "You have joined the game!");
            }
        } else {
            $messageService->add("error", "Corrupted payload: Missing [gid] in the payload");
        }
    }

    if ($request == "get_player_by_token" || $request == "player_by_token") {
        if (isset($_POST['playerToken'])) {
            $playerController->getPlayerByToken($_POST['playerToken']);
        } else {
            $messageService->add("error", "Corrupted JSON: Missing [playerToken] in the JSON object");
            $messageService->add("userError", $messageService->genericUserError);
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
                $messageService->add("error", "Cannot create player");
            }
        } else {
            $messageService->add("error", "Corrupted payload: Missing [name] in the payload");
            $messageService->add("userError", $messageService->genericUserError);
        }
    }
}
$connection->close();
usleep ( rand(100000,200000));
$json = $jsonBuilder->ConsumeJson();
if ($json === false) {
    $json = json_encode(["jsonError" => json_last_error_msg()]);
    if ($json === false) {
        $json = '{"jsonError":"unknown"}';
    }
    http_response_code(500);
}
echo $json;

