<?php

namespace Main\Services;
class StartupContext
{
    private static $instance = null;

    private function __construct()
    {
        self::runDbaseMigrations();
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new StartupContext();
        }
        return self::$instance;
    }

    public static function runDbaseMigrations()
    {
        global $migrations;
        SL::Services()->connection->runMigrations($migrations);
    }
}