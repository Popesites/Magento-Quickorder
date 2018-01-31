<?php

namespace Popesites\Quickorder\Block;

use Magento\Customer\Model\Context;

/**
 * Sales order view block
 *
 * Is used to show Quick order template in customer cabinet
 *
 * @category Api
 * @package  Popesites\Quickorder\Block
 * @author Popesites <info@popesites.tech>
 **/
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string $_template
     */
    protected $_template = 'quickorder/view.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 100.2.0
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Prepare layout and set title
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Quick Order'));
    }


    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder() {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl() {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->getUrl('customer/account');
        }
        return $this->getUrl('*/*/form');
    }

    /**
     * Returns url for place quick order
     *
     * @param object $order
     * @return string
     */
    public function getPlaceQuickorderUrl($order) {

        return $this->getUrl('', ['order_id' => $order->getId()]);
    }

    /**
     * Returns url to get product by
     *
     * @param string $productSku
     * @return object
     */
    public function getProductUrl($productSku) {
        return $this->getUrl('catalog/product/', ['productSku' => $productSku]);
    }

}