<?php

namespace Main\Models;
class Ability
{
    public $id = null;
    public $aid = '';
    public $name = '';
    public $type = '';
    public $description = '';
    public $mustBeActivated = true;
    public $canUseAt = [];
    public $activate_text = '';
    public $priority = 1;
    public $itemsNeeded = [];
    public $givesAbilities = [];
    public $stripsAbilities =[];
    public $worksFromHome = 1;
    public $worksFromAway = 1;
    public $staysHome = 0;
    public $needsTarget = 1;
    public $canTargetSelf = 1;
    public $canTargetOwnFaction = 1;
    public $canTargetOwnRoleType = 1;
    public $canTargetOthers = 1;
    public $targetsTargetAtHome = 1;
    public $targetsTargetAway = 1;
    public $targetsVisitorsOfSelf = 0;
    public $targetsVisitorsOfTarget = 0;
    public $activatesOncePerFaction = 0;
    public $oncePerFactionConcurrency = ''; //random or majority
    public $oncePerFactionFinalSayRoles = [];
    public $effectIsPermanent = 0;
    public $announceUseToPublic = 0;
    public $announceUserToPublic = 0;
    public $customValue = 0;
    public $deleted = 0;

}