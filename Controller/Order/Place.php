<?php
namespace Popesites\Quickorder\Controller\Order;
class Place extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $quickOrder;
    protected $customerSession;
    protected $helper;


    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Popesites\Quickorder\Model\Quickorder $quickOrder,
        \Popesites\Quickorder\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->quickOrder = $quickOrder;
        $this->helper = $helper;

        parent::__construct($context);
    }
    /**
     * Execute place action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $orderData = $this->createOrderData();
        $this->quickOrder->createOrder($orderData);
    }

    /**
     * Execute place action
     *
     * @param void
     * @return array
     */
    private function createOrderData(){
        $orderData = array();
        $customer = $this->customerSession->getCustomer();

        if ($customer) {
            // set Shipping Address to order data
            $shippingAddress = $customer->getDefaultShippingAddress();
            $billingAddress = $customer->getDefaultBillingAddress();
            if (!$shippingAddress && !$billingAddress) {
                $this->helper->throwErrorMessage('Customer has no addresses. They are required to create order. Please update addresses.');
                return ;
            }
            if ($shippingAddress) {
                $orderData['shipping_address'] = $shippingAddress->toArray();
            } else {
                $orderData['shipping_address'] = $billingAddress->toArray();
            }

            //set email to order data
            $orderData['email'] = $customer->getEmail();

            //set currency code to order data
            $orderData['currency'] = $this->helper->getCurrency();

            //set shipment method
            if (!$this->helper->getShipmentMethodCode()) {
                $this->helper->throwErrorMessage('Shipment method is required to create order. Please set shipment method in module configuration.');
                return ;
            } else {
                $orderData['shipping_method_code'] = $this->helper->getShipmentMethodCode();
            }

            //set payment method
            if (!$this->helper->getPaymentMethodCode()) {
                $this->helper->throwErrorMessage('Payment method is required to create order. Please set payment method in module configuration.');
                return ;
            } else {
                $orderData['payment_method_code'] = $this->helper->getPaymentMethodCode();
            }

            $items = $this->getItems();

            if (!$items || count($items) == 0 ) {
                $this->helper->throwErrorMessage('Sorry, no products found. Please try again.');
                return;
            }

            $orderData['items'] = $items;

            return $orderData;
        }
    }

    /**
     * Create product items array from request
     *
     * @param void
     * @return array
     */
    protected function getItems() {

        $requestItems = $this->getRequest()->getParam('item');

        //create items array from request form
        $items = array();
        $failed_items = array();
        if (count($requestItems) > 0) {
            foreach ($requestItems as $item) {
                $product_id = $this->helper->validateProduct($item['sku']);
                if ($product_id && $item['qty']) {
                    $items[]= array('product_id' => $product_id, 'qty' => $item['qty']);
                } else {
                    if (!$product_id) { $failed_items[] = $item['sku']; }
                }
            }
        }

        // Add error message if some products are not found
        if (count($failed_items) > 0) {
            if ($this->helper->getUseSku()) {
                $errorMsg = 'There no products with SKU\'s: '. implode(',', $failed_items);
            } else {
                $errorMsg = 'There no products with ERP Item Number: '. implode(',', $failed_items);
            }
            $this->helper->throwErrorMessage($errorMsg);
        }

        return $items;
    }
}