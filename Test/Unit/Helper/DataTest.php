<?php

namespace Popesites\Quickorder\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class DataTest
 * 
 * Helper test
 * 
 * @category Popesites
 * @package  Popesites_Quickorder
 * @author Popesites <info@popesites.tech>
 */
class DataTest {
    
    /**
     * @var Popesites\Quickorder\Helper\Data
     */
    protected $_helper;    
    
    protected function setUp() {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_helper = $objectManager->getObject(Popesites\Quickorder\Helper\Data::class);
        
    }
    
    /**
     * test getIsEnabled method
     */
    public function testGetIsEnabled() {
        $this->assertTrue(TRUE, $this->_helper->getIsEnabled());
    }

    /**
     * test getUseSku method
     */
    public function testGetUseSku() {
        $this->assertTrue(TRUE, $this->_helper->getUseSku());
    }
    
    /**
     * test getShipmentMethodCode method when is set to Freeshipping (default)
     */
    public function testGetShipmentMethodCodeFreeshipping() {
        $this->assertSame('freeshipping_freeshipping', $this->_helper->getShipmentMethodCode());
    }
    
    /**
     * test getPaymentMethodCode method when payment is set to Checkmoney/Order (default)
     */
    public function testGetPaymentMethodCodeCheckmo() {
        $this->assertSame('checkmo', $this->_helper->getPaymentMethodCode());
    }
    
    /**
     * test getOrderMethod method when 'Cart' is set
     */
    public function testGetOrderMethodisOrder() {
        $this->assertSame('order', $this->_helper->getOrderMethod());
    }
    
    /**
     * test getOrderMethod method when 'Order' is set
     */
    public function testGetOrderMethodisCart() {
        $this->assertSame('cart', $this->_helper->getOrderMethod());
    }

    /**
     * test getOrderMethod method (Product with ID:1)
     */
    public function testValidateProduct() {
        $productSku = '24-MB01';
        $this->assertSame('1', $this->_helper->validateProduct());
    }

    
    
    
}
