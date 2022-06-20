<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Controllers;

use Kraenkvisuell\NovaCmsPortfolio\Facades\ProjectsZipUpload;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;

class ProjectsController
{
    protected $artist;

    protected $okExtensions = ['jpg', 'png', 'gif', 'mp4', 'jpeg'];

    public function createFromZipFile(Artist $artist)
    {
        $path = request()->file('file')->store('tmp', 'local');

        ProjectsZipUpload::handle($artist, $path);

        return ['success' => true];
    }
}
