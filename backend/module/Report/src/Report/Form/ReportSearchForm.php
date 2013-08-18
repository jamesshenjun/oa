<?php
namespace Report\Form;

use Zend\Form\View\Helper\FormText;
use Zend\Form\Form;
use Zend\Form\Element;

class ReportSearchForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct ('ReportSearchform');

        $this->setAttribute('method', 'post');
        $this->setAttribute('onsubmit',  'return navTabSearch(this);');
        
        $nameStr = new Element\Text('nameStr');
        $nameStr->setAttribute('class', 'textInput');
        $this->add($nameStr);
        
        $keywords = new Element\Text('keywords');
        $keywords->setAttribute('class', 'textInput');
        $this->add($keywords);
        
        $department = new Element\Select('departmentId');
        $this->add($department);
        
        $submit = new Element\Button('submit');
        $submit->setAttribute('type', 'submit');
        $submit->setLabel('点击查询');
        $this->add($submit);

        
    }
}
