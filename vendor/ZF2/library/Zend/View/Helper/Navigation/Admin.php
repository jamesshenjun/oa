<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper\Navigation;

use RecursiveIteratorIterator;
use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Page\AbstractPage;
use Zend\View;
use Zend\View\Exception;

/**
 * Helper for rendering menus from navigation containers
 */
class Admin extends AbstractHelper
{
    
	/**
     * Whether labels should be escaped
     *
     * @var bool
     */
    protected $escapeLabels = true;

    

   
	/**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param  AbstractContainer $container [optional] container to operate on
     * @return Menu      fluent interface, returns self
     */
    
    public function __invoke($container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }



    /**
     * Returns an HTML string containing an 'a' element for the given page if
     * the page's href is not empty, and a 'span' element if it is empty
     *
     * Overrides {@link AbstractHelper::htmlify()}.
     *
     * @param  AbstractPage $page   page to generate HTML for
     * @param bool $escapeLabel     Whether or not to escape the label
     * @return string               HTML string for the given page
     */
    public function htmlify(AbstractPage $page, $escapeLabel = true)
    {
        //get label and title for translating
        $label = $page->getLabel();
        $title = $page->getTitle();

        //translate label and title?
        if (null !== ($translator = $this->getTranslator())) {
            $textDomain = $this->getTranslatorTextDomain();
            if (is_string($label) && !empty($label)) {
                $label = $translator->translate($label, $textDomain);
            }
            if (is_string($title) && !empty($title)) {
                $title = $translator->translate($title, $textDomain);
            }
        }

        // get attribs for element
        $attribs = array(
            'id'     => $page->getId(),
            'title'  => $title,
            'class'  => $page->getClass()
        );

        // does page have a href?
        $href = $page->getHref();
        
        $customProperties = $page->getCustomProperties();
        //得到程序员自己定义的属性
        
        $level = $customProperties['level'];
        //得到级别的取值
        
        if ($href) {
            $element = 'a';
            $attribs['href'] = $href;
            $attribs['target'] = $page->getTarget();
        } 
        else {
        	$element = 'span';
        	
        }

        if($level==1){
        	$html = '<' . $element . $this->htmlAttribs($attribs) . '><span>';
        }
        else{
        	$html = '<' . $element . $this->htmlAttribs($attribs) . '>';
        }
        
        if ($escapeLabel === true) {
            $escaper = $this->view->plugin('escapeHtml');
            $html .= $escaper($label);
        } else {
            $html .= $label;
        }
       
        
        if($level==1){
        	$html .= '</span></' . $element . '>';
        }
        else{
        	$html .= '</' . $element . '>';
        }
        
        return $html;
    }

   
	/**
     * Renders a nav menu
     */
    public function renderNav() {
    
    	$container = $this->getContainer();
    	
    	$html = '';

        $pages = $container->findAllBy('level','1');
        //得到所有的导航栏菜单
        
        $html = "<ul>".self::EOL;
        
        foreach($pages as $key=>$page){
        	
        	if(!$this->accept($page)){
        		continue;
        	}
        	if($key==0){
        		$html.='	<li class="selected">';
        	}
        	else{
        		$html.='	<li>';
        	}
        	
        	$html.= $this->htmlify($page);
        	
        	$html.='</li>'.self::EOL;
        
        } 
        
        $html .= "</ul>";
        
        return $html;
        
    }//function renderNav() end
    
    
    
    /**
     * Renders a sidebar menu
     */
    public function renderSidebar($parent_name=null){
    	
    	$container = $this->getContainer();
    	
    	if($parent_name===null){
    	//如果没有设置的话，就默认显示第一个有权限的菜单
    	
    		$nav  = $container->findAllBy('level','1');
    		//首先找出第一个层级上的导航栏上的菜单选项，就是找横向导航栏上面的内容
    		
    		//然后循环找出第一个有权限的菜单选项，然后赋值给下面的代码
    		//然后再在这个部分中查询有权限的侧边栏菜单选项
    		foreach($nav as $pages){
    			if($this->accept($pages)){
    				break;
    			}
    		}
    		
    	}
    	else{
    		
    		$pages  = $container->findOneBy('resource',$parent_name);
    		
    	}
    	
    	$html = '<div  class="accordion"  fillSpace="sidebar">';
    	
    	
    	foreach($pages as $key=>$page){
    		 
    		if(!$this->accept($page)||!$page->hasPages()){
    			continue;
    		}
    		
    		$html.='<div class="accordionHeader">'.self::EOL;
			$html.='	<h2><span>Folder</span>';
    		$html.=$page->getLabel();
    		$html.='	</h2>';
    		$html.='</div>';
    		
    		$html.='<div class="accordionContent">'.self::EOL;
    		$html.='	<ul class="tree treeFolder">';
    		
    		foreach($page->getPages() as $subPage){
    			
    			if(!$this->accept($subPage)){
    				continue;
    			}
    			
    			$html.='<li>'.self::EOL;
                $html.='		'.$this->htmlify($subPage);
                $html.='</li>';
            }
    		
    		$html.='	</ul>'.self::EOL;
    		$html.='</div>';
    		
    	}
    	
    	$html.="</div>";
    	
    	return $html;
    	
    }//function renderSidebar() end

   /**
     * Renders menu
     *
     * Implements {@link HelperInterface::render()}.
     *
     * If a partial view is registered in the helper, the menu will be rendered
     * using the given partial script. If no partial is registered, the menu
     * will be rendered as an 'ul' element by the helper's internal method.
     *
     * @see renderPartial()
     * @see renderMenu()
     *
     * @param  AbstractContainer $container [optional] container to render. Default is
     *                              to render the container registered in the
     *                              helper.
     * @return string               helper output
     */
    public function render($container = null)
    {
    	return $this->renderNav();
    }
    
    
}
