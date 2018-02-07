<?php
/**
 *   Rich Arbitrary Precision Integer Mathematics Library for PHP
 *   (c) 2014-2018, Rich Morgan <rich@richmorgan.me>
 *
 *   This class is in BETA status and may not work correctly. Do not
 *   use in a production environment!
 */

namespace RAPIM;

class Math
{
    private $internal      = false;
    private $a_orig_size   = 0;
    private $b_orig_size   = 0;
    private $a_padded_size = 0;
    private $b_padded_size = 0;
    private $subtotal      = '0';
    private $a_orig        = '';
    private $b_orig        = '';
    private $a_now         = '';
    private $b_now         = '';
    private $init_called   = false;
    private $math_type     = '';
    private $test          = false;
    private $maxint        = 0;
    private $digits        = array();
    private $gmpZero       = 0;
    private $gmpSixteen    = 16;
    private $gmpFiveEight  = 58;
    private $gmpTwoFiveSix = 256;
    
    /**
     * Public constructor method to initialize class properties
     *
     * @param bool $test
     */
    public function __construct($test = false)
    {
        for ($x = 0; $x < 256; $x++) {
            $this->digits[$x] = chr($x);
        }
        
        $this->maxint = (PHP_INT_SIZE > 4) ? 10: 5;
        
        if ($test) {
            $this->math_type = 'RPM';
        } else {
            if (true === function_exists('gmp_add')) {
                // GMP is preferred for speed
                $this->gmpZero       = gmp_init($this->gmpZero);
                $this->gmpSixteen    = gmp_init($this->gmpSixteen);
                $this->gmpFiveEight  = gmp_init($this->gmpFiveEight);
                $this->gmpTwoFiveSix = gmp_init($this->gmpTwoFiveSix);
                $this->math_type = 'GMP';
            } else if (true === function_exists('bcadd')) {
                // BC is second choice
                $this->math_type = 'BCM';
            } else {
                // Fallback to library functions
                $this->math_type = 'RPM';
            }
        }
    }
    
    /**
     * Public method for multiplying two numbers
     *
     * @param  string $x The first number
     * @param  string $y The second number
     * @return mixed     Either boolean 'false' or the result as a string
     */
    public function mul($x, $y)
    {
        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_mul(gmp_init($x), gmp_init($y)));
            case 'BCM':
                return bcmul($x, $y);
            case 'RPM';
                return $this->rpmul($x, $y);
            default:
                return false;
        }
    }
    
    /**
     * Public method for adding two numbers
     *
     * @param  string $x The first number
     * @param  string $y The second number
     * @return mixed     Either boolean 'false' or the result as a string
     */
    public function add($x, $y)
    {
        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_add(gmp_init($x), gmp_init($y)));
            case 'BCM':
                return bcadd($x, $y);
            case 'RPM';
                return $this->rpadd($x, $y);
            default:
                return false;
        }
    }
    
    /**
     * Public method for subtracting two numbers
     *
     * @param  string $x The first number
     * @param  string $y The second number
     * @return mixed     Either boolean 'false' or the result as a string
     */
    public function sub($x, $y)
    {
        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_sub(gmp_init($x), gmp_init($y)));
            case 'BCM':
                return bcsub($x, $y);
            case 'RPM';
                return $this->rpsub($x, $y);
            default:
                return false;
        }
    }
    
    /**
     * Public method for dividing two numbers
     *
     * @param  string $x The first number
     * @param  string $y The second number
     * @return mixed     Either boolean 'false' or the result as a string
     */
    public function div($x, $y)
    {
        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_div_q(gmp_init($x), gmp_init($y), GMP_ROUND_ZERO));
            case 'BCM':
                return bcdiv($x, $y);
            case 'RPM';
                return $this->rpdiv($x, $y);
            default:
                return false;
        }
    }
    
    /**
     * Public method for calculating 'x' modulo 'b'
     *
     * @param  string $x The first number
     * @param  string $y The second number
     * @return mixed     Either boolean 'false' or the result as a string
     */
    public function mod($x, $y)
    {
        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_mod(gmp_init($x), gmp_init($y)));
            case 'BCM':
                return bcmod($x, $y);
            case 'RPM';
                return $this->rpmod($x, $y);
            default:
                return false;
        }
    }
    
    /**
     * Public method for calculating the inverse modulo
     *
     * @param  string $x The first number
     * @param  string $y The second number
     * @return mixed     Either boolean 'false' or the result as a string
     */
    public function invmod($x, $y)
    {
        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_invert(gmp_init($x), gmp_init($y)));
            case 'BCM':
                return bc_invert($x, $y);
            case 'RPM';
                // TODO
                // return $this->rpinvmod($x, $y);
                break;
            default:
                return false;
        }
    }
    
    /**
     * Public method for comparing two numbers
     *
     * @param string $x The first number
     * @param string $x The second number
     */
    public function comp($x, $y)
    {
        switch ($this->math_type) {
            case 'GMP':
                return gmp_cmp(gmp_init($x), gmp_init($y));
            case 'BCM':
                return bccomp($x, $y);
            case 'RPM';
                return $this->rpcomp($x, $y);
            default:
                return false;
        }
    }
    
    /**
     * Public method for raising a number to a power
     *
     * @param string $x The first number
     * @param string $x The second number
     */
    public function power($x, $y)
    {
        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_pow(gmp_init($x), (int)$y));
            case 'BCM':
                return bcpow($x, $y);
            case 'RPM';
                return $this->rppow($x, $y);
            default:
                return false;
        }
    }
    
    /**
     * Library implementation for multiplying two numbers
     *
     * @param  string $x The first number
     * @param  string $y The second number
     * @return string    The result as a string
     * @throws \Exception $e
     */
    final private function rpmul($x, $y)
    {
        try {
            settype($x, 'string');
            settype($y, 'string');
            
            if ((trim($x) == '' || empty($x)) || (trim($y) == '' || empty($y))) {
                return '0';
            }
            
            if ($y == '1') {
                return $x;
            }
            
            if ($x == '1') {
                return $y;
            }
            
            $x_size = strlen($x);
            $y_size = strlen($y);
            $chunk = 0;
            
            if ($x_size > $y_size) {
                $chunk = $x_size;
            } else {
                $chunk = $y_size;
            }
            
            if ($chunk < $this->maxint) {
                return (int)((int)$x * (int)$y);
            }
            
            $m  = (int)((int)$chunk / 2);
            $x1 = substr($x, 0, -$m);
            $x2 = substr($x, -$m);
            $y1 = substr($y, 0, -$m);
            $y2 = substr($y, -$m);
            $a  = $this->rpmul($x2, $y2);
            $b  = $this->rpmul($x1, $y1);
            $c  = $this->rpmul($this->rpadd($x1, $x2), $this->rpadd($y1, $y2));
            $d  = $this->rpsub($this->rpsub($c, $a), $b);
            
            for ($qq = 0; $qq < $m; $qq++) {
                $d = $d . '0';
            }
            
            $e = $b;
            
            for ($qq = 0; $qq < ($m * 2); $qq++) {
                $e = $e . '0';
            }
            
            return $this->rpadd($a, $this->rpadd($d, $e));
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for adding two numbers
     *
     * @param  string $x The first number
     * @param  string $y The second number
     * @return string    The result as a string
     * @throws \Exception $e
     */
    final private function rpadd($a, $b)
    {
        try {
            settype($a, 'string');
            settype($b, 'string');
            
            if ((trim($a) == '' || empty($a)) && !empty($b)) {
                return $b;
            }
            
            if ((trim($b) == '' || empty($b)) && !empty($a)) {
                return $a;
            }
            
            if ((trim($a) == '' || empty($a)) && (trim($b) == '' || empty($b))) {
                return '0';
            }
            
            $len_a = strlen($a);
            $len_b = strlen($b);
            
            if ($len_a < $this->maxint && $len_b < $this->maxint) {
                return ((int)$a + (int)$b);
            }
            
            if ($a[0] == '0') {
                while ($len_a > 0 && $a[0] == '0') {
                    $a = substr($a, 1);
                    $len_a--;
                }
            }
            
            if ($b[0] == '0') {
                while ($len_b > 0 && $b[0] == '0') {
                    $b = substr($b, 1);
                    $len_b--;
                }
            }
            
            if ($a[0] == '-' || $b[0] == '-') {
                return $this->rpsub($a, $b);
            }
            
            if ((trim($a) == '' || empty($a)) && !empty($b)) {
                return $b;
            }
            
            if ((trim($b) == '' || empty($b)) && !empty($a)) {
                return $a;
            }
            
            if ((trim($a) == '' || empty($a)) && (trim($b) == '' || empty($b))) {
                return '0';
            }
            
            while ($len_a > $len_b) {
                $b = '0' . $b;
                $len_b++;
            }
            
            while ($len_b > $len_a) {
                $a = '0' . $a;
                $len_a++;
            }
            
            $q = $len_a - 1;
            $c_temp = 0;
            $s_temp = 0;
            $result = $number_string = '';
            
            while ($q >= 0) {
                $s_temp = (int)$a[$q] + (int)$b[$q] + (int)$c_temp;
                
                if ($s_temp >= 10) {
                    $c_temp = 1;
                    $str_s_temp = (string)$s_temp;
                    $result = $str_s_temp[1];
                } else {
                    $c_temp = 0;
                    $result = $s_temp;
                }
                
                $q--;
                
                $number_string .= $result;
            }
            
            if ($q < 0 && $c_temp == 1) {
                $number_string .= '1';
            }
            
            $number_string = strrev($number_string);
            $number_string_len = strlen($number_string);
            
            while ($number_string[0] == '0' && $number_string_len > 0) {
                $number_string = substr($number_string, 1);
                $number_string_len--;
            }
            
            return $number_string;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for subtracting two numbers
     *
     * @param  string $x The first number
     * @param  string $y The second number
     * @return string    The result as a string
     * @throws \Exception $e
     */
    final private function rpsub($a, $b)
    {
        try {
            settype($a, 'string');
            settype($b, 'string');
            
            $len_a = strlen($a);
            $len_b = strlen($b);
            
            if ($len_a < $this->maxint && $len_b < $this->maxint) {
                return ((int)$a - (int)$b);
            }
            
            $c = 0;
            $s = 0;
            $i = 0;
            $apad = 0;
            $bpad = 0;
            $result = '';
            $numerator   = '';
            $denominator = '';
            $sign = '';
            $sign_a = '';
            $sign_b = '';
            
            if ($a[0] == '-') {
                $sign_a = '-';
                $a = substr($a, 1);
                $len_a--;
            }
            
            if ($b[0] == '-') {
                $sign_b = '-';
                $b = substr($b, 1);
                $len_b--;
            }
            
            $larger = $this->rpcomp($a, $b);
            
            switch ($larger) {
                case 1:
                    $numerator   = $a;
                    $denominator = $b;
                    
                    if ($sign_a == '' && $sign_b == '') {
                        $sign = '';
                    }
                    
                    if ($sign_a == '' && $sign_b == '-') {
                        return $this->rpadd($a, $b);
                    }
                    
                    if ($sign_a == '-' && $sign_b == '-') {
                        $sign = '-';
                    }
                    
                    if ($sign_a == '-' && $sign_b == '') {
                        $sign = '-';
                    }
                    
                    break;
                    
                case 0:
                    $numerator   = $a;
                    $denominator = $b;
                    
                    if ($sign_a == '' && $sign_b == '') {
                        return '0';
                    }
                    
                    if ($sign_a == '' && $sign_b == '-') {
                        return $this->rpadd($a,$b);
                    }
                    
                    if ($sign_a == '-' && $sign_b == '-') {
                        $sign = '-';
                    }
                    
                    if ($sign_a == '-' && $sign_b == '') {
                        return '0';
                    }
                    
                    break;
                    
                case -1:
                    $numerator   = $b;
                    $denominator = $a;
                    
                    if ($sign_a == '' && $sign_b == '') {
                        $sign = '-';
                    }
                    
                    if ($sign_a == '' && $sign_b == '-') {
                        return $this->rpadd($a,$b);
                    }
                    
                    if ($sign_a == '-' && $sign_b == '-') {
                        $sign = '';
                    }
                    
                    if ($sign_a == '-' && $sign_b == '') {
                        $sign = '-';
                    }
                    
                    break;
                    
                default:
                    die('FATAL - unable to determine num/denom from comp() result!');
            }
            
            while (strlen($numerator) > strlen($denominator)) {
                $denominator = '0' . $denominator;
            }
            
            $q             = strlen($numerator) - 1;
            $c_temp        = 0;
            $number_string = '';
            $s_temp        = 0;
            
            while ($q >= 0) {
                $num_temp    = (int)substr($numerator, $q, 1);
                $denom_temp  = (int)substr($denominator, $q, 1);
                $borrow_temp = (int)$num_temp - (int)$c_temp;
                
                if ($borrow_temp > $denom_temp) {
                    $s_temp = (int)$borrow_temp - (int)$denom_temp;
                    $c_temp = 0;
                }
                
                if ($denom_temp > $borrow_temp) {
                    $s_temp = (10 + $borrow_temp) - $denom_temp;
                    $c_temp = 1;
                }
                
                if ($borrow_temp == $denom_temp) {
                    $s_temp = 0;
                    $c_temp = 0;
                }
                
                $q = $q - 1;
                
                $number_string = $number_string . $s_temp;
            }
            
            $result_a = strrev($number_string);
            $result_a_len = strlen($result_a);
            
            while (substr($result_a, 0, 1) === '0' && $result_a_len > 0) {
                $result_a = substr($result_a, 1);
                $result_a_len--;
            }
            
            return $sign . $result_a;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for dividing two numbers
     *
     * @param  string $x The first number
     * @param  string $y The second number
     * @return string    The result as a string
     * @throws \Exception $e
     */
    final private function rpdiv($a, $b)
    {
        try {
            settype($a, 'string');
            settype($b, 'string');
            
            if (trim($b) == '' || empty($b)) {
                return 'undefined';
            }
            
            if (trim($a) == '' || empty($a)) {
                return array('quotient' => '0', 'remainder' => '0');
            }
            
            $len_a = strlen($a);
            $len_b = strlen($b);
            
            if ($len_a < $this->maxint && $len_b < $this->maxint) {
                return array('quotient' => (int)((int)$a / (int)$b), 'remainder' => (int)((int)$a % (int)$b));
            }
            
            $c = 0;
            $s = 0;
            $i = 0;
            $rem = '';
            $result = '';
            $scale  = $len_a - $len_b;
            $larger = $this->rpcomp($a, $b);
            
            switch ($larger) {
                case 1:
                    $q   = $len_a - 1;
                    $r   = $len_b - 1;
                    $quo = $a;
                    $div = $b;
                    break;
                case 0:
                    return array('quotient' => '1', 'remainder' => '0');
                case -1:
                    return array('quotient' => '0', 'remainder' => $a);
                default:
                    return false;
            }
            
            $c_temp = 0;
            $s_temp = 0;
            $number_string = '';
            $result_r      = '';
            $quotient      = '';
            $passes = array();
            $rem = $quo;
            $qq  = 0;
            $mainbreak  = false;
            $chunk_size = $len_b;
            $c_temp = substr($quo, 0, $chunk_size);
            $position = strlen($c_temp) - 1;
            
            while (!$mainbreak >= 0 && $qq < 10) {
                $i = 0;
                $break = false;
                
                while ($this->rpcomp($c_temp, $div) < 0) {
                    $quotient = $quotient . '0';
                    $i++;
                    $c_temp = $c_temp . substr($quo, $position + $i, 1);
                }
                
                $position = $this->rpadd($position, $i);
                
                $i = 0;
                $chunk_size = 1;
                
                while (!$break) {
                    $i++;
                    $s_temp = $this->rpmul($div, $i);
                    
                    if ($this->rpcomp($s_temp, $c_temp) > 0) {
                        $i--;
                        break;
                    }
                    
                    if ($this->rpcomp($s_temp, $c_temp) == 0) {
                        break;
                    }
                    
                    if ($i > 9) {
                        break;
                    }
                }
                
                $quotient = $quotient . $i;
                
                $rem = $this->rpsub($c_temp, $this->rpmul($div, $i));
                
                if (isset($quo[$position + 1])) {
                    $c_temp = $rem . $quo[$position + 1];
                } else {
                    $mainbreak = true;
                    break;
                }
                
                $position = $this->rpadd($position, '1');
                $qq++;
            }
            
            if (trim($rem) == '') {
                $rem = '0';
            }
            
            $quotient_len = strlen($quotient);
            
            while (substr($quotient, 0, 1) === '0' && $quotient_len > 0) {
                $quotient = substr($quotient, 1);
                $quotient_len--;
            }
            
            return array('quotient' => $quotient, 'remainder' => $rem);
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for raising 'a' to the power 'b'
     *
     * @param  string $a The first number
     * @param  string $b The second number
     * @return string    The result as a string
     * @throws \Exception $e
     */
    final private function rppow($a, $b)
    {
        try {
            settype($a, 'string');
            settype($b, 'string');
            
            if (trim($b) == '' || empty($b)) {
                return '1';
            }
            
            $len_a = strlen($a);
            $len_b = strlen($b);
            
            if ($len_a < $this->maxint && $len_b < $this->maxint) {
                return pow((int)$a, (int)$b);
            }
            
            $i = 1;
            $q = 0;
            $result = $a;
            $number_string = '';
            
            while ($this->rpcomp($b, $i) > 0 && $q < 100) {
                $result = $this->rpmul($result, $a);
                $q++;
                $i++;
            }
            
            if ($q >= 100) {
                return 'overflow';
            } else {
                return $result;
            }
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for comparing two numbers.
     * Returns 1 if a>b, 0 if a=b, -1 if a<b.
     *
     * @param  string $a The first number
     * @param  string $b The second number
     * @return string    The result as a string
     * @throws \Exception $e
     */
    final private function rpcomp($a, $b)
    {
        try {
            settype($a, 'string');
            settype($b, 'string');
            
            if ((trim($a) == '' || empty($a)) && (trim($b) == '' || empty($b))) {
                return 0;
            }
            
            if (trim($a) == '' || empty($a)) {
                return -1;
            }
            
            if (trim($b) == '' || empty($b)) {
                return 1;
            }
            
            $i = 0;
            $a_size = strlen($a);
            $b_size = strlen($b);
            
            if ($a_size > $b_size) {
                return 1;
            }
            
            if ($b_size > $a_size) {
                return -1;
            }
            
            if($a == $b) {
                return 0;
            }
            
            while ($i < $a_size) {
                if ((int)$a[$i] > (int)$b[$i]) {
                    return 1;
                }
                
                if ((int)$b[$i] > (int)$a[$i]) {
                    return -1;
                }
                
                $i++;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for converting a decimal number into a binary number string
     *
     * @param  string $a The first number
     * @param  string $b The second number
     * @return string    The result as a string
     * @throws \Exception $e
     */
    final private function rpd2b($num)
    {
        try {
            if (trim($num) == '' || empty($num)) {
                return false;
            }
            
            $tmp = $num;
            $bin = '';
            
            while ($this->rpcomp($tmp, '0') > 0) {
                if ($this->rpmod($tmp, '2') == '1') {
                    $bin .= '1';
                } else {
                    $bin .= '0';
                }
                
                $tmp = $this->rpdiv($tmp, '2');
            }
            
            return strrev($bin);
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for calculating 'a' mod 'b'
     *
     * @param  string $a The first number
     * @param  string $b The second number
     * @return string    The result as a string
     */
    final private function rpmod($a, $b)
    {
        return bcmod($a, $b);
    }
    
    /**
     * Library implementation for converting a binary number string into decimal
     *
     * @param  string $a The first number
     * @param  string $b The second number
     * @return string    The result as a string
     * @throws \Exception $e
     */
    final private function rpb2d($num)
    {
        try {
            settype($num, 'string');
            
            if (trim($num) == '' || empty($num)) {
                return false;
            }
            
            $tmp  = $num;
            $dec  = '0';
            $size = strlen($num);
            $x    = '0';
            $exp  = '0';
            
            while ($size > 0) {
                $size--;
                
                $exp   = $this->rppow('2', $x);
                $digit = (int)$tmp[$size];
                $dec   = $this->rpadd($this->rpmul($digit, $exp), $dec);
                
                $x++;
            }
            
            return $dec;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for encoding a hex number string into Base-58
     *
     * @param  string $hex The hex number
     * @return string      The result as a string or error message
     * @throws \Exception $e
     */
    final public function encodeBase58($hex)
    {
        try {
            settype($hex, 'string');
            
            if (trim($hex) == '' || empty($hex)) {
                return 'Error in encodeBase58(): Blank or empty data passed to function.';
            }
            
            $hex_len = strlen($hex);
            
            if ($hex_len % 2 != 0) {
                return 'Error in encodeBase58(): Uneven number of hex characters passed. Cannot encode the string: ' . $hex;
            } else {
                $orighex = $hex;
                $chars   = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
                $hex     = $this->decodeHex($hex);
                $result  = '';
                
                switch ($this->math_type) {
                    case 'GMP':
                        $hex = gmp_init($hex);
                        
                        while (gmp_cmp($hex, $this->gmpZero) > 0) {
                            list ($hex, $rem) = gmp_div_qr($hex, $this->gmpFiveEight, GMP_ROUND_ZERO);
                            $result = $result . $chars[$rem];
                        }
                        
                        break;
                        
                    case 'BCM':
                        // TODO
                        break;
                        
                    case 'RPM':
                        // TODO
                        break;
                        
                    default:
                        die('Error in encodeBase58(): Unknown MATH_TYPE');
                }
                
                $result = strrev($result);
                
                for ($i =0 ; $i < strlen($orighex) && substr($orighex, $i, 2) == '00'; $i += 2) {
                    $result = '1' . $result;
                }
            }
            
            return $result;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for decoding a Base-58 encoded hex value
     *
     * @param  string $base58 The Base-58 number
     * @return string         The result as a string or error message
     * @throws \Exception $e
     */
    final public function decodeBase58($base58)
    {
        try {
            settype($base58, 'string');
            
            if (trim($base58) == '' || empty($string)) {
                die('Error in decodeBase58(): Value passed was not a string.');
            }
            
            $chars  = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
            $result = '0';
            $base58_len = strlen($base58);
            
            for ($i = 0; $i < $base58_len; $i++) {
                $current = strpos($chars, $base58[$i]);
                
                switch ($this->math_type) {
                    case 'GMP':
                        $result     = gmp_init($result);
                        $current    = gmp_init($current);
                        $result = gmp_mul($result, $this->gmpFiveEight);
                        $result = gmp_strval(gmp_add($result, $current));
                        
                        break;
                        
                    case 'BCM':
                        // TODO
                        break;
                        
                    case 'RPM':
                        // TODO
                        break;
                        
                    default:
                        die('Error in decodeBase58(): Unknown MATH_TYPE');
                }
            }
            
            $result = $this->encodeHex($result);
            
            for ($i = 0; $i < $base58_len && $base58[$i] == '1'; $i++) {
                $result = '00' . $result;
            }
            
            if (strlen($result) %2 != 0) {
                $result = '0' . $result;
            }
            
            return $result;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for removing an '0x' prefix from a number string if it's present
     *
     * @param  string $string The string number
     * @return string         The result as a string or error message
     * @throws \Exception $e
     */
    final private function remove0x($string)
    {
        try {
            settype($string, 'string');
            
            if (trim($string) == '' || empty($string)) {
                die('Error in remove0x(): Value passed was not a string.');
            }
            
            if (strtolower(substr($string, 0, 2)) == '0x') {
                $string = substr($string, 2);
            }
            
            return $string;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for appending an '0x' prefix to a hex number string if it's missing
     *
     * @param  string $string The string number
     * @return string         The result as a string or error message
     * @throws \Exception $e
     */
    final private function add0x($string)
    {
        try {
            settype($string, 'string');
            
            if (trim($string) == '' || empty($string)) {
                die('Error in add0x(): Value passed was not a string.');
            }
            
            if (strtolower(substr($string, 0, 2)) != '0x') {
                $string = '0x' . strtoupper($string);
            }
            
            return $string;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for decoding a hex number into decimal
     *
     * @param  string $hex The hex number
     * @return string      The result as a string or error message
     * @throws \Exception $e
     */
    final public function decodeHex($hex)
    {
        try {
            settype($hex, 'string');
            
            if (trim($hex) == '' || empty($hex)) {
                die('Error in decodeHex(): Value passed was not a string.');
            }
            
            $hex    = $this->add0x($hex);
            $chars  = '0123456789ABCDEF';
            $result = '0';
            
            for ($i=0;$i<strlen($hex);$i++) {
                $current = strpos($chars, $hex[$i]);
                
                switch ($this->math_type) {
                    case 'GMP':
                        $result  = gmp_init($result);
                        $sixteen = gmp_init('16');
                        $current = gmp_init($current);
                        $result  = gmp_mul($result, $sixteen);
                        $result  = gmp_strval(gmp_add($result, $current));
                        
                        break;
                        
                    case 'BCM':
                        // TODO
                        break;
                        
                    case 'RPM':
                        // TODO
                        break;
                        
                    default:
                        die('Error in decodeHex(): Unknown MATH_TYPE');
                }
            }
            
            return $result;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for encoding a decimal number as hex
     *
     * @param  string $dec The deciman number
     * @return string      The result as a string or error message
     * @throws \Exception $e
     */
    final public function encodeHex($dec)
    {
        try {
            settype($dec, 'string');
            
            if (trim($dec) == '' || empty($dec)) {
                die('Error in encodeHex(): Value passed was not a string.');
            }
            
            $chars  = '0123456789ABCDEF';
            $result = '';
            $i = 0;
            
            switch ($this->math_type) {
                case 'GMP':
                    $dec = gmp_init($dec);
                    
                    while (gmp_cmp($dec, $this->gmpZero) > 0) {
                        $i++;
                        $dv  = gmp_div_q($dec, $this->gmpSixteen, GMP_ROUND_ZERO);
                        $rem = gmp_strval(gmp_div_r($dec, $this->gmpSixteen, GMP_ROUND_ZERO));
                        $dec = $dv;
                        $result = $result . $chars[$rem];
                    }
                    
                    break;
                    
                case 'BCM':
                    // TODO
                    break;
                    
                case 'RPM':
                    // TODO
                    break;
                    
                default:
                    die('Error in encodeHex(): Unknown MATH_TYPE');
            }
            
            return strrev($result);
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Library implementation for converting a hex value into a byte array
     *
     * @param  string $hex The hex number
     * @return string      The result as a string or error message
     * @throws \Exception $e
     */
    final public function binconv($hex)
    {
        try {
            $dec  = self::add0x($hex);
            $byte = '';
            
            switch ($this->math_type) {
                case 'GMP':
                    $dec = gmp_init($dec);
                    
                    while (gmp_cmp($dec, $this->gmpZero) > 0) {
                        $dv   = gmp_div_q($dec, $this->gmpTwoFiveSix, GMP_ROUND_ZERO);
                        $rem  = gmp_strval(gmp_mod($dec, $this->gmpTwoFiveSix));
                        $dec  = $dv;
                        $byte = $byte . $this->digits[$rem];
                    }
                    
                    break;
                    
                case 'BCM':
                    // TODO
                    break;
                    
                case 'RPM':
                    // TODO
                    break;
                    
                default:
                    die('Error in binconv(): Unknown MATH_TYPE');
            }
            
            $byte = strrev($byte);
            
            return $byte;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Binary Calculator implementation of GMP's inverse
     * modulo function, where ax = 1(mod p).
     *
     * @param  string $number    The number to inverse mod
     * @param  string $modulus   The modulus to use
     * @return string $a         The string result
     */
    public function bc_invert($number, $modulus)
    {
        if (!$this->coprime($number, $modulus)) {
            return '0';
        }
        
        $a = '1';
        $b = '0';
        $z = '0';
        $c = '0';
        $mod = $modulus;
        $num = $number;
        
        do {
            $z = bcmod($num, $mod);
            $c = bcdiv($num, $mod);
            
            $mod = $z;
            
            $z = bcsub($a, bcmul($b, $c));
            
            $num = $mod;
            $a = $b;
            $b = $z;
            
        } while (bccomp($mod, '0') > 0);
        
        if (bccomp($a, '0') < 0) {
            $a = bcadd($a, $modulus);
        }
        
        return (string)$a;
    }
    
    /**
     * Determines if two numbers are co-prime using the Euclidean algorithm.
     *
     * @param  string $a  The first number to compare.
     * @prarm  string $b  The second number to compare.
     * @return string     The result of the comparison.
     */
    function coprime($a, $b)
    {
        $small = 0;
        $diff  = 0;
        
        while (bccomp($a, '0') > 0 && bccomp($b, '0') > 0) {
            if (bccomp($a, $b) == -1) {
                $small = $a;
                $diff  = bcmod($b, $a);
            }
            
            if (bccomp($a, $b) == 1) {
                $small = $b;
                $diff = bcmod($a, $b);
            }
            
            if (bccomp($a, $b) == 0) {
                $small = $a;
                $diff  = bcmod($b, $a);
            }
            
            $a = $small;
            $b = $diff;
        }
        
        if (bccomp($a, '1') == 0) {
            return 'true' . "\r\n";
        }
        
        return 'false' . "\r\n";
    }
}
