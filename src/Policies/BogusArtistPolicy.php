<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;

class BogusArtistPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Artist $artist)
    {
        if ($user->cms_role == 'artist') {
            return $user->artist_id == $artist->id;
        }

        return true;
    }

    public function create(User $user)
    {
        return true;
    }
}
