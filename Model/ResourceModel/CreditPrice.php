<?php

namespace Magento\Braintree\Model\ResourceModel;

/**
 * Class CreditPrice
 * @package Magento\Braintree\Model\ResourceModel
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class CreditPrice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct() //@codingStandardsIgnoreLine
    {
        $this->_init('braintree_credit_prices', 'id');
    }
}
