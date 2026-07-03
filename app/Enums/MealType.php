<?php

declare(strict_types=1);

namespace App\Enums;

enum MealType: string
{
    case Colazione = 'colazione';
    case Pranzo = 'pranzo';
    case Cena = 'cena';
    case Spuntino = 'spuntino';
}
