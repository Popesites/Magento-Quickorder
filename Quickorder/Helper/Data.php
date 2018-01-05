<?php

namespace Popesites\Quickorder\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

	const XML_PATH_ENABLED = 'popesites_configuration/options/is_enabled';
	
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Get is_enabled Core_Config_Data value
     *
     * @param void
     * @return bool 
     */
	public function getIsEnabled(){
		return (bool)$this->getValue(self::XML_PATH_ENABLED);
	}
	
	
	/**
     * Get Configuration parameter value
     *
     * @param string
     * @return mixed
     */
    public function getValue($path){
        return  $this->scopeConfig->getValue($path, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    } 

}
