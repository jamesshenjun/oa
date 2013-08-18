<?php
namespace Report\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ReporttemplateAddForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct ('ReporttemplateAddform');

        $this->setAttribute('method', 'post');
        

        $this->setAttribute('enctype',  'multipart/form-data');
        $this->setAttribute('class',  'pageForm required-validate');
        $this->setAttribute('onsubmit',  'return iframeCallback(this);');
        
        
        //模板名
        $name =  new Element\Text('name');
        $name->setAttribute('class', 'required');
        $this->add($name);
        
        //模板文件
        $template_excel = new Element\File('template_excel');
        $template_excel->setAttribute('class', 'required');
        $template_excel->setValue('请选择Excel文件');
        $this->add($template_excel);
        

        
        //提交按钮
        $submit = new Element\Submit('submit');
        $submit->setValue('提交');
        $this->add($submit);
 
    }
}
