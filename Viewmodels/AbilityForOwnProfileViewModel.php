<?php

namespace Main\Viewmodels;

use Main\Models\Ability;

class AbilityForOwnProfileViewModel extends AbilityForPublicListing
{

    public function __construct(Ability $ability = null)
    {
        parent::__construct($ability);
    }
}