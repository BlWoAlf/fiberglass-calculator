<?php

namespace App\Enums;

enum MaterialType: string
{
    case Mat = 'mat';
    case Roving = 'roving';
    case Twill = 'twill';
    case Veil = 'veil';
    case Resin = 'resin';
    case Hardener = 'hardener';
    case Gelcoat = 'gelcoat';
    case Filler = 'filler';
    case Core = 'core';
}