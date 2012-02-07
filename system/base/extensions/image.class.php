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
require_once(Config::get('path/extensions').'file.class.php');
class ImageExtension extends FileExtension {
    /**
     * @var int JPEG quality
     */
    private $_quality = 90;

    /**
     * Set jpeg quality
     *
     * @access public
     * @param  int $quality
     * @return void
     */
    public function setJpegQuality($quality) {
        $this->_quality = (int) $quality;
    }

    /**
     * Resize source image and save result.
     * Изменение происходит по относительно ширины, высоты, или в зависимости от большей стороны
     * Например, если исходное изображение 400x200 px,
     * - передан параметр width = 100, то полученное изображение будет 100x50px
     * - передан параметр height = 100, то полученное изображение будет 200x100px
     * - переданы width = 100, height = 100, то полученное изображение 100x50px, т.к. ширина больше высоты
     *
     * @access public
     * @param string $source      path to source image file
     * @param string $destination path to result image file
     * @param int $width          width result image
     * @param int $height         hight result image
     * @return bool
     */
    public function resizeImage($source, $destination, $width = 0, $height = 0) {
        $width = (int) $width;
        $height = (int) $height;

        $image_params = getimagesize($source);

        if ($width == 0 && $height != 0) {
            $coeff = $height / $image_params[1];
            $width = round($image_params[0] * $coeff);
        }
        elseif ($width != 0 && $height == 0) {
            $coeff = $width / $image_params[0];
            $height = round($image_params[1] * $coeff);
        }
        elseif ($width != 0 && $height != 0) {
            if ($width == $height) {
                if ($image_params[0] >= $image_params[1]) {
                    $coeff = $width / $image_params[0];
                    $height = round($image_params[1] * $coeff);
                }
                else {
                    $coeff = $height / $image_params[1];
                    $width = round($image_params[0] * $coeff);
                }
            }
            if ($width > $height) {
                $coeff = $width / $image_params[0];
                $height = round($image_params[1] * $coeff);
            }
            if ($width < $height) {
                $coeff = $height / $image_params[1];
                $width = round($image_params[0] * $coeff);
            }
        }
        elseif ($width == 0 && $height == 0) {
            $res = @copy($source, $destination);
            return $res;
        }

        switch ($image_params[2]) {
            case 1: $source_image = imagecreatefromgif($source);
                break;
            case 2: $source_image = imagecreatefromjpeg($source);
                break;
            case 3: $source_image = imagecreatefrompng($source);
                break;
            default: return false;
        }

        $output_image = imagecreatetruecolor($width, $height);
        $this->transparentImage($source_image, $output_image, $image_params[2]);

        imagecopyresampled($output_image, $source_image, 0, 0, 0, 0, $width, $height, $image_params[0], $image_params[1]);

        switch ($image_params[2]) {
            case 1: imagegif($output_image, $destination);
                break;
            case 2: imagejpeg($output_image, $destination, $this->_quality);
                break;
            case 3: imagepng($output_image, $destination);
                break;
        }

        imagedestroy($output_image);
        imagedestroy($source_image);
        return true;
    }

    /**
     * Create thumbnail from source image
     *
     * @access public
     * @param string $source      path to source image file
     * @param string $destination path to result image file
     * @param int $width          width result image
     * @param int $height         hight result image
     * @param int $type           resize по большей стороне (1), или по меньшей (2)
     * @return bool
     */
    public function createThumbnail($source, $destination, $width, $height, $type = 1) {
        $width = (int) $width;
        $height = (int) $height;

        $image_params = getimagesize($source);

        $thumb_coeff = round($width / $height, 4);
        $image_coeff = round($image_params[0] / $image_params[1], 4);

        switch ($type) {
            case 1:
                if ($thumb_coeff < $image_coeff) {
                    $coeff = $width / $image_params[0];
                    $height = round($image_params[1] * $coeff);
                }
                if ($thumb_coeff > $image_coeff) {
                    $coeff = $height / $image_params[1];
                    $width = round($image_params[0] * $coeff);
                }
                if ($image_coeff == 1) {
                    if ($thumb_coeff >= 1) {
                        $coeff = $height / $image_params[1];
                        $width = round($image_params[0] * $coeff);
                    }
                    if ($thumb_coeff < 1) {
                        $coeff = $width / $image_params[0];
                        $height = round($image_params[1] * $coeff);
                    }
                }
                break;
            case 2:
                if ($thumb_coeff < $image_coeff) {
                    $coeff = $height / $image_params[1];
                    $width = round($image_params[0] * $coeff);
                }
                if ($thumb_coeff > $image_coeff) {
                    $coeff = $width / $image_params[0];
                    $height = round($image_params[1] * $coeff);
                }
                if ($image_coeff == 1) {
                    if ($thumb_coeff >= 1) {
                        $coeff = $width / $image_params[0];
                        $height = round($image_params[1] * $coeff);
                    }
                    if ($thumb_coeff < 1) {
                        $coeff = $height / $image_params[1];
                        $width = round($image_params[0] * $coeff);
                    }
                }
                break;
        }

        switch ($image_params[2]) {
            case 1: $source_image = imagecreatefromgif($source);
                break;
            case 2: $source_image = imagecreatefromjpeg($source);
                break;
            case 3: $source_image = imagecreatefrompng($source);
                break;
            default: return false;
        }

        $output_image = imagecreatetruecolor($width, $height);
        $this->transparentImage($source_image, $output_image, $image_params[2]);

        imagecopyresampled($output_image, $source_image, 0, 0, 0, 0, $width, $height, $image_params[0], $image_params[1]);

        switch ($image_params[2]) {
            case 1: imagegif($output_image, $destination);
                break;
            case 2: imagejpeg($output_image, $destination, $this->_quality);
                break;
            case 3: imagepng($output_image, $destination);
                break;
        }

        imagedestroy($output_image);
        imagedestroy($source_image);
    }

    /**
     * Crop source image and save result.
     *
     * @access public
     * @param string $source      path to source image file
     * @param string $destination path to result image file
     * @param int $width           width result image
     * @param int $height         hight result image
     * @param int $start_x        left top x coord
     * @param int $start_y        left top y coord
     * @return bool
     */
    public function cropImage($source, $destination, $start_x = 0, $start_y = 0, $width = 0, $height = 0) {
        $width = (int) $width;
        $height = (int) $height;
        $start_x = (int) $start_x;
        $start_y = (int) $start_y;

        $image_params = getimagesize($source);

        if ($start_x > $image_params[0] || $start_x < 0)
            $start_x = 0;
        if ($start_y > $image_params[1] || $start_y < 0)
            $start_y = 0;

        if (($width + $start_x) > $image_params[0])
            $width = $image_params[0] - $start_x;
        if (($height + $start_y) > $image_params[1])
            $height = $image_params[1] - $start_y;

        if ($width == 0 && $height == 0)
            return false;

        if ($width == $image_params[0] && $height == $image_params[0]) {
            $res = @copy($source, $destination);
            return $res;
        }

        switch ($image_params[2]) {
            case 1: $source_image = imagecreatefromgif($source);
                break;
            case 2: $source_image = imagecreatefromjpeg($source);
                break;
            case 3: $source_image = imagecreatefrompng($source);
                break;
        }

        $output_image = imagecreatetruecolor($width, $height);
        $this->transparentImage($source_image, $output_image, $image_params[2]);
        imagecopyresampled($output_image, $source_image, 0, 0, $start_x, $start_y, $width, $height, $width, $height);

        switch ($image_params[2]) {
            case 1: imagegif($output_image, $destination);
                break;
            case 2: imagejpeg($output_image, $destination, $this->_quality);
                break;
            case 3: imagepng($output_image, $destination);
                break;
        }
        imagedestroy($output_image);
        imagedestroy($source_image);
        return true;
    }

    /**
     * Set watermark text on image.
     *
     * @access public
     * @param string $source      path to source image file
     * @param string $destination path to result image file
     * @param string $text        text string
     * @param array  $options     [optional] array with options
      $options['font_size']  = 1 (must be between 1 and 5, default 2)
      $options['font_color'] = '#EAEAEA', default #FFFFFF
      $options['align']  = 'center' (must be 'left', 'center', 'right', default 'right')
      $options['valign'] = 'center' (must be 'top',  'middle', 'bottom', default 'bottom')
      $options['margin'] = 5 (number in px, default = 3)
     * @return bool
     */
    public function setTextWatermark($source, $destination, $text, $options = NULL) {
        $font = isset($options['font_size']) ? (int) $options['font_size'] : 2;
        $color = isset($options['font_color']) ? strtolower(trim($options['font_color'])) : '#FFFFFF';
        $align = isset($options['align']) ? strtolower(trim($options['align'])) : 'right';
        $valign = isset($options['valign']) ? strtolower(trim($options['valign'])) : 'bottom';
        $margin = isset($options['margin']) ? (int) $options['margin'] : 3;

        $image_params = getimagesize($source);
        $width = $image_params[0];
        $height = $image_params[1];

        $output_image = imagecreatetruecolor($width, $height);
        switch ($image_params[2]) {
            case 1: $source_image = imagecreatefromgif($source);
                break;
            case 2: $source_image = imagecreatefromjpeg($source);
                break;
            case 3: $source_image = imagecreatefrompng($source);
                break;
        }

        $this->transparentImage($source_image, $output_image, $image_params[2]);

        imagecopyresampled($output_image, $source_image, 0, 0, 0, 0, $width, $height, $width, $height);
        $color = $this->hexToRgb($color);
        $color = imageColorAllocate($output_image, $color[0], $color[1], $color[2]);
        $text_x = $this->calcTextX($text, $align, $font, $margin, $width);
        $text_y = $this->calcTextY($text, $valign, $font, $margin, $height);
        $res = imagestring($output_image, $font, $text_x, $text_y, $text, $color);

        switch ($image_params[2]) {
            case 1: imagegif($output_image, $destination);
                break;
            case 2: imagejpeg($output_image, $destination, $this->_quality);
                break;
            case 3: imagepng($output_image, $destination);
                break;
        }

        imagedestroy($output_image);
        imagedestroy($source_image);
        return true;
    }

    /**
     * Set image watermark on image.
     *
     * @access public
     * @param string $source      path to source image file
     * @param string $destination path to result image file
     * @param string $watermark   path to watermark file
     * @param array $options      array with options
      $options['align']  = 'center' (must be 'left', 'center', 'right', default 'right')
      $options['valign'] = 'center' (must be 'top',  'center', 'bottom', default 'bottom')
      $options['margin'] = 5 (number in px, default = 3)
      $options['transp'] = 100 (% transparence default = 70%), only for 'common' mode
     * @param string $mode merge mode 'common' or 'transparence'. If source image and watermark is transparence,
     *                     use mode 'transparence'
     * @return bool
     */
    public function setImageWatermark($source, $destination, $watermark, $options = NULL, $mode = 'common') {
        $align = isset($options['align']) ? strtolower(trim($options['align'])) : 'right';
        $valign = isset($options['valign']) ? strtolower(trim($options['valign'])) : 'bottom';
        $margin = isset($options['margin']) ? (int) $options['margin'] : 3;
        $transp = isset($options['transp']) ? (int) $options['transp'] : 70;

        $image_params = getimagesize($source);
        $water_params = getimagesize($watermark);

        $width_s = $image_params[0];
        $height_s = $image_params[1];
        $width_w = $water_params[0];
        $height_w = $water_params[1];

        switch ($image_params[2]) {
            case 1: $source_image = imagecreatefromgif($source);
                break;
            case 2: $source_image = imagecreatefromjpeg($source);
                break;
            case 3: $source_image = imagecreatefrompng($source);
                break;
        }
        switch ($water_params[2]) {
            case 1: $water_img = imagecreatefromgif($watermark);
                break;
            case 2: $water_img = imagecreatefromjpeg($watermark);
                break;
            case 3: $water_img = imagecreatefrompng($watermark);
                break;
        }

        $x = $this->calcImageX($align, $margin, $width_w, $width_s);
        $y = $this->calcImageY($valign, $margin, $height_w, $height_s);

        switch ($mode) {
            case 'common':
                imageCopyMerge($source_image, $water_img, $x, $y, 0, 0, $width_w, $height_w, $transp);
                switch ($image_params[2]) {
                    case 1: imagegif($source_image, $destination);
                        break;
                    case 2: imagejpeg($source_image, $destination, $this->_quality);
                        break;
                    case 3: imagepng($source_img, $destination);
                        break;
                }
                imagedestroy($water_img);
                imagedestroy($source_image);
                break;
            case 'transparence':
                $output_image = imagecreatetruecolor($width_s, $height_s);
                $this->transparentImage($source_image, $output_image, $image_params[2]);
                imagecopyresampled($output_image, $source_image, 0, 0, 0, 0, $width_s, $height_s, $width_s, $height_s);
                imagedestroy($source_image);

                $output_water = imagecreatetruecolor($width_w, $width_w);
                $this->transparentImage($water_img, $output_water, $image_params[2]);
                imagecopyresampled($output_water, $water_img, 0, 0, 0, 0, $width_w, $height_w, $width_w, $height_w);
                imagedestroy($water_img);

                imagealphablending($output_image, 1);
                imagealphablending($output_water, 1);
                imagecopy($output_image, $output_water, $x, $y, 0, 0, $width_w, $height_w);

                switch ($image_params[2]) {
                    case 1: imagegif($output_image, $destination);
                        break;
                    case 2: imagejpeg($output_image, $destination, $this->_quality);
                        break;
                    case 3: imagepng($output_image, $destination);
                        break;
                }
                imagedestroy($output_image);
                imagedestroy($output_water);
                break;
        }
        return true;
    }

    /**
     * Calculate X coord left top point.
     *
     * @access private
     * @param string $text  text string
     * @param string $align center, right or left
     * @param int $font     font size
     * @param int $margin   margin in pixels
     * @param int $width    picture width
     * @return int
     */
    private function calcTextX($text, $align, $font, $margin, $width) {
        $x = imagefontwidth($font) * strlen($text);
        switch ($align) {
            case 'center': $x = $width / 2 - $x / 2;
                break;
            case 'right': $x = $width - $x - $margin;
                break;
            case 'left':
            default: $x = 0 + $margin;
                break;
        }
        return $x;
    }

    /**
     * Calculate Y coord left top point.
     *
     * @access private
     * @param string $text   text string
     * @param string $valign center, bottom or top
     * @param int $font      font size
     * @param int $margin    margin in pixels
     * @param int $height    picture height
     * @return int
     */
    private function calcTextY($text, $valign, $font, $margin, $height) {
        $y = imagefontheight($font);
        switch ($valign) {
            case 'center': $y = $height / 2 - $y / 2;
                break;
            case 'bottom': $y = $height - $y - $margin;
                break;
            case 'top':
            default: $y = 0 + $margin;
                break;
        }
        return $y;
    }

    /**
     * Calculate X coord left top point.
     *
     * @access private
     * @param string $align center, right or left
     * @param int $margin   margin in pixels
     * @param int $width_w  watermark width
     * @param int $width_s  picture width
     * @return int
     */
    private function calcImageX($align, $margin, $width_w, $width_s) {
        switch ($align) {
            case 'center': $x = $width_s / 2 - $width_w / 2;
                break;
            case 'right': $x = $width_s - $width_w - $margin;
                break;
            case 'left':
            default: $x = 0 + $margin;
                break;
        }
        return $x;
    }

    /**
     * Calculate Y coord left top point.
     *
     * @access private
     * @param string $valign center, bottom or top
     * @param int $margin    margin in pixels
     * @param int $height_w  watermark height
     * @param int $height_s  picture height
     * @return int
     */
    private function calcImageY($valign, $margin, $height_w, $height_s) {
        switch ($valign) {
            case 'center': $y = $height_s / 2 - $height_w / 2;
                break;
            case 'bottom': $y = $height_s - $height_w - $margin;
                break;
            case 'top':
            default: $y = 0 + $margin;
                break;
        }
        return $y;
    }

    /**
     * Convert hex color to array.
     *
     * @access private
     * @param string $color hex color (for example '#cccccc', '#fff')
     * @return array
     */
    private function hexToRgb($color) {
        $color = str_replace('#', '', $color);
        $size = strlen($color);
        if ($size == 6) {
            $r = substr($color, 0, 2);
            $g = substr($color, 2, 2);
            $b = substr($color, 4, 2);
            return array(hexdec($r), hexdec($g), hexdec($b));
        }
        if ($size == 3) {
            $r = substr($color, 0, 1);
            $g = substr($color, 1, 1);
            $b = substr($color, 2, 1);
            return array(hexdec($r.$r), hexdec($g.$g), hexdec($b.$b));
        }
        return array(255, 255, 255);
    }

    /**
     * If input image support transparent, create output image transparently
     *
     * @access private
     * @param link $source_image link on input image
     * @param link $output_image link on output image
     * @param int $image_type
     * @return void
     */
    private function transparentImage(&$source_image, &$output_image, $image_type) {
        /**
         * Transparent
         * Source code for transparent - http://alexle.net/archives/131/
         */
        if ($image_type == 1 || $image_type == 3) {
            $trnprt_indx = imagecolortransparent($source_image);
            $pallet_size = imagecolorstotal($source_image);
            // If we have a specific transparent color
            if ($trnprt_indx >= 0 && $trnprt_indx < $pallet_size) {
                // Get the original image's transparent color's RGB values
                $trnprt_color = imagecolorsforindex($source_image, $trnprt_indx);
                // Allocate the same color in the new image resource
                $trnprt_indx = imagecolorallocate($output_image, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                // Completely fill the background of the new image with allocated color.
                imagefill($output_image, 0, 0, $trnprt_indx);
                // Set the background color for new image to transparent
                imagecolortransparent($output_image, $trnprt_indx);
            }
            // Always make a transparent background color for PNGs that don't have one allocated already
            elseif ($image_type == 3) {
                // Turn off transparency blending (temporarily)
                imagealphablending($output_image, false);
                // Create a new transparent color for image
                $color = imagecolorallocatealpha($output_image, 0, 0, 0, 127);
                // Completely fill the background of the new image with allocated color.
                imagefill($output_image, 0, 0, $color);
                // Restore transparency blending
                imagesavealpha($output_image, true);
            }
        }
    }
}