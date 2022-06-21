<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsMedia\API;
use Kraenkvisuell\NovaCmsMedia\Core\Model as MediaModel;
use Kraenkvisuell\NovaCmsPortfolio\Models\Work;

class ImportSlideshowFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $okExtensions = ['jpg', 'png', 'gif', 'mp4', 'jpeg'];
    protected $artist;
    protected $slideshow;
    protected $files;

    public function __construct($artist, $slideshow, $files)
    {
        $this->artist = $artist;
        $this->slideshow = $slideshow;
        $this->files = $files;
    }

    public function handle()
    {
        sort($this->files);
        //ray($files);
        foreach ($this->files as $file) {
            $fileName = Str::afterLast($file, '/');
            $extension = Str::afterLast($file, '.');
            if (!Str::startsWith($fileName, '.') && in_array($extension, $this->okExtensions)) {
                $this->importFile($file);
            }
        }
    }

    protected function importFile($file)
    {
        $fileName = Str::afterLast($file, '/');
        $newFilename = $fileName;

        if (
            !stristr($newFilename, $this->artist->slug)
            && !stristr($newFilename, str_replace('-', '_', $this->artist->slug))
        ) {
            $newFilename = str_replace('-', '_', $this->artist->slug) . '_' . $newFilename;
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
                    'slideshow_id' => $this->slideshow->id,
                ]
            );
        }
    }
}
