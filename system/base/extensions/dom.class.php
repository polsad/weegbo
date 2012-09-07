<?php
/**
 * Weegbo DomExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.extensions
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
/**
 * DomExtension class
 *
 * Class for make XML.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.extensions
 * @since 0.8
 */
class DomExtension {
    /**
     * @var object dom document
     */
    private $_dom = null;

    /**
     * Constructor
     *
     * @access public
     * @param string $version XML version, 1.0 by default
     * @param string $encoding XML encoding, UTF-8 by default
     * @return void
     */
    public function __construct($version = '1.0', $encoding = 'UTF-8') {
        $this->_dom = new DOMDocument($version, $encoding);
    }

    /**
     * Set new node to XML
     *
     * @access public
     * @param string $node node name
     * @param DOM node $root parent node
     * @param string $value node value
     * @param array $attr array with node attributes
     * @return DOM node
     */
    public function add($node, $root = null, $value = null, $attr = null) {
        $item = (null === $value) ? $this->_dom->createElement($node) : $this->_dom->createElement($node, (string) $value);
        if (null !== $attr) {
            foreach ((array) $attr as $k => $v) {
                $item->setAttribute($k, $v);
            }
        }
        if (null !== $root) {
            $root->appendChild($item);
        }
        else {
            $this->_dom->appendChild($item);
        }
        return $item;
    }

    /**
     * Set new CDATA node to XML
     *
     * @access public
     * @param string $node node name
     * @param DOM node $root parent node
     * @param string $value node value
     * @param array $attr array with node attributes
     * @return DOM node
     */
    public function addCdata($node, $root, $value, $attr = null) {
        $cdata = $this->_dom->createCDATASection($value);
        $item = $this->_dom->createElement($node);
        if (null != $attr) {
            foreach ((array) $attr as $k => $v) {
                $item->setAttribute($k, $v);
            }
        }
        $item->appendChild($cdata);
        $root->appendChild($item);
        return $item;
    }

    /**
     * Return XML
     *
     * @access public
     * @param bool $format, true - XML formatting
     * @return XML
     */
    public function getXML($format = false) {
        if ($format == true) {
            $this->_dom->preserveWhiteSpace = false;
            $this->_dom->formatOutput = true;
        }
        return $this->_dom->saveXML();
    }
}