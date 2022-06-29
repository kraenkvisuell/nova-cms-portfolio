<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Services;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsMedia\API;
use Kraenkvisuell\NovaCmsMedia\Core\Model as MediaModel;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;
use Kraenkvisuell\NovaCmsPortfolio\Models\Work;
use ZipArchive;

class ProjectsZipUpload
{
    protected $okExtensions = ['jpg', 'png', 'gif', 'mp4', 'jpeg'];

    protected $artist;

    protected $tmpFolder;

    public function handle(Artist $artist, $path)
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_input_time', 1800);
        ini_set('max_execution_time', 1800);

        $this->artist = $artist;

        $zipperPath = storage_path('app/'.$path);

        $this->tmpFolder = 'tmp/'.Str::random(20);

        $zip = new ZipArchive;
        if ($zip->open($zipperPath) === true) {
            $zip->extractTo(storage_path('app/'.$this->tmpFolder));
            $zip->close();

            $folders = Storage::disk('local')->directories($this->tmpFolder);

            if (config('nova-cms-portfolio.projects_zip_starts_with_categories')) {
                $this->importCategories($folders);
            } else {
                $this->importSlideshows($folders);
            }

            Storage::disk('local')->delete($path);
            Storage::disk('local')->deleteDirectory($this->tmpFolder);
        }
    }

    protected function importCategories($folders)
    {
        foreach ($folders as $folder) {
            $folderName = Str::afterLast($folder, '/');
            if (
                ! Str::startsWith($folderName, '_')
                && ! Str::startsWith($folderName, '.')
            ) {
                $this->importCategory($folderName);
            }
        }
    }

    protected function importCategory($folderName)
    {
        $folders = Storage::disk('local')->directories($this->tmpFolder.'/'.$folderName);

        $categoryName = str_replace(':', '/', $folderName);
        $category = Category::firstOrCreate(
            [
                'title->'.app()->getLocale() => $categoryName,
            ],
            [
                'slug' => Str::slug(str_replace(':', '-', $folderName)),
            ]
        );

        $this->importSlideshows($folders, $category->id);
    }

    protected function importSlideshows($folders, $categoryId = null)
    {
        foreach ($folders as $folder) {
            $folderName = Str::afterLast($folder, '/');
            if (
                ! Str::startsWith($folderName, '_')
                && ! Str::startsWith($folderName, '.')
            ) {
                $this->importSlideshow($folder, $categoryId);
                //ImportSlideshow::dispatch($this->artist, $folder, $categoryId);
            }
        }
    }

    protected function importSlideshow($folder, $categoryId)
    {
        $folderName = Str::afterLast($folder, '/');
        $slideshowName = str_replace(':', '/', $folderName);
        $slideshowName = str_replace('(no special order)', '', $slideshowName);
        $slideshowName = str_replace('(video + photos)', '', $slideshowName);
        $slideshowName = str_replace('(video + photos no special order)', '', $slideshowName);
        $slideshowName = trim($slideshowName);

        $files = Storage::disk('local')->files($folder);

        $slideshow = Slideshow::firstOrCreate(
            [
                'artist_id' => $this->artist->id,
                'title' => $slideshowName,
            ],
            [
                'slug' => Str::slug(str_replace('/', '-', $slideshowName)),
                'robots' => [
                    'index' => true,
                    'follow' => true,
                ],
            ]
        );

        if ($categoryId) {
            $slideshow->categories()->syncWithoutDetaching($categoryId);
        }

        sort($files);
        //ray($files);
        foreach ($files as $file) {
            $fileName = Str::afterLast($file, '/');
            $extension = Str::afterLast($file, '.');
            if (! Str::startsWith($fileName, '.') && in_array($extension, $this->okExtensions)) {
                $this->importFile($file, $slideshow);
            }
        }
    }

    protected function importFile($file, $slideshow)
    {
        $fileName = Str::afterLast($file, '/');
        $newFilename = $fileName;

        if (
            ! stristr($newFilename, $this->artist->slug)
            && ! stristr($newFilename, str_replace('-', '_', $this->artist->slug))
        ) {
            $newFilename = str_replace('-', '_', $this->artist->slug).'_'.$newFilename;
        }

        $mediaItem = MediaModel::where('original_name', $fileName)->first();

        if (! $mediaItem) {
            try {
                $mediaItem = API::upload(storage_path('app/'.$file), null, $newFilename);
            } catch (Exception $e) {
            }
        }

        if ($mediaItem) {
            $work = Work::firstOrCreate(
                [
                    'file' => $mediaItem->id,
                ],
                [
                    'slideshow_id' => $slideshow->id,
                ]
            );
        }
    }
}
