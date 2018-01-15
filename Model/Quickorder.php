<?php
/**
 * Popesites Qickorder Model
 *
 * is used for order creation
 */
namespace Popesites\Quickorder\Model;
class Quickorder {
    protected $storeManager;
    protected $product;
    protected $formkey;
    protected $quote;
    protected $quoteManagement;
    protected $customerFactory;
    protected $customerRepository;
    protected $orderService;
    protected $quoteItemFactory;
    protected $shippingRate;
    protected $cart;
    protected $checkoutSession;
//    protected $cartRepositoryInterface;
//    protected $cartManagementInterface;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Quote\Model\Quote\Address\Rate $shippingRate,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession

    ) {
        $this->storeManager = $storeManager;
        $this->product = $product;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
//        $this->cartRepositoryInterface = $cartRepositoryInterface;
//        $this->cartManagementInterface = $cartManagementInterface;
        $this->shippingRate = $shippingRate;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
    }
    /**
     * Creates Order
     *
     * @param array $orderData
     * @return int $orderId
     *
     */
    public function createOrder($orderData) {

        $store = $this->storeManager->getStore();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']); // load customet by email address
        if (!$customer->getEntityId()) {
            //If not avilable then create this customer
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($orderData['shipping_address']['firstname'])
                ->setLastname($orderData['shipping_address']['lastname'])
                ->setEmail($orderData['email'])
                ->setPassword($orderData['email']);
            $customer->save();
        }

        $quote = $this->quote->create(); //Create object of quote
        $quote->setStore($store); //set store for which you create quote

        // loading customer data
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote->setCurrency();
        $quote->assignCustomer($customer); //Assign quote to customer
        $quote->save();
        //add items in quote
        foreach ($orderData['items'] as $item) {
            $_product = $this->product->load($item['product_id']);
            $quote->addProduct($_product, intval($item['qty']));
        }
        $quote->save();
        //Set Address to quote
        $quote->getBillingAddress()->addData($orderData['billing_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);
        // Collect Rates and Set Shipping & Payment Method
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($orderData['shipping_method_code']); //shipping method
        $_shippingRate = $shippingAddress->getShippingRateByCode($orderData['shipping_method_code']);
        if ($_shippingRate) {
            $this->shippingRate = $_shippingRate;
        } else {
            $this->shippingRate
                ->setCode('flatrate_flatrate')
                ->setPrice(1);
        }
        $quote->setPaymentMethod($orderData['payment_method_code']); //payment method
        $quote->setInventoryProcessed(); //not effetc inventory
        $quote->save(); //Now Save quote and your quote is ready
        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => $orderData['payment_method_code']]);
        // Collect Totals & Save Quote
        $quote->collectTotals()->save();
        // Create Order From Quote
        $order = $this->quoteManagement->submit($quote);
        $order->setEmailSent(0);
        $increment_id = $order->getRealOrderId();
        if ($order->getEntityId()) {
            $result['order_id'] = $order->getRealOrderId();
        } else {
            $result = ['error' => 1, 'msg' => 'Could not to dispatch an order.'];
        }
        return $result;
    }

    public function addToCart($orderData) {
        $store = $this->storeManager->getStore();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']); // load customet by email address
        // loading customer data
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote = $this->quoteManagement->getCartForCustomer($customer->getId());
        //add items in cart
        foreach ($orderData['items'] as $item) {
            $_product = $this->product->load($item['product_id']);
            $quote->addProduct($_product, intval($item['qty']));
        }
        $quote->save();

        $resultmsg = ['msg' => 'All products were successfully added.'];
        return $resultmsg;
    }
}