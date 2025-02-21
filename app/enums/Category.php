<?php

namespace Danielyan\Parus\App\Enums;

enum Category: int
{
    /**
     * Можно убрать и добавить нужные, обновить id категории при апдейте API.
     * LandPlots - Земельные участки из ЕГРН из категории "Земельные участки",
     * Building - Здания из категории "Объекты капитального строительства",
     * Facilities - Сооружения из категории "Объекты капитального строительства".
     */
    case LandPlots = 36368;
    case Building = 36369;
    case Facilities = 36383;
}