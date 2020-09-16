<?php

namespace Main\Viewmodels;
use Main\Models\Faction;

class FactionForOwnProfileViewModel extends FactionForPublicListing
{

    public function __construct(Faction $faction = null)
    {
        parent::__construct($faction);

    }

}