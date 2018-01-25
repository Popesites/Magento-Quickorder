<?php

namespace Popesites\Quickorder\Model;

/**
 * Class Quickorder
 *
 * A short description about the purpose of the class would be awesome. 1 sentence that describes the task of this class
 *
 * @category Api
 * @package  Popesites\Quickorder\Model
 * @author   An Author <theauthors@email.com>
 */
class Quickorder
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var string $formkey
     */
    protected $formkey;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quote;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $quoteItemFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\Rate
     */
    protected $shippingRate;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Quickorder constructor.
     *
     * @param \Magento\Framework\App\Helper\Context             $context
     * @param \Magento\Store\Model\StoreManagerInterface        $storeManager
     * @param \Magento\Catalog\Model\Product                    $product
     * @param \Magento\Framework\Data\Form\FormKey              $formkey
     * @param \Magento\Quote\Model\QuoteFactory                 $quote
     * @param \Magento\Quote\Model\QuoteManagement              $quoteManagement
     * @param \Magento\Quote\Model\Quote\ItemFactory            $quoteItemFactory
     * @param \Magento\Customer\Model\CustomerFactory           $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Sales\Model\Service\OrderService         $orderService
     * @param \Magento\Quote\Model\Quote\Address\Rate           $shippingRate
     * @param \Magento\Checkout\Model\Cart                      $cart
     * @param \Magento\Checkout\Model\Session                   $checkoutSession
     */
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

    )
    {
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
     *
     * @return array $orderId
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createOrder($orderData)
    {
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

        $customer = $this->customerRepository->getById($customer->getEntityId()); // loading customer data
        $quote->setCurrency();
        $quote->assignCustomer($customer); //Assign quote to customer
        $quote->save();

        foreach ($orderData['items'] as $item) { //add items in quote
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

        if ($order->getEntityId()) {
            $result['order_id'] = $order->getRealOrderId();
        } else {
            $result = ['error' => 1, 'msg' => 'Could not to dispatch an order.'];
        }
        return $result;
    }

    /**
     * @param $orderData
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addToCart($orderData)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']); // load customet by email address

        /**
         * loading customer data and add items in cart
         */
        foreach ($orderData['items'] as $item) {
            $_product = $this->product->load($item['product_id']);
            $this->cart->addProduct($_product, intval($item['qty']));
        }

        $this->cart->save();
        $this->checkoutSession->setCartWasUpdated(true);

        $resultmsg = ['msg' => 'All products were successfully added.'];
        return $resultmsg;
    }
}
