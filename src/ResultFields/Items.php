<?php
namespace Relevanz\ResultFields;

use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\DataExchange\Contracts\ResultFields;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\Search\Mutators\DefaultCategoryMutator;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;

/**
 * Class Items
 * @package Relevanz\ResultFields
 */
class Items extends ResultFields
{
    /**
     * @var ArrayHelper
     */
    private $arrayHelper;

    /**
     * TwengaCOM constructor.
     *
     * @param ArrayHelper $arrayHelper
     */
    public function __construct(ArrayHelper $arrayHelper)
    {
        $this->arrayHelper = $arrayHelper;
    }

    /**
     * Creates the fields set to be retrieved from ElasticSearch.
     *
     * @param array $formatSettings
     * @return array
     */
    public function generateResultFields(array $formatSettings = []): array
    {
        $settings  = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');
        $reference = $settings->get('referrerId', 1);
        $this->setOrderByList(['variation.itemId', 'ASC']);

        // Mutators
        /**
         * @var ImageMutator $imageMutator
         */
        $imageMutator = pluginApp(ImageMutator::class);
        if ($imageMutator instanceof ImageMutator) {
            $imageMutator->addMarket($reference);
        }

        /**
         * @var DefaultCategoryMutator $defaultCategoryMutator
         */
        $defaultCategoryMutator = pluginApp(DefaultCategoryMutator::class);
        if ($defaultCategoryMutator instanceof DefaultCategoryMutator) {
            $defaultCategoryMutator->setPlentyId($settings->get('plentyId'));
        }

        /**
         * @var LanguageMutator $languageMutator
         */
        $languageMutator = pluginApp(LanguageMutator::class, [[$settings->get('lang')]]);

        // Fields
        $fields = [
            [
                //variation
                'id',
                'item.id',

                //images
                'images.all.url',
                'images.all.path',
                'images.all.position',
                'images.item.url',
                'images.item.path',
                'images.item.position',
                'images.variation.url',
                'images.variation.path',
                'images.variation.position',

                 //texts
                'texts.shortDescription',
                'texts.description',
                'texts.lang',
                'texts.urlPath',
                ($settings->get('nameId')) ? 'texts.name' . $settings->get('nameId') : 'texts.name1',

                //defaultCategories
                'defaultCategories.id',
            ],
            [
                $languageMutator,
                $defaultCategoryMutator
            ],
        ];

        // Get the associated images if reference is selected
        if ($reference != -1) {
            $fields[1][] = $imageMutator;
        }

        return $fields;
    }
}