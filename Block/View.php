<?php
namespace Popesites\Quickorder\Block;
use Magento\Customer\Model\Context;

/**
 * Sales order view block
 *
 * @api
 * @since 100.0.2
 **/
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
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
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Payment\Helper\Data $paymentHelper
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
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->getUrl('customer/account');
        }
        return $this->getUrl('*/*/form');
    }
    /**
     * @param object $order
     * @return string
     */
    public function getPlaceQuickorderUrl($order)
    {

        return $this->getUrl('', ['order_id' => $order->getId()]);
    }
    /**
     * @param string $
     * @return object
     */
    public function getProductUrl($productSku)
    {
        return $this->getUrl('catalog/product/', ['productSku' => $productSku]);
    }
}
