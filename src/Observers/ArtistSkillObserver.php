<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Observers;

use Kraenkvisuell\NovaCmsPortfolio\Models\ArtistSkill;

class ArtistSkillObserver
{
    public function created(ArtistSkill $artistSkill)
    {
        $artistSkill->moveToStart();
    }
}
