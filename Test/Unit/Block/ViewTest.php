<?php

namespace Popesites\Quickorder\Test\Unit\Block;

/**
 * Class ViewTest
 * 
 * View block unit test
 * 
 * @category Popesites
 * @package  Popesites_Quickorder
 * @author Popesites <info@popesites.tech>
 */
class ViewTest extends \PHPUnit_Framework_TestCase {
    
    /**
     * @var \Magento\Customer\Block\Account\Customer 
     */
    private $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject 
     */
    private $httpContext;

    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;
    
    /**
     * Setup test
     */
    protected function setUp() {
        $this->httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->block = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(Popesites\Quickorder\Block\View::class, ['httpContext' => $this->httpContext]);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function customerLoggedInDataProvider()
    {
        return [
            [1, true],
            [0, false],
        ];
    }
    /**
     * @param $isLoggedIn
     * @param $result
     * @dataProvider customerLoggedInDataProvider
     */
    public function testCustomerLoggedIn($isLoggedIn, $result)
    {
        $this->httpContext->expects($this->once())->method('getValue')
            ->with(\Magento\Customer\Model\Context::CONTEXT_AUTH)
            ->willReturn($isLoggedIn);
        $this->assertSame($result, $this->block->customerLoggedIn($isLoggedIn));
    }
    
    /**
     * test getBackUrl when customer is logged in 
     */
    public function testGetBackUrlCustomerLoggedIn(){
        $this->assertRegExp('/form/', $this->block->getBackUrl()); 
    }
    
    /**
     * test getBackUrl when customer logged in 
     */
    public function testGetBackUrlCustomerNotLoggedIn(){
        $this->assertRegExp('/login/', $this->block->getBackUrl()); 
    }
    
    /**
     * test getProductUrl if product is registered 
     */
    public function testGetProductUrl(){
        $productSku = '24-MB01';
        $this->assertRegExp('/joust-duffle-bag.html/', $this->block->getProductSku($productSku)); 
    }    
    
    
}
