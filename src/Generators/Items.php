<?php
namespace Relevanz\Generators;

use ElasticExport\Helper\ElasticExportCoreHelper;
use ElasticExport\Helper\ElasticExportPriceHelper;
use ElasticExport\Helper\ElasticExportStockHelper;
use Plenty\Modules\DataExchange\Contracts\CSVPluginGenerator;
use Plenty\Modules\Helper\Models\KeyValue;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchScrollRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Class Items
 * @package Relevanz\Generators
 */
class Items extends CSVPluginGenerator
{
    use Loggable;

    const DELIMITER = ";";

    /**
     * @var ElasticExportCoreHelper
     */
    private $elasticExportHelper;
    /**
     * @var ElasticExportStockHelper
     */
    private $elasticExportStockHelper;
    /**
     * @var ElasticExportPriceHelper
     */
    private $elasticExportPriceHelper;
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
     * Generates and populates the data into the CSV file.
     *
     * @param VariationElasticSearchScrollRepositoryContract $elasticSearch
     * @param array $formatSettings
     * @param array $filter
     */
    protected function generatePluginContent($elasticSearch, array $formatSettings = [], array $filter = [])
    {
        $this->elasticExportPriceHelper = pluginApp(ElasticExportPriceHelper::class);
        $this->elasticExportStockHelper = pluginApp(ElasticExportStockHelper::class);
        $this->elasticExportHelper      = pluginApp(ElasticExportCoreHelper::class);

        $settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');

        $this->setDelimiter(self::DELIMITER);
        $this->setHeader();

        if ($elasticSearch instanceof VariationElasticSearchScrollRepositoryContract) {
            $limitReached = false;
            $lines        = 0;
            do {
                if ($limitReached === true) {
                    break;
                }
                $resultList = $elasticSearch->execute();
                if (!is_null($resultList['error'])) {
                    $this->getLogger(__METHOD__)->error('ElasticExportTwengaCOM::logs.esError', [
                        'Error message ' => $resultList['error'],
                    ]);
                }
                foreach ($resultList['documents'] as $variation) {
                    if ($lines == $filter['limit']) {
                        $limitReached = true;
                        break;
                    }
                    if (is_array($resultList['documents']) && count($resultList['documents']) > 0) {
                        //if ($this->elasticExportStockHelper->isFilteredByStock($variation, $filter) === true) {
                        //    continue;
                        //}
                        try {
                            $this->buildRow($variation, $settings);
                            $lines = $lines + 1;
                        } catch (\Throwable $throwable) {
                            $this->getLogger(__METHOD__)->error('ElasticExportTwengaCOM::logs.fillRowError', [
                                'Error message ' => $throwable->getMessage(),
                                'Error line' => $throwable->getLine(),
                                'VariationId' => $variation['id']
                            ]);
                        }
                    }
                }
            } while ($elasticSearch->hasNext());
        }
    }

    /**
     * set the current header
     */
    private function setHeader()
    {
        $this->addCSVContent(
            [
                'product_id',
                'category_ids',
                'product_name',
                'short_description',
                'description',
                'price',
                'deeplink',
                'images'
            ]
        );
    }

    /**
     * @param array $variation
     * @param KeyValue $settings
     */
    private function buildRow($variation, $settings)
    {
        // Get the price and retail price
        $priceList = $this->elasticExportPriceHelper->getPriceList($variation, $settings, 2, '.');
        $price     = $priceList['price'];

        $image = $image = $this->elasticExportHelper->getImageListInOrder($variation, $settings, 1, $this->elasticExportHelper::VARIATION_IMAGES);
        if (count($image) > 0) {
            $image = $image[0];
        } else {
            $image = '';
        }

        $data = [
            'product_id' => (int)$variation['id'],
            'category_ids' => (int)$variation['data']['defaultCategories'][0]['id'],
            'product_name' => $this->elasticExportHelper->getMutatedName($variation, $settings, 256),
            'short_description' => $variation['data']['texts']['shortDescription'],
            'description' => $variation['data']['texts']['description'],
            'price' => $price,
            'deeplink' => $this->elasticExportHelper->getMutatedUrl($variation, $settings, false, false),
            'images' => $image,
        ];
        $this->addCSVContent(array_values($data));
    }
}
