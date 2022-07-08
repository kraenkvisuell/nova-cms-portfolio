<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;

class ArtistPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //ray('test');
    }

    public function create(User $user)
    {
        return $user->cms_role != 'artist';
    }

    public function view(User $user, Artist $artist)
    {
        if ($user->cms_role == 'artist') {
            return $user->artist_id == $artist->id;
        }

        return true;
    }

    public function delete(User $user, Artist $artist)
    {
        if ($user->cms_role == 'artist') {
            return $user->artist_id == $artist->id;
        }

        return true;
    }

    public function update(User $user, Artist $artist)
    {
        if ($user->cms_role == 'artist') {
            return $user->artist_id == $artist->id;
        }

        return true;
    }

    public function detachCategory(User $user, Artist $artist)
    {
        return false;
    }

    public function editCategory(User $user, Artist $artist)
    {
        return false;
    }
}
