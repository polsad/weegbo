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
    private $dom = null;

    /**
     * Constructor
     *
     * @access public
     * @param string $version XML version, 1.0 by default
     * @param string $encoding XML encoding, UTF-8 by default
     * @return void
     */
    public function __construct($version = '1.0', $encoding = 'UTF-8') {
        $this->dom = new DOMDocument($version, $encoding);
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
    public function setElement($node, $root = null, $value = null, $attr = null) {
        if (null == $value)
            $nnode = $this->dom->createElement($node);
        else
            $nnode = $this->dom->createElement($node, (string) $value);
        if (null != $attr) {
            foreach ((array) $attr as $k => $v) {
                $nnode->setAttribute($k, $v);
            }
        }
        if ($root != null)
            $root->appendChild($nnode);
        else
            $this->dom->appendChild($nnode);
        return $nnode;
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
    public function setElementCdata($node, $root, $value, $attr = null) {
        $cdata = $this->dom->createCDATASection($value);
        $nnode = $this->dom->createElement($node);
        if (null != $attr) {
            foreach ((array) $attr as $k => $v) {
                $nnode->setAttribute($k, $v);
            }
        }
        $nnode->appendChild($cdata);
        $root->appendChild($nnode);
        return $nnode;
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
            $this->dom->preserveWhiteSpace = false;
            $this->dom->formatOutput = true;
        }
        return $this->dom->saveXML();
    }
}