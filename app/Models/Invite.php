<?php

namespace App\Models;

class Invite extends BaseModel
{
    protected $fillable=['email','invited_as','invited_branch','token'];
}
