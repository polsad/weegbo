<?php
/**
 * Weegbo CaptchaExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.extensions.captcha
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
/**
 * CaptchaExtension class
 *
 * Extension for generate captcha image and check entered captcha
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.base.extensions.captcha
 * @since 0.8
 */
class CaptchaExtension {
    /**
     * @var string alphabet
     */
    private $_alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';
    /**
     * @var string allowed alphabet
     */
    private $_allowed_symbols = '23456789abcdeghkmnpqsuvxyz';
    /**
     * @var int captcha length
     */
    private $_length = 5;
    /**
     * @var int captcha image width
     */
    private $_width = 120;
    /**
     * @var int captcha image height
     */
    private $_height = 60;
    /**
     * @var int amplitude
     */
    private $_fluctuation_amplitude = 5;
    /**
     * @var array rgb color captha font
     */
    private $_foreground_color = array(24, 159, 35);
    /**
     * @var array rgb color captha background
     */
    private $_background_color = array(255, 255, 255);
    /**
     * @var int quality, if captch output as JPEG file
     */
    private $_quality = 80;

    public function __get($var) {
        return Registry::get($var);
    }

    public function __construct() {
        $this->load->extension('session');
    }

    /**
     * Compare value $captcha with value in session
     *
     * @access public
     * @param string $captcha - string with captcha
     * @return bool
     */
    public function checkCaptcha($captcha) {
        $result = false;
        if ($this->session->check('captcha')) {
            $scaptcha = $this->session->get('captcha');
            if ($scaptcha == $captcha) {
                $result = true;
            }
            $this->session->delete('captcha');
        }
        return $result;
    }

    /**
     * Generate captcha image
     *
     * @access public
     * @param string $output_file_type jpeg, gif or png
     * @return void
     */
    public function getCaptchaImage($output_file_type = 'gif') {
        $keystring = $this->generateCaptcha();
        if ($keystring == '')
            return false;

        $alength = strlen($this->_alphabet);
        do {
            $font_file = dirname(__FILE__).'/fonts/rockwell.png';
            $font = imagecreatefrompng($font_file);
            imagealphablending($font, true);
            $fontfile_width = imagesx($font);
            $fontfile_height = imagesy($font) - 1;
            $font_metrics = array();
            $symbol = 0;
            $reading_symbol = false;

            for ($i = 0; $i < $fontfile_width && $symbol < $alength; $i++) {
                $transparent = (imagecolorat($font, $i, 0) >> 24) == 127;
                if (!$reading_symbol && !$transparent) {
                    $font_metrics[$this->_alphabet{$symbol}] = array('start' => $i);
                    $reading_symbol = true;
                    continue;
                }
                if ($reading_symbol && $transparent) {
                    $font_metrics[$this->_alphabet{$symbol}]['end'] = $i;
                    $reading_symbol = false;
                    $symbol++;
                    continue;
                }
            }

            $img = imagecreatetruecolor($this->_width, $this->_height);
            imagealphablending($img, true);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefilledrectangle($img, 0, 0, $this->_width - 1, $this->_height - 1, $white);

            $x = 1;
            for ($i = 0; $i < $this->_length; $i++) {
                $m = $font_metrics[$keystring[$i]];
                $y = mt_rand(-$this->_fluctuation_amplitude, $this->_fluctuation_amplitude) + ($this->_height - $fontfile_height) / 2 + 2;
                imagecopy($img, $font, $x - 1, $y, $m['start'], 1, $m['end'] - $m['start'], $fontfile_height);
                $x += $m['end'] - $m['start'] - 1;
            }
        }
        while ($x >= $this->_width - 10); // while not fit in canvas

        $center = $x / 2;

        $captcha = imagecreatetruecolor($this->_width, $this->_height);
        $foreground = imagecolorallocate($captcha, $this->_foreground_color[0], $this->_foreground_color[1], $this->_foreground_color[2]);
        $background = imagecolorallocate($captcha, $this->_background_color[0], $this->_background_color[1], $this->_background_color[2]);
        imagefilledrectangle($captcha, 0, 0, $this->_width - 1, $this->_height - 1, $background);
        imagefilledrectangle($captcha, 0, $this->_height, $this->_width - 1, $this->_height + 12, $foreground);

        // periods
        $rand1 = mt_rand(750000, 1200000) / 10000000;
        $rand2 = mt_rand(750000, 1200000) / 10000000;
        $rand3 = mt_rand(750000, 1200000) / 10000000;
        $rand4 = mt_rand(750000, 1200000) / 10000000;
        // phases
        $rand5 = mt_rand(0, 31415926) / 10000000;
        $rand6 = mt_rand(0, 31415926) / 10000000;
        $rand7 = mt_rand(0, 31415926) / 10000000;
        $rand8 = mt_rand(0, 31415926) / 10000000;
        // amplitudes
        $rand9 = mt_rand(330, 420) / 110;
        $rand10 = mt_rand(330, 450) / 110;

        for ($x = 0; $x < $this->_width; $x++) {
            for ($y = 0; $y < $this->_height; $y++) {
                $sx = $x + (sin($x * $rand1 + $rand5) + sin($y * $rand3 + $rand6)) * $rand9 - $this->_width / 2 + $center + 1;
                $sy = $y + (sin($x * $rand2 + $rand7) + sin($y * $rand4 + $rand8)) * $rand10;
                if ($sx < 0 || $sy < 0 || $sx >= $this->_width - 1 || $sy >= $this->_height - 1)
                    continue;
                else {
                    $color = imagecolorat($img, $sx, $sy) & 0xFF;
                    $color_x = imagecolorat($img, $sx + 1, $sy) & 0xFF;
                    $color_y = imagecolorat($img, $sx, $sy + 1) & 0xFF;
                    $color_xy = imagecolorat($img, $sx + 1, $sy + 1) & 0xFF;
                }
                if ($color == 255 && $color_x == 255 && $color_y == 255 && $color_xy == 255)
                    continue;
                else if ($color == 0 && $color_x == 0 && $color_y == 0 && $color_xy == 0) {
                    $newred = $this->_foreground_color[0];
                    $newgreen = $this->_foreground_color[1];
                    $newblue = $this->_foreground_color[2];
                }
                else {
                    $frsx = $sx - floor($sx);
                    $frsy = $sy - floor($sy);
                    $frsx1 = 1 - $frsx;
                    $frsy1 = 1 - $frsy;
                    $newcolor = ($color * $frsx1 * $frsy1 + $color_x * $frsx * $frsy1 + $color_y * $frsx1 * $frsy + $color_xy * $frsx * $frsy);
                    if ($newcolor > 255)
                        $newcolor = 255;
                    $newcolor = $newcolor / 255;
                    $newcolor0 = 1 - $newcolor;
                    $newred = $newcolor0 * $this->_foreground_color[0] + $newcolor * $this->_background_color[0];
                    $newgreen = $newcolor0 * $this->_foreground_color[1] + $newcolor * $this->_background_color[1];
                    $newblue = $newcolor0 * $this->_foreground_color[2] + $newcolor * $this->_background_color[2];
                }
                imagesetpixel($captcha, $x, $y, imagecolorallocate($captcha, $newred, $newgreen, $newblue));
            }
        }

        Header('Expires: Mon, 4 Jul 1997 05:00:00 GMT');
        Header('Cache-Control: no-store, no-cache, must-revalidate');
        Header('Cache-Control: post-check=0, pre-check=0', FALSE);
        Header('Pragma: no-cache');
        switch ($output_file_type) {
            case 'jpeg':
                if (function_exists("imagejpeg")) {
                    Header("Content-Type: image/jpeg");
                    imagejpeg($captcha, null, $this->_quality);
                }
                break;
            case 'gif':
                if (function_exists("imagegif")) {
                    Header("Content-Type: image/gif");
                    imagegif($captcha);
                }
                break;
            case 'png':
                if (function_exists("imagepng")) {
                    Header("Content-Type: image/x-png");
                    imagepng($captcha);
                }
                break;
        }
    }

    /**
     * Generate captcha string
     *
     * @access private
     * @return string
     */
    private function generateCaptcha() {
        $keystring = '';
        for ($i = 0; $i < $this->_length; $i++) {
            $keystring .=$this->_allowed_symbols{mt_rand(0, strlen($this->_allowed_symbols) - 1)};
        }
        $this->session->set('captcha', $keystring);
        return $keystring;
    }
}