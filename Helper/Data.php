<?php

namespace Popesites\Quickorder\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Data
 *
 * Popesites Helper
 *
 * @category Popesites
 * @package  Popesites_Quickorder
 * @author Popesites <info@popesites.tech>
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     * Xml configuration path of is_enabled dropdown
     * @const XML_PATH_ENABLED
     */
    const XML_PATH_ENABLED = 'popesites_configuration/options/is_enabled';

    /**
     * Xml configuration path of use_sku dropdown
     * @const XML_PATH_USE_SKU
     */
    const XML_PATH_USE_SKU = 'popesites_configuration/options/use_sku';

    /**
     * Xml configuration path of erp_item_number_attribute_code input
     * @const XML_PATH_ERP_ITEM_ATTRIBUTE_CODE
     */
    const XML_PATH_ERP_ITEM_ATTRIBUTE_CODE = 'popesites_configuration/options/erp_item_number_attribute_code';

    /**
     * Xml configuration path of shipment_method_code dropdown
     * @const XML_PATH_SHIPMENT_METHOD_CODE
     */
    const XML_PATH_SHIPMENT_METHOD_CODE = 'popesites_configuration/options/shipment_method_code';

    /**
     * Xml configuration path of payment_method_code dropdown
     * @const XML_PATH_PAYMENT_METHOD_CODE
     */
    const XML_PATH_PAYMENT_METHOD_CODE = 'popesites_configuration/options/payment_method_code';

    /**
     * Xml configuration path of cart_or_order dropdown
     * @const XML_PATH_ORDER_METOD
     */
    const XML_PATH_ORDER_METOD = 'popesites_configuration/options/cart_or_order';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface $messageManager
     */
    protected $messageManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        $this->messageManager = $messageManager;
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
     * Get Core_Config_Data value
     *
     * @param void
     * @return string
     */
    public function getOrderMethod() {
        return trim($this->getValue(self::XML_PATH_ORDER_METOD));
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
            $this->messageManager->addError(__($messageText));
        }
    }

    /**
     * Throw warning message on frontend
     *
     * @param string
     * @return void
     */
    public function throwWarningMessage($messageText) {
        if ($messageText && $messageText != '') {
            $this->messageManager->addWarning(__($messageText));
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
            $this->messageManager->addWarning(__($messageText));
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

        $productCollection = $this->collectionFactory->create();

        if ($this->getUseSku()) {
            $productCollection->addAttributeToFilter('sku', $sku);
        } elseif ($this->getErpItemAttributeCode() != '') {
            $productCollection->addAttributeToFilter($this->getErpItemAttributeCode(), $sku);
        }

        $productCollection->load();
        $product = $productCollection->getFirstItem();
        return $product->getId();
    }

}
