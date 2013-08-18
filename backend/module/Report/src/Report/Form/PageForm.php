<?php
namespace Report\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class PageForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct ('Pageform');

        $this->setAttribute('method', 'post');
        
        $this->setAttribute('id',  'pagerForm');
        
        
        $pageNumhidden =  new Element\Hidden('pageNum');
        $pageNumhidden->setValue('1');
        
        
        $this->add($pageNumhidden);
        
        $numPerPagehidden =  new Element\Hidden('numPerPage');
        $this->add($numPerPagehidden);
        
        
        $keywordhidden = new Element\Hidden('keywords');
        $this->add($keywordhidden);
        
        $nameStrhidden = new Element\Hidden('nameStr');
        $this->add($nameStrhidden);
        
        $departmentIdhidden = new Element\Hidden('departmentId');
        $this->add($departmentIdhidden);
        
    }
}
