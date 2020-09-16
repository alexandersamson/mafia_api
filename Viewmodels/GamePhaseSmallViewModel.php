<?php

namespace Main\Viewmodels;
use Main\Models\GamePhase;

class GamePhaseSmallViewModel
{
    public $id = 0;
    public $name = '';
    public $isNight = true;

    public function __construct(GamePhase $gamePhase = null)
    {
        if(isset($gamePhase)) {
            $this->id = (int)$gamePhase->id;
            $this->name = (string)$gamePhase->name;
            $this->isNight = (bool)$gamePhase->isNight;
        } else {
            $this->id = 1;
            $this->name = 'DAY';
            $this->isNight = false;
        }

    }
}