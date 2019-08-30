<?php

namespace Popesites\Quickorder\Controller\Order;

/**
 * Class Place
 *
 * Order place action.
 *
 * @category Popesites
 * @package  Popesites_Quickorder
 * @author Popesites <info@popesites.tech>
 */
class Place extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Popesites\Quickorder\Model\Quickorder $quickOrder
     */
    protected $quickOrder;

    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;

    /**
     * @var \Popesites\Quickorder\Helper\Data $helper
     */
    protected $helper;


    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Popesites\Quickorder\Model\Quickorder $quickOrder
     * @param \Popesites\Quickorder\Helper\Data $helper
     *
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
    public function execute() {
        $orderData = $this->createOrderData();

        // Create order
        if ( ! $orderData || count($orderData) == 0) {
            $this->helper->throwErrorMessage('Sorry, no products found. Please try again.');
        } else {
            if ($this->helper->getOrderMethod() == 'order') {
                $resultmsg = $this->quickOrder->createOrder($orderData);
                if (isset($resultmsg['error']) && $resultmsg['error'] == 1) {
                    $this->helper->throwErrorMessage(__($resultmsg['msg']));
                } else {
                    $this->helper->throwSuccessMessage(__('Order # %1 was successfully created.', $resultmsg['msg']));
                }
            } elseif ($this->helper->getOrderMethod() == 'cart') {
                $resultmsg = $this->quickOrder->addToCart($orderData);
                $this->helper->throwSuccessMessage(__($resultmsg['msg']));
            } else {
                $this->helper->throwWarningMessage(__('Configuration is wrong. Please check a configuration.'));
            }
        }

        $this->_redirect('quickorder/index/view');
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

        if (!$customer) {
            $this->helper->throwErrorMessage('Customer is not logged in. Please log in.');
            return array();
        }

        if ($customer) {

            // set Shipping Address to order data

            if ($this->helper->getOrderMethod() != 'cart') {

                $shippingAddress = $customer->getDefaultShippingAddress();
                $billingAddress = $customer->getDefaultBillingAddress();
                if (!$shippingAddress && !$billingAddress) {
                    $this->helper->throwErrorMessage('Customer has no addresses. They are required to create order. Please update addresses.');
                    return array();
                }

                // set addresses
                if ($shippingAddress) {
                    $orderData['shipping_address'] = $shippingAddress->toArray();
                    if (!$billingAddress) {
                        $orderData['billing_address'] = $shippingAddress->toArray();
                    }
                }

                if ($billingAddress) {
                    $orderData['billing_address'] = $billingAddress->toArray();
                    if (!$shippingAddress) {
                        $orderData['shipping_address'] = $billingAddress->toArray();
                    }

                }
            }


            //set email to order data
            $orderData['email'] = $customer->getEmail();

            //set currency code to order data
            $orderData['currency'] = $this->helper->getCurrency();

            //set shipment method
            if (!$this->helper->getShipmentMethodCode()) {
                $this->helper->throwErrorMessage('Shipment method is required to create order. Please set shipment method in module configuration.');
                return array();
            } else {
                $orderData['shipping_method_code'] = $this->helper->getShipmentMethodCode();
            }

            //set payment method
            if (!$this->helper->getPaymentMethodCode()) {
                $this->helper->throwErrorMessage('Payment method is required to create order. Please set payment method in module configuration.');
                return array();
            } else {
                $orderData['payment_method_code'] = $this->helper->getPaymentMethodCode();
            }

            //get product id's array to create quote items
            $items = $this->getItems();

            if (!$items || count($items) == 0) {
                return array();
            }

            $orderData['items'] = $items;
            $orderData['form_key'] = $this->getRequest()->getParam('form_key');

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
                if ($item['sku'] == '')
                    continue;
                $product_id = $this->helper->validateProduct($item['sku']);
                // default quantity = 1 (we might as well take the min order qty)
                if (empty($item['qty'])) {
                    $item['qty'] = 1;
                }
                if ($product_id) {
                    $items[] = array('product_id' => $product_id, 'qty' => (int) $item['qty']);
                } else {
                    if (!$product_id) {
                        $failed_items[] = $item['sku'];
                    }
                }
            }
        }

        // Add error message if some products are not found
        if (count($failed_items) > 0) {
            if ($this->helper->getUseSku()) {
                $errorMsg = __('There no products with SKU(s): %1', implode(',', $failed_items));
            } else {
                $errorMsg = __('There no products with ERP Item Number: %1', implode(',', $failed_items));
            }
            $this->helper->throwErrorMessage($errorMsg);
        }

        return $items;
    }

}
