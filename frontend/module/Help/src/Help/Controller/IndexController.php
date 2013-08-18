<?php
namespace Help\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    
	public function indexAction()
    {
    	$this->layout()->setVariable('select','index');
    	
    	return new ViewModel();
    }
    
   
    
    
}//class IndexController end
