<?php

namespace Main\Models;
class Player
{
    public $id = null;
    public $pid = null;
    public $name= "";
    public $discriminator = "";
    public $createdOn = 0;
    public $lastSeen = 0;
    public $email = "";
    public $password = "";
    public $gamesPlayed = 0;
    public $gamesHosted = 0;
    public $blocked = false;
    public $deleted = false;
    public $isSuperadmin = false;
    public $isAdmin = false;
    public $isModerator = false;
    public $token = "";
    public $tokenExpiresOn = 0;



}