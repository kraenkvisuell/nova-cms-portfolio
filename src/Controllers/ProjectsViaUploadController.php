<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Controllers;

use Kraenkvisuell\NovaCmsPortfolio\Facades\ProjectFolderUpload;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;

class ProjectsViaUploadController
{
    public function store(Artist $artist)
    {
        return ProjectFolderUpload::handle($artist, request()->all());
    }
}
