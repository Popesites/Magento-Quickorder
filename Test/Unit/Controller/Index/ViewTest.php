<?php

/**
 * Class ViewTest
 * 
 * Quickorder/index/view controller test 
 * 
 * @category Api
 * @package  Popesites\Quickorder\Test\Unit\Model
 * @author Popesites <info@popesites.tech>
 */
class ViewTest {
   
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject 
     */
    private $httpContext;

    public function testExecute()
    {
        // Create dependency mocks
        $page = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->getMock();
        // Set up SUT
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject('Popesites\Quickorder\Controller\Index\View',
            ['resultPageFactory' => $resultFactory]
        );
        // Expectations of test
        $resultFactory->expects($this->once())->method('create')->willReturn($page);
        $this->assertSame($page, $model->execute());
    }
    
    
    
}
