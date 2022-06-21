<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Services;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsMedia\API;
use Kraenkvisuell\NovaCmsMedia\Core\Model as MediaModel;
use Kraenkvisuell\NovaCmsPortfolio\Jobs\ImportSlideshow;
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

        $zipperPath = storage_path('app/' . $path);

        $this->tmpFolder = 'tmp/' . Str::random(20);

        $zip = new ZipArchive;
        if ($zip->open($zipperPath) === true) {
            $zip->extractTo(storage_path('app/' . $this->tmpFolder));
            $zip->close();

            $folders = Storage::disk('local')->directories($this->tmpFolder);

            if (config('nova-cms-portfolio.projects_zip_starts_with_categories')) {
                $this->importCategories($folders);
            } else {
                $this->importSlideshows($folders);
            }
        }

        // Storage::disk('local')->delete($path);
        // Storage::disk('local')->delete($this->tmpFolder);
    }

    protected function importCategories($folders)
    {
        foreach ($folders as $folder) {
            $folderName = Str::afterLast($folder, '/');
            if (
                !Str::startsWith($folderName, '_')
                && !Str::startsWith($folderName, '.')
            ) {
                $this->importCategory($folderName);
            }
        }
    }

    protected function importCategory($folderName)
    {
        $folders = Storage::disk('local')->directories($this->tmpFolder . '/' . $folderName);

        $categoryName = str_replace(':', '/', $folderName);
        $category = Category::firstOrCreate(
            [
                'title->' . app()->getLocale() => $categoryName,
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
                !Str::startsWith($folderName, '_')
                && !Str::startsWith($folderName, '.')
            ) {
                ImportSlideshow::dispatch($this->artist, $folder, $categoryId);
            }
        }
    }
}
