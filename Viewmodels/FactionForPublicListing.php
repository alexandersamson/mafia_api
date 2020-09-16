<?php

namespace Main\Viewmodels;
use Main\Models\Faction;
use Main\Services\GlobalsService;

class FactionForPublicListing
{
    public $id;
    public $fid;
    public $name;
    public $description;
    public $color;
    public $imageUrl;
    public $winAsWholeFaction;
    public $winsWithFactions;
    public $revealRolesToFaction;
    public $hasFactionChat;
    public $listPriority;

    public function __construct(Faction $faction = null)
    {
        if(isset($faction)) {
            $this->id = (int)$faction->id ?? null;
            $this->fid = (string)$faction->fid ?? null;
            $this->name = (string)$faction->name ?? null;
            $this->description = (string)$faction->description ?? null;
            $this->color = (string)$faction->color ?? GlobalsService::$factionBaseColor;
            $this->imageUrl = (string)$faction->imageUrl ?? null;
            $this->winAsWholeFaction = (bool)$faction->winAsWholeFaction ?? null;
            $this->winsWithFactions = (array)$faction->winsWithFactions ?? null;
            $this->revealRolesToFaction = (bool)$faction->revealRolesToFaction ?? null;
            $this->hasFactionChat = (bool)$faction->hasFactionChat ?? null;
            $this->listPriority = (int)$faction->listPriority ?? null;
        }
    }
}