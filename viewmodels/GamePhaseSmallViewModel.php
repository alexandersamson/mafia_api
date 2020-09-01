<?php


class GamePhaseSmallViewModel
{
    public $id = 0;
    public $name = '';
    public $isNight = true;

    public function __construct(GamePhase $gamePhase)
    {
        $this->id = $gamePhase->id;
        $this->name = $gamePhase->name;
        $this->isNight = $gamePhase->isNight;

    }
}