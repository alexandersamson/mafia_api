<?php


class GamePhase
{
    public $id = 0;
    public $gpid = '';
    public $name = '';
    public $events = [];
    public $isNight = true;
    public $duration = 0;
    public $nextPhaseId = 0;
    public $description = '';
    public $deleted = false;
}