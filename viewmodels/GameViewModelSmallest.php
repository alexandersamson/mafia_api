<?php


class GameViewModelSmallest
{
    public $id = 0;               //model
    public $gid = "";               //model
    public $name = "";              //model
    public $status = "";            //model

    public function __construct(Game $game = null){
        $this->id = $game->id ?? null;
        $this->gid = $game->gid ?? null;
        $this->name = $game->name ?? null;
        $this->status = $game->status ?? null;
    }
}