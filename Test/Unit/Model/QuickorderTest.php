<?php

namespace Popesites\Quickorder\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;


/**
 * Class QuickorderTest
 * 
 * Quickorder model unit test
 * 
 * @category Popesites
 * @package  Popesites_Quickorder
 * @author Popesites <info@popesites.tech>
 */
class QuickorderTest extends \PHPUnit_Framework_TestCase {
    
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;
    
    /**
     * @var $quickOrderData;
     */
    protected $quickOrderData;
    
    /**
     * @var $resultMessage;
     */
    protected $resultMessage;

    /**
     * @var $expectedMessage;
     */
    protected $expectedMessage;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry;
     */
    protected $customer;
    
    /**
     * @var Popesites\Quickorder\Helper\Data
     */
    protected $popesitesHelper;
    
    /**
     * @var orderData
     */
    protected $defaultBillingAddress;    
    

    /**
     * Setup test
     */
    protected function setUp() {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()->getMock();
        
        $this->customer = $objectManager->getObject(
            \Magento\Customer\Model\Customer::class,
            ['customer' => $this->customer]
        );
        
        $this->popesitesHelper = $objectManager->getObject( \Popesites\Quickorder\Helper\Data::class );
        
        
        //prepare order /cart data
        $this->customer->load(1);
        $this->orderData['shippingAddress'] = $this->customer->getShippingAddress();
        $this->orderData['billingAddress'] = $this->customer->getBillingAddress();
        $this->orderData['email'] = $this->customer->getEmail();
        $this->orderData['currency'] = 'USD';
        $this->orderData['shipping_method_code'] = $this->popesitesHelper->getShipmentMethodCode();
        $this->orderData['payment_method_code'] = $this->popesitesHelper->getPaymentMethodCode();
        $this->orderData['items'] = array (
            array('product_id' => 1, 'qty' => 10),
            array('product_id' => 2, 'qty' => 20),
            array('product_id' => 3, 'qty' => 30),
        );
        
        
    }
    
    /**
     * Test createOrder exception
     */
    public function testCreateOrderException() {
        
        $model = $this->getMockBuilder(Popesites\Quickorder\Model\Quickorder::class)
             ->disableOriginalConstructor()->getMock();
        
        $model->expects($this->once())
              ->method('createOrder')
              ->with($this->quickOrderData)
              ->willReturnArgument($returnMessage);
        try {
            $model->createOrder();
            $this->fail('Expected exception'); 
        } catch (ExpectationFailedException $e) {
            $this->assertSame( 
                'Expectation failed for method name is equal to "createOrder" when invoked 1 time(s)' . PHP_EOL .
                'Parameter count for invocation Quickorder::createOrder() is too low.' . PHP_EOL .
                'To allow 0 or more parameters with any value, omit ->with() or use ->withAnyParameters() instead.', $e->getMessage()
            );
        }
        
        $this->resetMockObjects();
    }
   
    /**
     * Test createOrder success
     */
    public function testCreateOrder() {
        
        $model = $this->getMockBuilder(Popesites\Quickorder\Model\Quickorder::class)
             ->disableOriginalConstructor()->getMock();
        
        $model->expects($this->any())
              ->method('createOrder')
              ->with($this->quickOrderData)
              ->willReturnArgument('success');
        
        $this->assertEquals('success', $model->createOrder($this->quickOrderData));
    }
    
    
    /**
     * Test addToCart method exception
     */
    public function testAddToCartException() {
        
        $model = $this->getMockBuilder(Popesites\Quickorder\Model\Quickorder::class)
             ->disableOriginalConstructor()->getMock();
        
        $model->expects($this->once())
              ->method('addToCart')
              ->with($this->quickOrderData)
              ->willReturnArgument($returnMessage);
        try {
            $model->addToCart();
            $this->fail('Expected exception'); 
        } catch (ExpectationFailedException $e) {
            $this->assertSame( 
                'Expectation failed for method name is equal to "addToCart" when invoked 1 time(s)' . PHP_EOL .
                'Parameter count for invocation Quickorder::createOrder() is too low.' . PHP_EOL .
                'To allow 0 or more parameters with any value, omit ->with() or use ->withAnyParameters() instead.', $e->getMessage()
            );
        }
        
        $this->resetMockObjects();
    }
    
    /**
     * Test addToCart method
     */
    public function testAddToCart() {
        
        $model = $this->getMockBuilder(Popesites\Quickorder\Model\Quickorder::class)
             ->disableOriginalConstructor()->getMock();
        
        $model->expects($this->any())
              ->method('addToCart')
              ->with($this->quickOrderData)
              ->willReturnArgument('success');
        
        $this->assertArrayHasKey('success', $model->addToCart($this->quickOrderData));
    }

    
    
}
