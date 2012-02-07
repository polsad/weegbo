<?php
/**
 * Weegbo RssExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.extensions
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
/**
 * RssExtension class
 *
 * Extension for generate RSS 2.0 feed
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.extensions
 * @since 0.8
 */
require_once(Config::get('path/extensions').'dom.class.php');
class RssExtension extends DomExtension {
    private $_channel = NULL;

    public function __construct($encoding = 'UTF-8') {
        parent::__construct('1.0', $encoding);
        $rss = $this->setElement('rss', NULL, NULL, array('version' => '2.0'));
        $this->_channel = $this->setElement('channel', $rss);
    }

    /**
     * Set RSS channel
     *
     * Required - title, link, description
     * Not required - language, copyright, managingEditor, webMaster,
     *                pubDate, lastBuildDate, category, generator,
     *                docs, cloud, ttl, rating,
     *                textInput, skipHours, skipDays
     *
     * @access public
     * @param array $attr array with channel attributes
     * @return void
     */
    public function setChannel($attr) {
        $allow = array(
            'title', 'link', 'description', 'language', 'copyright', 'managingEditor',
            'webMaster', 'pubDate', 'lastBuildDate', 'category', 'generator', 'docs',
            'cloud', 'ttl', 'image', 'rating', 'textInput', 'skipHours', 'skipDays'
        );

        if (!array_key_exists('title', $attr))
            $attr['title'] = '';
        if (!array_key_exists('link', $attr))
            $attr['link'] = '';
        if (!array_key_exists('description', $attr))
            $attr['description'] = '';
        if (!array_key_exists('generator', $attr))
            $attr['generator'] = 'Weegbo Rss Extension';

        foreach ($attr as $k => $v) {
            if (in_array($k, $allow)) {
                /**
                 * Cloud tag
                 */
                if ($k == 'cloud' && is_array($v)) {
                    $this->setElement($k, $this->_channel, NULL, $v);
                }
                /**
                 * Image tag
                 */
                elseif ($k == 'image' && is_array($v)) {
                    $node = $this->setElement('image', $this->_channel);
                    $iallow = array('url', 'title', 'link', 'width', 'height', 'description');
                    foreach ($v as $ki => $vi) {
                        if (in_array($ki, $iallow)) {
                            $this->setElement($ki, $node, $vi);
                        }
                    }
                }
                else {
                    $this->setElement($k, $this->_channel, $v);
                }
            }
        }
    }

    /**
     * Set RSS item
     *
     * Required - title or description
     * Not required - link, author, category, comments, enclosure, guid, pubDate, source
     *
     * @access public
     * @param array $attr array with item attributes
     * @return void
     */
    public function setItem($attr) {
        $allow = array(
            'title', 'link', 'description', 'author', 'pubDate',
            'category', 'comments', 'enclosure', 'source'
        );

        /**
         * Correct item
         */
        if (array_key_exists('title', $attr) || array_key_exists('description', $attr)) {
            $item = $this->setElement('item', $this->_channel);
            foreach ($attr as $k => $v) {
                if (in_array($k, $allow)) {
                    /**
                     * If category is array ('Category', array(arrtibutes))
                     */
                    if ($k == 'category') {
                        if (is_array($v)) {
                            $this->setElement($k, $item, $v[0], $v[1]);
                        }
                        else {
                            $this->setElement($k, $item, $v);
                        }
                    }
                    /**
                     * If enclosure is array (array('url' => ..., 'length' => ..., 'type' => ...))
                     */
                    else if ($k == 'enclosure' && is_array($v)) {
                        $nattr = array();
                        if (array_key_exists('url', $v))
                            $nattr['url'] = $v['url'];
                        if (array_key_exists('length', $v))
                            $nattr['length'] = $v['length'];
                        if (array_key_exists('type', $v))
                            $nattr['type'] = $v['type'];
                        if (NULL != $nattr) {
                            $this->setElement($k, $item, NULL, $nattr);
                        }
                    }
                    /**
                     * If source is array ('source', array('url' => ...))
                     */
                    else if ($k == 'source' && is_array($v)) {
                        $this->setElement($k, $item, $v[0], $v[1]);
                    }
                    else {
                        $this->setElement($k, $item, $v);
                    }
                }
            }
        }
    }

    public function getRss() {
        return $this->getXML(true);
    }
}