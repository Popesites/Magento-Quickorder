<?php
namespace Popesites\Quickorder\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    const XML_PATH_ENABLED = 'popesites_configuration/options/is_enabled';
    const XML_PATH_USE_SKU = 'popesites_configuration/options/use_sku';
    const XML_PATH_ERP_ITEM_ATTRIBUTE_CODE = 'popesites_configuration/options/erp_item_number_attribute_code';
    const XML_PATH_SHIPMENT_METHOD_CODE = 'popesites_configuration/options/shipment_method_code';
    const XML_PATH_PAYMENT_METHOD_CODE = 'popesites_configuration/options/payment_method_code';
    protected $storeManager;
    protected $collectionFactory;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        $this->_messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }
    /**
     * Get is_enabled Core_Config_Data value
     *
     * @param void
     * @return bool
     */
    public function getIsEnabled() {
        return (bool) $this->getValue(self::XML_PATH_ENABLED);
    }
    /**
     * Get use_sku Core_Config_Data value
     *
     * @param void
     * @return bool
     */
    public function getUseSku() {
        return (bool) $this->getValue(self::XML_PATH_USE_SKU);
    }
    /**
     * Get Shipment method code Core_Config_Data value
     *
     * @param void
     * @return string
     */
    public function getShipmentMethodCode() {
        return (string) $this->getValue(self::XML_PATH_SHIPMENT_METHOD_CODE);
    }
    /**
     * Get Payment method code Core_Config_Data value
     *
     * @param void
     * @return string
     */
    public function getPaymentMethodCode() {
        return (string) $this->getValue(self::XML_PATH_PAYMENT_METHOD_CODE);
    }
    /**
     * Get  Core_Config_Data value
     *
     * @param void
     * @return string
     */
    public function getErpItemAttributeCode() {
        return trim($this->getValue(self::XML_PATH_ERP_ITEM_ATTRIBUTE_CODE));
    }
    /**
     * Get Configuration parameter value
     *
     * @param string
     * @return mixed
     */
    public function getValue($path) {
        return $this->scopeConfig->getValue($path, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }
    /**
     * Get Currency Code
     *
     * @param void
     * @return string
     */
    public function getCurrency() {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }
    /**
     * Throw error message on frontend
     *
     * @param string
     * @return void
     */
    public function throwErrorMessage($messageText) {
        if ($messageText && $messageText != '') {
            $this->_messageManager->addError(__($messageText));
        }
    }
    /**
     * Throw warning message on frontend
     *
     * @param string
     * @return void
     */
    public function throwWaringMessage($messageText) {
        if ($messageText && $messageText != '') {
            $this->_messageManager->addWarning(__($messageText));
        }
    }
    /**
     * Throw success message on frontend
     *
     * @param string
     * @return void
     */
    public function throwSuccessMessage($messageText) {
        if ($messageText && $messageText != '') {
            $this->_messageManager->addWarning(__($messageText));
        }
    }

    /**
     * Validate product by SKU or erp_item_number_attribute_code
     * Depends on configuration
     *
     * @param type $sku
     * @return mixed $entity_id or false
     */
    public function validateProduct($sku) {

        var_dump($sku);

        $productCollection = $this->collectionFactory->create();

        if ($this->getUseSku()){
            $productCollection->addAttributeToFilter('sku', $sku);
        } elseif ($this->getErpItemAttributeCode() != '' ) {
            $productCollection->addAttributeToFilter($this->getErpItemAttributeCode(), $sku);
        }

        $productCollection->load();

        $result = false;
        $product = $productCollection->getFirstItem();

        return $product->getId();
    }
}