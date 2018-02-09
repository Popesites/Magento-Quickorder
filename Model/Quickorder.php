<?php

namespace Popesites\Quickorder\Model;

/**
 * Class Quickorder
 *
 * Creates order or adds items to cart
 *
 * @category Popesites
 * @package  Popesites_Quickorder
 * @author Popesites <info@popesites.tech>
 */
class Quickorder {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product $product
     */
    protected $product;

    /**
     * @var \Magento\Framework\Data\Form\FormKey $formkey
     */
    protected $formkey;

    /**
     * @var \Magento\Quote\Model\QuoteFactory $quote
     */
    protected $quote;

    /**
     * @var \Magento\Quote\Model\QuoteFactory $quote
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;

    /**
     * @var \Magento\Sales\Model\Service\OrderService $orderService
     */
    protected $orderService;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory
     */
    protected $quoteItemFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\Rate $shippingRate
     */
    protected $shippingRate;

    /**
     * @var \Magento\Checkout\Model\Cart $cart
     */
    protected $cart;

    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote\Item\Processor $quoteItemProcessor
     */
    protected $quoteItemProcessor;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\DataObject\Factory $objectFactory
     */
    protected $objectFactory;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Data\Form\FormKey $formkey
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Quote\Model\Quote\Address\Rate $shippingRate
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\Quote\Item\Processor $quoteItemProcessor
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Quote\Model\Quote\Address\Rate $shippingRate,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\Quote\Item\Processor $quoteItemProcessor,
        \Magento\Framework\DataObject\Factory $objectFactory
    ) {
        $this->storeManager = $storeManager;
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        $this->shippingRate = $shippingRate;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->quoteItemProcessor = $quoteItemProcessor;
        $this->objectFactory = $objectFactory;
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

        // Load customer by email address
        $customer->loadByEmail($orderData['email']);

        if (!$customer->getEntityId()) {

            // If not avilable then create this customer
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($orderData['shipping_address']['firstname'])
                ->setLastname($orderData['shipping_address']['lastname'])
                ->setEmail($orderData['email'])
                ->setPassword($orderData['email']);
            $customer->save();
        }

        // Create quote object
        $quote = $this->quote->create();

        // Set store for quote
        $quote->setStore($store);

        // Load customer data
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote->setCurrency();

        // Assign Customer to quote
        $quote->assignCustomer($customer);

        $quote->save();

        // Add items in quote
        foreach ($orderData['items'] as $item) {
            $_product = $this->productRepository->getById($item['product_id']);

            $params = $this->objectFactory->create(
                array('qty' => $item['qty'])
            );

            $quoteItem = $quote->getItemByProduct($_product);
            if (!$quoteItem) {
                $quoteItem = $this->quoteItemFactory->create();
                $quoteItem->setQuote($quote);
                $quoteItem->setStoreId($this->storeManager->getStore()->getId());

                $quoteItem->setOptions($_product->getCustomOptions())->setProduct($_product);
                $quoteItem->setQty($item['qty']);
                $quoteItem->setPrice($_product->getPrice());

                // Add only item that is not in quote already
                $quote->addItem($quoteItem);
            } else {
                // Add product to existing item or create new
                $quote->addProduct($_product, $item['qty']);
            }
        }

        try {
            $quote->save();
        } catch (Exception $ex) {

        }

        // Set Address to quote
        $quote->getBillingAddress()->addData($orderData['billing_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);

        // Collect Rates and Set Shipping & Payment Method
        $shippingAddress = $quote->getShippingAddress();

        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($orderData['shipping_method_code']);

        // Set shipping method
        $_shippingRate = $shippingAddress->getShippingRateByCode($orderData['shipping_method_code']);
        if ($_shippingRate) {
            $this->shippingRate = $_shippingRate;
        } else {
            $this->shippingRate
                ->setCode('flatrate_flatrate')
                ->setPrice(1);
        }

        // Add payment method,
        $quote->setPaymentMethod($orderData['payment_method_code']);
        $quote->setInventoryProcessed();
        $quote->save();

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => $orderData['payment_method_code']]);

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        $order = $this->quoteManagement->submit($quote);

        // Do not sent email to customer
        $order->setEmailSent(0);
        $increment_id = $order->getRealOrderId();
        if ($order->getEntityId()) {
            $result['order_id'] = $order->getRealOrderId();
        } else {
            $result = ['error' => 1, 'msg' => 'Could not to dispatch an order.'];
        }

        // Return RealOrderId OR ErrorMessage
        return $result;
    }


    /**
     * Adds products to Cart
     *
     * @param array $orderData
     * @return string
     */
    public function addToCart($orderData) {
        $store = $this->storeManager->getStore();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        // Load customer by email address
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']);

        // Loading customer data
        $customer = $this->customerRepository->getById($customer->getEntityId());

        // Add items to cart/quote
        $quote = $this->cart->getQuote();

        foreach ($orderData['items'] as $item) {
            $_product = $this->productRepository->getById($item['product_id']);

            $params = $this->objectFactory->create( array('qty' => $item['qty']) );

            $quoteItem = $quote->getItemByProduct($_product);
            if (!$quoteItem) {
                $quoteItem = $this->quoteItemFactory->create();
                $quoteItem->setQuote($quote);
                $quoteItem->setStoreId($this->storeManager->getStore()->getId());

                $quoteItem->setOptions($_product->getCustomOptions())->setProduct($_product);
                $quoteItem->setQty($item['qty']);
                $quoteItem->setPrice($_product->getPrice());

                // Add only item that is not in quote already
                $quote->addItem($quoteItem);
            } else {
                $quote->addProduct($_product, $item['qty']);
            }
        }

        try {
            // Save changes to quote/cart
            $quote->save();
            $this->cart->save();
            $this->checkoutSession->setCartWasUpdated(true);
        } catch (Exception $ex) {
            $resultmsg = ['error' => 1,  'msg' => 'Error! Could not update cart! Please try again.'];
        }

        $resultmsg = ['msg' => 'All products were successfully added.'];
        return $resultmsg;
    }

}
