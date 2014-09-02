<?php

class Aligent_RedisMasterSlave_Block_Adminhtml_System_Config_Form_Field_Redisslaves extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    public function __construct()
    {
        $this->addColumn('server', array(
            'label' => Mage::helper('adminhtml')->__('server'),
            'style' => 'width:120px',
        ));
        $this->addColumn('port', array(
            'label' => Mage::helper('adminhtml')->__('port'),
            'style' => 'width:220px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Slave Redis');
        parent::__construct();
    }
}