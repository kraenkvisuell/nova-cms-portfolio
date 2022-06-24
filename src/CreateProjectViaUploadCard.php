<?php
namespace Kraenkvisuell\NovaCmsPortfolio;

use Kraenkvisuell\NovaCmsPortfolio\Models\Category;
use Laravel\Nova\Card;

class CreateProjectViaUploadCard extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'create-project-via-upload-card';
    }

    public function addMeta($artistId = 0)
    {
        return $this->withMeta([
            'artistId' => $artistId,
            'headline' => __('nova-cms-portfolio::create_via_folder_upload.headline'),
            'intro' => __('nova-cms-portfolio::create_via_folder_upload.intro'),
            'categoryIdTitle' => __('nova-cms-portfolio::categories.category'),
            'newCategoryTitle' => __('nova-cms-portfolio::create_via_folder_upload.new_category'),
            'categories' => Category::pluck('title', 'id'),
        ]);
    }
}
