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
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;

class ImportSlideshow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $slideshowName = str_replace('(video + photos no special order)', '', $slideshowName);
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

        ImportSlideshowFiles::dispatch($this->artist, $slideshow, $files);
    }
}
