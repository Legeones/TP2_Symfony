<?php

namespace App\Entity;

enum Roles : string
{
    case Admin = 'ROLE_ADMIN';
    case User = 'ROLE_USER';
    case Support = 'ROLE_SUPPORT';
}