<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Observers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Services\ArtistCacheService;

class ArtistObserver
{
    public function saved(Artist $artist)
    {
        $this->checkUserCreation($artist);

        ArtistCacheService::refreshCachesWhereNeeded($artist);
    }

    protected function checkUserCreation($artist)
    {
        $original = trim(strtolower($artist->getOriginal('email')));
        $email = trim(strtolower($artist->email));

        if ($original == $email) {
            return;
        }

        if (! $email && $artist->user) {
            return $artist->user->delete();
        }

        if ($artist->user) {
            $artist->user->update([
                'email' => $email,
                'name' => $artist->name,
            ]);
        } else {
            $user = $artist->user()->create([
                'email' => $email,
                'name' => $artist->name,
                //'password' => Hash::make(Str::uuid()),
                'password' => Hash::make('password'),
            ]);

            $user->cms_role = 'artist';
            $user->save();
        }
    }
}
