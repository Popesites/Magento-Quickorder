<?php

namespace Popesites\Quickorder\Block;

use Magento\Customer\Model\Context;

/**
 * Sales order view block
 *
 * Is used to show Quick order template in customer cabinet
 *
 * @category Popesites
 * @package  Popesites_Quickorder
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
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $product;

    /** @var \Popesites\Quickorder\Helper\Data */
    protected $helper;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    /** @var \Magento\Framework\Data\Form\FormKey */
    protected $formKey;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Popesites\Quickorder\Helper\Data $helper,
        array $data = [],
        \Magento\Framework\Data\Form\FormKey $formKey
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->product = $product;
        $this->helper = $helper;
        $this->formKey = $formKey;
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
    
    public function getPlaceholder(){
        $attributeCode = $this->helper->getErpItemAttributeCode();
        $placeholders = [
            $this->product->getAttribute('sku')->getStoreLabel(),
        ];

        if(!empty($attributeCode)){
            $placeholders[] = $attributeCode;
        }

        return implode(' / ', $placeholders);
    }

    public function getOrderMethodText(){
        $orderMethod = $this->helper->getOrderMethod();

        if($orderMethod === 'cart'){
            return __('Add to Cart');
        } else {
            return __('Place Order');
        }
    }
    
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
