<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\System\Config;

/**
 * Flat product on/off backend
 */
class Mode extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_productFlatIndexerProcessor;

    /**
     * @var \Magento\Indexer\Model\Indexer\State
     */
    protected $indexerState;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Indexer\Model\Indexer\State $indexerState
     * @param \Magento\Framework\Model\ModelResource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Indexer\Model\Indexer\State $indexerState,
        \Magento\Framework\Model\ModelResource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->indexerState = $indexerState;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Set after commit callback
     *
     * @return $this
     */
    public function afterSave()
    {
        $this->_getResource()->addCommitCallback([$this, 'processValue']);
        return $this;
    }

    /**
     * Process flat enabled mode change
     *
     * @return void
     */
    public function processValue()
    {
        if ((bool)$this->getValue() != (bool)$this->getOldValue()) {
            if ((bool)$this->getValue()) {
                $this->indexerState->loadByIndexer(\Magento\Catalog\Model\Indexer\Product\Flat\Processor::INDEXER_ID);
                $this->indexerState->setStatus(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID);
                $this->indexerState->save();
            } else {
                $this->_productFlatIndexerProcessor->getIndexer()->setScheduled(false);
            }
        }
    }
}
