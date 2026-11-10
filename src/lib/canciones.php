<?php

function duracionLegible(int $total): string
{
    $minutes = intdiv($total, 60);
    $seconds = $total % 60;
    return "$minutes minuto(s) y $seconds segundo(s)";
}