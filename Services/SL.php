<?php

namespace Main\Services;
use Main\Services\GlobalsService;
use Main\Connection\Connection;
use Main\Services\ObjectService;
use Main\Services\GameService;
use Main\Services\RoleService;
use Main\Services\PlayerService;
use Main\Services\SeatService;
use Main\Services\FactionService;
use Main\Services\GamePhaseService;
use Main\Services\PlayerPackageService;
use Main\Services\FormatService;
use Main\Services\JsonBuilderService;
use Main\Services\MessageService;
use Main\Services\ValidationService;
use Main\Services\QueryService;
use Main\Services\PaginationService;
use Main\Services\AbilityService;
use Main\Services\RandGenService;


class SL
{
    private static $instance = null;
    public $globals;
    public $connection;
    public $objectService;
    public $gameService;
    public $roleService;
    public $playerService;
    public $seatService;
    public $factionService;
    public $gamePhaseService;
    public $playerPackageService;
    public $formatService;
    public $jsonBuilderService;
    public $messageService;
    public $validationService;
    public $queryService;
    public $paginationService;
    public $abilityService;
    public $randGenService;


    private function __construct()
    {
        $this->globals = GlobalsService::getInstance();
        $this->connection = Connection::getInstance();
        $this->objectService = ObjectService::getInstance();
        $this->gameService = new GameService();
        $this->roleService = new RoleService();
        $this->playerService = new PlayerService();
        $this->seatService = new SeatService();
        $this->factionService = new FactionService();
        $this->gamePhaseService = new GamePhaseService();
        $this->abilityService = new AbilityService();
        $this->playerPackageService = new PlayerPackageService();
        $this->formatService = new FormatService();
        $this->validationService = new ValidationService();
        $this->queryService = new QueryService();
        $this->paginationService = new PaginationService();
        $this->jsonBuilderService = JsonBuilderService::getInstance();
        $this->messageService = MessageService::getInstance();
        $this->randGenService = RandGenService::getInstance();
    }

    public static function Services()
    {
        if (self::$instance == null) {
            self::$instance = new SL();
        }
        return self::$instance;
    }
}