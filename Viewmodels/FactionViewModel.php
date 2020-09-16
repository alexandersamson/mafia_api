<?php

namespace Main\Viewmodels;
class FactionViewModel
{
    public $id = null;
    public $fid = "";
    public $name = "";
    public $description = "";
    public $color = "";
    public $imageUrl = null;
    public $winAsWholeFaction = true;
    public $winsWithFactions = [];
    public $revealRolesToFaction = false;
    public $hasFactionChat = false;
    public $listPriority = 0;
    public $powerLevel = 0;
    public $isInert = 0;
    public $deleted = false;

}