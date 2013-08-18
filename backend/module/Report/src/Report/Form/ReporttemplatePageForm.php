<?php
namespace Report\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ReporttemplatePageForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct ();

               
        $numPerPageSelect = new Element\Select('numPerPage');
        $numPerPageSelect->setValueOptions(
				array(
							'20'		=>'20',
							'50'		=>'50',
							'100'	=>'100',
							'200'	=>'200',
						)
				);
        $numPerPageSelect->setAttribute('class','combox');
        $numPerPageSelect->setAttribute('onchange','navTabPageBreak({numPerPage:this.value})');
        $this->add($numPerPageSelect);

        
        
        
        
    }
}
