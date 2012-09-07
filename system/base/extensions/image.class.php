<?php
/**
 * Weegbo ImageExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 *
 * Class for work with images
 *
 * @package system.base.extensions
 * @since 0.8
 */
class ImageExtension {
    /**
     * @var int JPEG quality
     */
    private $_jpgQuality = 90;

    /**
     * Set jpeg quality
     *
     * @access public
     * @param  int $quality
     * @return void
     */
    public function setJpgQuality($quality) {
        $this->_jpgQuality = (int) $quality;
    }

    /**
     * Resize source image and save result.
     *
     * @access public
     * @param string $iImg input image file
     * @param string $oImg output image file
     * @param int $width output image width
     * @param int $height output image hight
     * @param bool $type
     * @return bool
     */
    public function resize($iImg, $oImg, $width = 0, $height = 0, $type = true) {
        $result = true;
        $iSrc = $oSrc = null;
        $iSize = getimagesize($iImg);

        $width = (int) $width;
        $height = (int) $height;

        if (($width == 0 && $height == 0) || ($width == $iSize[0] && $height == $iSize[1])) {
            $result = @copy($iImg, $oImg);
        }
        else {
            // Calculate width and height for new image
            $this->_calcImgs($iSize, $type, $width, $height);
            // Get image
            $iSrc = $this->_getImg($iSize[2], $iImg);

            $oSrc = imagecreatetruecolor($width, $height);
            $this->_transparentImg($iSrc, $oSrc, $iSize[2]);
            imagecopyresampled($oSrc, $iSrc, 0, 0, 0, 0, $width, $height, $iSize[0], $iSize[1]);

            // Save image
            $this->_saveImg($iSize[2], $oSrc, $oImg);
            imagedestroy($oSrc);
            imagedestroy($iSrc);
        }
        return $result;
    }

    /**
     * Crate thumb
     * Create thumb $width x $height, crop image
     * 
     * @access public
     * @param type $iImg input image
     * @param type $oImg output image
     * @param type $width thumb width
     * @param type $height thumb height
     * @return bool
     */
    public function thumb($iImg, $oImg, $width, $height) {
        $result = true;
        $iSrc = $oSrc = null;
        $iSize = getimagesize($iImg);
        $tSize = array($width, $height);

        $width = (int) $width;
        $height = (int) $height;

        if (($width == 0 && $height == 0) || ($width == $iSize[0] && $height == $iSize[1])) {
            $result = @copy($iImg, $oImg);
        }
        else {
            // Calculate width and height for new image
            $this->_calcImgs($iSize, false, $width, $height);
            // Get image
            $iSrc = $this->_getImg($iSize[2], $iImg);

            $oSrc = imagecreatetruecolor($tSize[0], $tSize[1]);
            $this->_transparentImg($iSrc, $oSrc, $iSize[2]);
            $x = $y = 0;
            if ($width == $tSize[0]) {
                $y = round(($height - $tSize[1]) / 2);
            }
            if ($height == $tSize[1]) {
                $x = round(($width - $tSize[0]) / 2);
            }
            imagecopyresampled($oSrc, $iSrc, -$x, -$y, 0, 0, $width, $height, $iSize[0], $iSize[1]);

            // Save image
            $this->_saveImg($iSize[2], $oSrc, $oImg);
            imagedestroy($oSrc);
            imagedestroy($iSrc);
        }
        return $result;
    }

    /**
     * Crop source image and save result.
     *
     * @access public
     * @param string $iImg input image
     * @param string $oImg output image
     * @param int $width output image width
     * @param int $height output image hight
     * @param int $x left top x coord
     * @param int $y left top y coord
     * @return bool
     */
    public function crop($iImg, $oImg, $x = 0, $y = 0, $width = 0, $height = 0) {
        $result = true;
        $iSrc = $oSrc = null;
        $iSize = getimagesize($iImg);

        $x = (int) $x;
        $y = (int) $y;
        $width = (int) $width;
        $height = (int) $height;

        if ($width == 0 && $height == 0) {
            $result = false;
        }
        else {
            $x = ($x > $iSize[0] || $x < 0) ? 0 : $x;
            $y = ($y > $iSize[1] || $y < 0) ? 0 : $y;
            if (($width + $x) > $iSize[0]) {
                $width = $iSize[0] - $x;
            }
            if (($height + $y) > $iSize[1]) {
                $height = $iSize[1] - $y;
            }

            if ($width == $iSize[0] && $height == $iSize[0]) {
                $result = @copy($iImg, $oImg);
            }
            else {
                // Get image
                $iSrc = $this->_getImg($iSize[2], $iImg);

                $oSrc = imagecreatetruecolor($width, $height);
                $this->_transparentImg($iSrc, $oSrc, $iSize[2]);
                imagecopyresampled($oSrc, $iSrc, 0, 0, $x, $y, $width, $height, $width, $height);

                // Save image
                $this->_saveImg($iSize[2], $oSrc, $oImg);
                imagedestroy($oSrc);
                imagedestroy($iSrc);
            }
        }
        return $result;
    }

    /**
     * Set watermark text on image.
     *
     * @access public
     * @param string $iImg input image
     * @param string $oImg output imagee
     * @param string $text text string
     * @param array  $options array with options
     * $options['font'] - path to ttf font, or null
     * $options['font-size'] - for $options['font'] = null, must be between 1 and 5
     * $options['font-color'] = '#EAEAEA', default #FFFFFF
     * $options['align']  = 'center' (must be 'left', 'center', 'right', default 'right')
     * $options['valign'] = 'center' (must be 'top',  'middle', 'bottom', default 'bottom')
     * $options['margin'] = 5 (number in px, default = 5)
     * @return bool
     */
    public function textWatermark($iImg, $oImg, $text, $options = array()) {
        // Image vars
        $result = true;
        $iSrc = $oSrc = null;
        $iSize = getimagesize($iImg);

        // Font
        $options['font'] = isset($options['font']) ? $options['font'] : null;
        $options['font-size'] = isset($options['font-size']) ? (int) $options['font-size'] : 10;
        $options['font-color'] = isset($options['font-color']) ? $options['font-color'] : '#FFFFFF';
        // Positions
        $options['align'] = isset($options['align']) ? strtolower(trim($options['align'])) : 'right';
        $options['valign'] = isset($options['valign']) ? strtolower(trim($options['valign'])) : 'bottom';
        $options['margin'] = isset($options['margin']) ? (int) $options['margin'] : 5;

        $iSrc = $this->_getImg($iSize[2], $iImg);
        $oSrc = imagecreatetruecolor($iSize[0], $iSize[1]);

        imagecopyresampled($oSrc, $iSrc, 0, 0, 0, 0, $iSize[0], $iSize[1], $iSize[0], $iSize[1]);
        $color = $this->_hexToRgb($color);
        $color = imageColorAllocate($oSrc, $color[0], $color[1], $color[2]);

        $x = $y = 0;
        // Generate color
        $color = $this->_hexToRgb($options['font-color']);
        $color = imageColorAllocate($oSrc, $color[0], $color[1], $color[2]);
        // Calc text
        $this->_calcTxtWatermark($text, $options, $iSize[0], $iSize[1], $x, $y);

        // Generate text 
        if ($options['font'] != null) {
            $result = imagettftext($oSrc, $options['font-size'], 0, $x, $y, $color, $options['font'], $text);
        }
        else {
            $result = imagestring($oSrc, $options['font-size'], $x, $y, $text, $color);
        }

        // Save image
        $this->_saveImg($iSize[2], $oSrc, $oImg);
        imagedestroy($oSrc);
        imagedestroy($iSrc);
        return $result;
    }

    /**
     * Set image watermark on image.
     *
     * @access public
     * @param string $iImg      path to source image file
     * @param string $oImg path to result image file
     * @param string $wImg   path to watermark file
     * @param array $options      array with options
     *
     * $options['align']  = 'center' (must be 'left', 'center', 'right', default 'right')
     * $options['valign'] = 'center' (must be 'top',  'center', 'bottom', default 'bottom')
     * $options['margin'] = 5 (number in px, default = 3)
     * $options['opacity'] = 100 (% transparence default = 70%), only for 'opacity-mode' = true
     * $options['opacity-mode'] = false If source image and watermark is transparence, use $options['opacity-mode'] = true
     *
     * @return bool
     */
    public function imageWatermark($iImg, $oImg, $wImg, $options = array()) {
        // Positions
        $options['align'] = isset($options['align']) ? strtolower(trim($options['align'])) : 'right';
        $options['valign'] = isset($options['valign']) ? strtolower(trim($options['valign'])) : 'bottom';
        $options['margin'] = isset($options['margin']) ? (int) $options['margin'] : 5;
        $options['opacity'] = isset($options['opacity']) ? $options['opacity'] : 70;
        $options['opacity-mode'] = isset($options['opacity-mode']) ? (bool) $options['opacity-mode'] : false;
        // Image vars
        $iSrc = $oSrc = $wSrc = $woSrc = null;
        $result = true;

        $iSize = getimagesize($iImg);
        $wSize = getimagesize($wImg);

        $iSrc = $this->_getImg($iSize[2], $iImg);
        $wSrc = $this->_getImg($wSize[2], $wImg);

        $x = $y = 0;
        $this->_calcImgWatermark($options, $iSize, $wSize, $x, $y);

        if ($options['opacity-mode'] === false) {
            imageCopyMerge($iSrc, $wSrc, $x, $y, 0, 0, $wSize[0], $wSize[1], $options['opacity']);
            // Save image
            $this->_saveImg($iSize[2], $iSrc, $oImg);
            imagedestroy($wSrc);
        }
        else {
            $oSrc = imagecreatetruecolor($iSize[0], $iSize[1]);
            $this->_transparentImg($iSrc, $oSrc, $iSize[2]);
            imagecopyresampled($oSrc, $iSrc, 0, 0, 0, 0, $iSize[0], $iSize[1], $iSize[0], $iSize[1]);
            imagedestroy($iSrc);

            $woSrc = imagecreatetruecolor($wSize[0], $wSize[1]);
            $this->_transparentImg($wSrc, $woSrc, $wSize[2]);
            imagecopyresampled($woSrc, $wSrc, 0, 0, 0, 0, $wSize[0], $wSize[1], $wSize[0], $wSize[1]);
            imagedestroy($wSrc);

            imagealphablending($oSrc, 1);
            imagealphablending($woSrc, 1);
            imagecopy($oSrc, $woSrc, $x, $y, 0, 0, $wSize[0], $wSize[1]);


            // Save image
            $this->_saveImg($iSize[2], $oSrc, $oImg);
            imagedestroy($oSrc);
            imagedestroy($woSrc);
        }

        return $result;
    }

    private function _getImg($type, $iImg) {
        $iSrc = null;
        switch ($type) {
            case 1: $iSrc = imagecreatefromgif($iImg);
                break;
            case 2: $iSrc = imagecreatefromjpeg($iImg);
                break;
            case 3: $iSrc = imagecreatefrompng($iImg);
                break;
        }
        return $iSrc;
    }

    private function _saveImg($type, &$oSrc, &$oImg) {
        switch ($type) {
            case 1: imagegif($oSrc, $oImg);
                break;
            case 2: imagejpeg($oSrc, $oImg, $this->_jpgQuality);
                break;
            case 3: imagepng($oSrc, $oImg);
                break;
        }
    }

    private function _calcImgs($iSize, $type, &$width, &$height) {
        $coeff = 0;
        $ocoeff = round($width / $height, 4);
        $icoeff = round($iSize[0] / $iSize[1], 4);
        if ($type === true) {
            if ($ocoeff < $icoeff || ($icoeff == 1 && $ocoeff < 1)) {
                $this->_calcImgSize($width, $iSize[0], $iSize[1], $coeff, $height);
            }
            else {
                $this->_calcImgSize($height, $iSize[1], $iSize[0], $coeff, $width);
            }
        }
        else {
            if ($ocoeff < $icoeff || ($icoeff == 1 && $ocoeff < 1)) {
                $this->_calcImgSize($height, $iSize[1], $iSize[0], $coeff, $width);
            }
            else {
                $this->_calcImgSize($width, $iSize[0], $iSize[1], $coeff, $height);
            }
        }
    }

    private function _calcImgSize($i1, $i2, $i3, &$o1, &$o2) {
        $o1 = $i1 / $i2;
        $o2 = round($i3 * $o1);
    }

    private function _calcTxtWatermark($text, $options, $width, $height, &$x, &$y) {
        if ($options['font'] == null) {
            $x = imagefontwidth($options['font-size']) * strlen($text);
            $y = imagefontheight($options['font-size']);
        }
        else {
            $tmp = imagettfbbox($options['font-size'], 0, $options['font'], $text);
            $x = $tmp[2];
            $y = $tmp[3];
        }
        // Calc X
        switch ($options['align']) {
            case 'center':
                $x = $width / 2 - $x / 2;
                break;
            case 'right':
                $x = $width - $x - $options['margin'];
                break;
            default:
                $x = 0 + $options['margin'];
                break;
        }
        // Calc Y
        switch ($options['valign']) {
            case 'center':
                $y = $height / 2 - $y / 2;
                break;
            case 'bottom':
                $y = $height - $y - $options['margin'];
                break;
            default:
                $y = 0 + $options['margin'];
                break;
        }
        $x = (int) $x;
        $y = (int) $y;
    }

    private function _calcImgWatermark($options, $iSize, $wSize, &$x, &$y) {
        switch ($options['align']) {
            case 'center':
                $x = $iSize[0] / 2 - $wSize[0] / 2;
                break;
            case 'right':
                $x = $iSize[0] - $wSize[0] - $options['margin'];
                break;
            default:
                $x = 0 + $options['margin'];
                break;
        }
        switch ($options['valign']) {
            case 'center':
                $y = $iSize[1] / 2 - $wSize[1] / 2;
                break;
            case 'right':
                $y = $iSize[1] - $wSize[1] - $options['margin'];
                break;
            default:
                $y = 0 + $options['margin'];
                break;
        }
        $x = (int) $x;
        $y = (int) $y;
    }

    /**
     * Convert hex color to array.
     *
     * @access private
     * @param string $color hex color (for example '#cccccc', '#fff')
     * @return array
     */
    private function _hexToRgb($color) {
        $color = str_replace('#', '', $color);
        $size = strlen($color);
        $result = array(255, 255, 255);
        if ($size == 6) {
            $r = substr($color, 0, 2);
            $g = substr($color, 2, 2);
            $b = substr($color, 4, 2);
            $result = array(hexdec($r), hexdec($g), hexdec($b));
        }
        if ($size == 3) {
            $r = substr($color, 0, 1);
            $g = substr($color, 1, 1);
            $b = substr($color, 2, 1);
            $result = array(hexdec($r.$r), hexdec($g.$g), hexdec($b.$b));
        }
        return $result;
    }

    /**
     * If input image support transparent, create output image transparently
     *
     * @access private
     * @param link $iSrc link on input image
     * @param link $oSrc link on output image
     * @param int $type
     * @return void
     */
    private function _transparentImg(&$iSrc, &$oSrc, $type) {
        /**
         * Transparent
         * Source code for transparent - http://alexle.net/archives/131/
         */
        if ($type == 1 || $type == 3) {
            $tIndex = imagecolortransparent($iSrc);
            $pSize = imagecolorstotal($iSrc);
            // If we have a specific transparent color
            if ($tIndex >= 0 && $tIndex < $pSize) {
                // Get the original image's transparent color's RGB values
                $tColor = imagecolorsforindex($iSrc, $tIndex);
                // Allocate the same color in the new image resource
                $tIndex = imagecolorallocate($oSrc, $tColor['red'], $tColor['green'], $tColor['blue']);
                // Completely fill the background of the new image with allocated color.
                imagefill($oSrc, 0, 0, $tIndex);
                // Set the background color for new image to transparent
                imagecolortransparent($oSrc, $tIndex);
            }
            // Always make a transparent background color for PNGs that don't have one allocated already
            elseif ($type == 3) {
                // Turn off transparency blending (temporarily)
                imagealphablending($oSrc, false);
                // Create a new transparent color for image
                $color = imagecolorallocatealpha($oSrc, 0, 0, 0, 127);
                // Completely fill the background of the new image with allocated color.
                imagefill($oSrc, 0, 0, $color);
                // Restore transparency blending
                imagesavealpha($oSrc, true);
            }
        }
    }
}