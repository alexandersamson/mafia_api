<?php

class SL
{
    private static $instance = null;
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
    public $validationService;
    public $queryService;
    public $paginationService;


    private function __construct()
    {
        $this->connection = new Connection();
        $this->objectService = new ObjectService();
        $this->gameService = new GameService();
        $this->roleService = new RoleService();
        $this->playerService = new PlayerService();
        $this->seatService = new SeatService();
        $this->factionService = new FactionService();
        $this->gamePhaseService = new GamePhaseService();
        $this->playerPackageService = new PlayerPackageService();
        $this->formatService = new FormatService();
        $this->validationService = new ValidationService();
        $this->queryService = new QueryService();
        $this->paginationService = new PaginationService();
        $this->jsonBuilderService = JsonBuilderService::getInstance();
    }

    public static function Services()
    {
        if (self::$instance == null) {
            self::$instance = new SL();
        }
        return self::$instance;
    }
}