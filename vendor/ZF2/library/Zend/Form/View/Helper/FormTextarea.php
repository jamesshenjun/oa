<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\Exception;

class FormTextarea extends AbstractHelper
{
    /**
     * Attributes valid for the input tag
     *
     * @var array
     */
    protected $validTagAttributes = array(
        'autofocus'   => true,
        'cols'        => true,
        'dirname'     => true,
        'disabled'    => true,
        'form'        => true,
        'maxlength'   => true,
        'name'        => true,
        'placeholder' => true,
        'readonly'    => true,
        'required'    => true,
        'rows'        => true,
        'wrap'        => true,
        //以下修改是为了使用dwz的js框架所做出的妥协，为了添加dwz中对于HTML标签额外添加的不属于HTML标准的属性
    	'upLinkUrl'  => true,
    	'upLinkExt'  => true,
    	'upImgUrl'   => true,
    	'upImgExt'   => true,
    	'upFlashUrl' => true,
    	'upFlashExt' => true,
    	'upMediaUrl' => true,
    	'upMediaExt' => true,
    	'tools'	     => true,
    );

    /**
     * Render a form <textarea> element from the provided $element
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $name   = $element->getName();
        if (empty($name) && $name !== 0) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $attributes         = $element->getAttributes();
        $attributes['name'] = $name;
        $content            = (string) $element->getValue();
        $escapeHtml         = $this->getEscapeHtmlHelper();

        return sprintf(
            '<textarea %s>%s</textarea>',
            $this->createAttributesString($attributes),
            $escapeHtml($content)
        );
    }

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string|FormTextarea
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }
}
