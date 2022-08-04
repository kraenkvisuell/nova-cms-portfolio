<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsMedia\API;
use Kraenkvisuell\NovaCmsMedia\Core\Model as MediaModel;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;
use Kraenkvisuell\NovaCmsPortfolio\Models\SlideshowFolderUpload;
use Kraenkvisuell\NovaCmsPortfolio\Models\Work;

class ProjectFolderUpload
{
    protected $okExtensions = ['jpg', 'png', 'gif', 'mp4', 'jpeg'];

    public function handle(Artist $artist, $data)
    {
        $filename = Str::afterLast($data['originalPath'], '/');

        $response = [
            'status' => 'success',
            'reason' => '',
            'path' => Str::after($data['originalPath'], '/'),
            'filename' => $filename,
            'category' => '',
            'slideshow' => '',
        ];
        ray($data);

        $pathArr = explode('/', $data['originalPath']);

        if (Str::startsWith($filename, '.')) {
            $response['status'] = 'not_uploaded';
            $response['reason'] = 'hidden file';
        }

        if ($response['status'] != 'not_uploaded' && count($pathArr) != 4) {
            $response['status'] = 'not_uploaded';
            $response['reason'] = 'wrong folder depth';
        }

        $extension = Str::afterLast($filename, '.');
        if ($response['status'] != 'not_uploaded' && ! in_array($extension, $this->okExtensions)) {
            $response['status'] = 'not_uploaded';
            $response['reason'] = 'wrong filetype';
        }

        if ($response['status'] != 'not_uploaded' && $data['size'] > 50000000) {
            $response['status'] = 'not_uploaded';
            $response['reason'] = 'file too large';
        }

        $rootFolder = $pathArr[0];

        $upload = SlideshowFolderUpload::make([
            'status' => $response['status'],
            'reason' => $response['reason'],
            'uuid' => $data['uuid'],
            'root_folder' => $rootFolder,
        ]);

        if ($response['status'] == 'not_uploaded') {
            $upload->save();

            return $response;
        }

        $category = $this->importCategory($artist, $pathArr[1]);
        $slideshow = $this->importSlideshow($artist, $category, $pathArr[2]);

        $upload->category = $category?->title;
        $upload->slideshow = $slideshow?->title;

        $response['category'] = $upload->category;
        $response['slideshow'] = $upload->slideshow;

        $upload->save();

        $fileResponse = $this->importFile($artist, $slideshow, $data['file'], $filename);
        $response['status'] = $fileResponse['status'];
        $response['reason'] = $fileResponse['reason'];

        return $response;
    }

    protected function importCategory($artist, $folderName)
    {
        $categoryName = str_replace(':', '/', $folderName);
        $slug = Str::slug(str_replace(':', '-', $folderName));

        $category = Cache::remember(
            'portfolio.uploaded_category.'.$slug,
            now()->addSeconds(1),
            function () use ($categoryName, $slug, $artist) {
                $category = Category::where('slug->'.app()->getLocale(), $slug)->first();

                if (! $category) {
                    $category = Category::create([
                        'slug->'.app()->getLocale() => $slug,
                        'title->'.app()->getLocale() => $categoryName,
                    ]);
                }

                $artist->categories()->syncWithoutDetaching($category->id);

                return $category;
            }
        );

        return $category;
    }

    protected function importSlideshow($artist, $category, $folderName)
    {
        $slideshowName = str_replace(':', '/', $folderName);
        $slideshowName = str_replace('(no special order)', '', $slideshowName);
        $slideshowName = str_replace('(video + photos)', '', $slideshowName);
        $slideshowName = str_replace('(video + photos no special order)', '', $slideshowName);
        $slideshowName = trim($slideshowName);

        $slug = Str::slug(str_replace('/', '-', $slideshowName));

        $slideshow = Slideshow::firstOrCreate(
            [
                'artist_id' => $artist->id,
                'title' => $slideshowName,
            ],
            [
                'slug' => $slug,
                'robots' => [
                    'index' => true,
                    'follow' => true,
                ],
            ]
        );

        $slideshow->categories()->syncWithoutDetaching($category->id);

        return $slideshow;
    }

    protected function importFile($artist, $slideshow, $file, $filename)
    {
        $response = [
            'status' => 'not_uploaded',
            'reason' => 'unknown',
        ];

        $newFilename = $filename;

        if (
            ! stristr($newFilename, $artist->slug)
            && ! stristr($newFilename, str_replace('-', '_', $artist->slug))
        ) {
            $newFilename = str_replace('-', '_', $artist->slug).'_'.$newFilename;
        }

        $mediaItem = MediaModel::where('original_name', $filename)->first();
        Log::debug($filename);
        Log::debug($mediaItem);
        if (! $mediaItem) {
            try {
                $tmpPath = Storage::putFileAs('tmp/portfolio-uploads', $file, $filename);
                $mediaItem = API::upload(storage_path('app/'.$tmpPath), null, $newFilename);
                $response['status'] = 'success';
                $response['reason'] = '';
            } catch (Exception $e) {
                Log::debug($e);
            }
        } else {
            $response['reason'] = 'already exists';
        }

        if ($mediaItem) {
            Work::firstOrCreate(
                [
                    'file' => $mediaItem->id,
                    'slideshow_id' => $slideshow->id,
                ]
            );
        }

        return $response;
    }
}
