<?php
/**
 * Tweakwise & Emico (https://www.tweakwise.com/ & https://www.emico.nl/) - All Rights Reserved
 *
 * @copyright Copyright (c) 2017-2017 Tweakwise.com B.V. (https://www.tweakwise.com)
 * @license   Proprietary and confidential, Unauthorized copying of this file, via any medium is strictly prohibited
 */

namespace Emico\Tweakwise\Model\Catalog\Layer;

use Emico\Tweakwise\Exception\TweakwiseException;
use Emico\Tweakwise\Model\Catalog\Product\CollectionFactory;
use Emico\Tweakwise\Model\Client\RequestFactory;
use Emico\Tweakwise\Model\Config;
use Emico\TweakwiseExport\Model\Logger;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ItemCollectionProviderInterface
     */
    protected $originalProvider;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * Proxy constructor.
     *
     * @param Config $config
     * @param Logger $log
     * @param ItemCollectionProviderInterface $originalProvider
     * @param CollectionFactory $collectionFactory
     * @param RequestFactory $requestFactory
     */
    public function __construct(Config $config, Logger $log, ItemCollectionProviderInterface $originalProvider, CollectionFactory $collectionFactory, RequestFactory $requestFactory)
    {
        $this->config = $config;
        $this->log = $log;
        $this->collectionFactory = $collectionFactory;
        $this->originalProvider = $originalProvider;
        $this->requestFactory = $requestFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(Category $category)
    {
        if (!$this->config->isLayeredEnabled()) {
            return $this->originalProvider->getCollection($category);
        }

        try {
            $collection = $this->collectionFactory->create(['requestFactory' => $this->requestFactory]);
            $collection->addCategoryFilter($category);
            return $collection;
        } catch (TweakwiseException $e) {
            $this->log->critical($e);
            $this->config->setTweakwiseExceptionThrown();

            return $this->originalProvider->getCollection($category);
        }
    }
}
