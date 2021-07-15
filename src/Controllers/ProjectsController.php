<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Controllers;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsMedia\API;
use Kraenkvisuell\NovaCmsMedia\Core\Model as MediaModel;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;
use Kraenkvisuell\NovaCmsPortfolio\Models\Work;
use ZipArchive;

class ProjectsController
{
    protected $artist;

    protected $okExtensions = ['jpg', 'png', 'gif', 'mp4', 'jpeg'];

    public function createFromZipFile(Artist $artist)
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_input_time', 1800);
        ini_set('max_execution_time', 1800);

        $this->artist = $artist;

        $path = request()->file('file')->store('tmp', 'local');

        $zipperPath = storage_path('app/'.$path);

        $tmpFolder = 'tmp/'.Str::random(20);

        $zip = new ZipArchive;
        if ($zip->open($zipperPath) === true) {
            $zip->extractTo(storage_path('app/'.$tmpFolder));
            $zip->close();

            $folders = Storage::disk('local')->directories($tmpFolder);
            foreach ($folders as $folder) {
                $folderName = Str::afterLast($folder, '/');
                if (
                    ! Str::startsWith($folderName, '_')
                    && ! Str::startsWith($folderName, '.')
                ) {
                    $this->importFolder($folder);
                }
            }
        }

        Storage::disk('local')->delete($path);
        Storage::disk('local')->delete($tmpFolder);

        return ['success' => $path];
    }

    protected function importFolder($path)
    {
        $slideshowName = Str::afterLast($path, '/');

        $files = Storage::disk('local')->files($path);

        $slideshow = Slideshow::firstOrCreate(
            [
                'artist_id' => $this->artist->id,
                'slug' => Str::slug($slideshowName),
            ],
            [
                'title' => $slideshowName,
                'robots' => [
                    'index' => true,
                    'follow' => true,
                ],
            ]
        );

        $this->importFiles($slideshow, $files);
    }

    protected function importFiles($slideshow, $files)
    {
        foreach ($files as $file) {
            $fileName = Str::afterLast($file, '/');
            $extension = Str::afterLast($file, '.');
            if (! Str::startsWith($fileName, '.') && in_array($extension, $this->okExtensions)) {
                $this->importFile($slideshow, $file);
            }
        }
    }

    protected function importFile($slideshow, $file)
    {
        $fileName = Str::afterLast($file, '/');
        $newFilename = $this->artist->slug.'-'.$fileName;

        $mediaItem = MediaModel::where('original_name', $newFilename)->first();

        if (! $mediaItem) {
            try {
                $mediaItem = API::upload(storage_path('app/'.$file), null, $newFilename);
            } catch (Exception $e) {
            }
        }

        if ($mediaItem) {
            $work = Work::where('file', $mediaItem->id)->first();
            if (! $work) {
                $work = Work::create([
                    'slideshow_id' => $slideshow->id,
                    'file' => $mediaItem->id,
                ]);
            }
        }
    }
}
