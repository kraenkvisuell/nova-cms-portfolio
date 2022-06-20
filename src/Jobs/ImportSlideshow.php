<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsMedia\API;
use Kraenkvisuell\NovaCmsMedia\Core\Model as MediaModel;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;
use Kraenkvisuell\NovaCmsPortfolio\Models\Work;

class ImportSlideshow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $okExtensions = ['jpg', 'png', 'gif', 'mp4', 'jpeg'];
    protected $artist;
    protected $folder;
    protected $categoryId;

    public function __construct($artist, $folder, $categoryId = null)
    {
        $this->artist = $artist;
        $this->folder = $folder;
        $this->categoryId = $categoryId;
    }

    public function handle()
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_input_time', 1800);
        ini_set('max_execution_time', 1800);

        $folderName = Str::afterLast($this->folder, '/');
        $slideshowName = str_replace(':', '/', $folderName);
        $slideshowName = str_replace('(no special order)', '', $slideshowName);
        $slideshowName = str_replace('(video + photos)', '', $slideshowName);
        $slideshowName = trim($slideshowName);

        $files = Storage::disk('local')->files($this->folder);

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

        if ($this->categoryId) {
            $slideshow->categories()->syncWithoutDetaching($this->categoryId);
        }

        $this->importFiles($slideshow, $files);
    }

    protected function importFiles($slideshow, $files)
    {
        sort($files);
        //ray($files);
        foreach ($files as $file) {
            $fileName = Str::afterLast($file, '/');
            $extension = Str::afterLast($file, '.');
            if (!Str::startsWith($fileName, '.') && in_array($extension, $this->okExtensions)) {
                $this->importFile($slideshow, $file);
            }
        }
    }

    protected function importFile($slideshow, $file)
    {
        $fileName = Str::afterLast($file, '/');
        $newFilename = $fileName;

        if (
            !stristr($newFilename, $this->artist->slug)
            && !stristr($newFilename, Str::snake($this->artist->slug))
        ) {
            $newFilename = Str::snake($this->artist->slug) . '_' . $newFilename;
        }

        $mediaItem = MediaModel::where('original_name', $fileName)->first();

        if (!$mediaItem) {
            try {
                $mediaItem = API::upload(storage_path('app/' . $file), null, $newFilename);
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
