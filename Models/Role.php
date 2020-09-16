<?php

namespace Main\Models;
class Role
{
    public $id = "";
    public $rid = "";
    public $name = "";
    public $type = "";
    public $balancePower = "";
    public $description = "";
    public $imageUrl= "";
    public $factionId = "";
    public $abilities = [];
    public $inventory = [];
    public $deleted = false;


    public function __construct(){
    }
}