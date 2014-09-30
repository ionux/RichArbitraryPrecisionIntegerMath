<?php

/*
    Rich Arbitrary Precision Integer Mathematics Library for PHP
    (c) 2014, Rich Morgan

    This class is in ALPHA status and may not work correctly. Do not
    use in a production environment!

*/


class RichArbitraryPrecisionIntegerMath
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

    /* public constructor method to initialize important class properties */
    public function __construct($test = false)
    {
        if (PHP_INT_SIZE > 4) {
            $this->maxint = 10;
        } else {
            $this->maxint = 5;
        }

        if ($test) {
            $this->math_type = 'RPM';
            return true;
        }

        if (function_exists('gmp_add')) {
            $this->math_type = 'GMP';
            return true;
        } else if (function_exists('bcadd')) {
            $this->math_type = 'BCM';
            return true;
        } else {
            $this->math_type = 'RPM';
            return true;
        }
    }

    /* public interface for multiplying two numbers */
    public function mul($x, $y)
    {

        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_mul($x, $y));
            case 'BCM':
                return bcmul($x, $y);
            case 'RPM';
                return $this->rpmul($x, $y);
            default:
                return false;
        }

    }

    /* public interface for adding two numbers */
    public function add($x, $y)
    {

        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_add($x, $y));
            case 'BCM':
                return bcadd($x, $y);
            case 'RPM';
                return $this->rpadd($x, $y);
            default:
                return false;
        }

    }

    /* public interface for subtracting two numbers */
    public function sub($x, $y)
    {

        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_sub($x, $y));
            case 'BCM':
                return bcsub($x, $y);
            case 'RPM';
                return $this->rpsub($x, $y);
            default:
                return false;
            }

    }

    /* public interface for dividing two numbers */
    public function div($x, $y)
    {

        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_div_q($x, $y, GMP_ROUND_ZERO));
            case 'BCM':
                return bcdiv($x, $y);
            case 'RPM';
                return $this->rpdiv($x, $y);
            default:
                return false;
        }

    }

    /* public interface for calculating 'x' modulo 'b' */
    public function mod($x, $y)
    {

        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_mod($x, $y));
            case 'BCM':
                return bcmod($x, $y);
            case 'RPM';
                return $this->rpmod($x, $y);
            default:
                return false;
        }

    }

    /* public interface for calculating the inverse modulo */
    public function invmod($x, $y)
    {

        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_invert($x, $y));
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

    /* public interface for comparing two numbers */
    public function comp($x, $y)
    {

        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_cmp($x, $y));
            case 'BCM':
                return bccomp($x, $y);
            case 'RPM';
                return $this->rpcomp($x, $y);
            default:
                return false;
        }

    }

    /* public interface for raising a number to a power */
    public function power($x, $y)
    {

        switch ($this->math_type) {
            case 'GMP':
                return gmp_strval(gmp_pow($x, $y));
            case 'BCM':
                return bcpow($x, $y);
            case 'RPM';
                return $this->rppow($x, $y);
            default:
                return false;
        }

    }

    /* multiplies a number 'a' by a number 'b' */
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

        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    /* adds a number 'a' by a number 'b' */
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

        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    /* subtracts a number 'a' by a number 'b' */
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

        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    /* divides a number 'a' by a number 'b' */
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

        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    /* raises a number 'a' to a power 'b' */
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

        } catch (Exception $e) {
            return 'Error in rppow: ' . $e->getMessage();
        }

    }

    /* compares two numbers and returns 1 if a>b, 0 if a=b, -1 if a<b */
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

        } catch (Exception $e) {
            return 'Error in rpcomp: ' . $e->getMessage();
        }

    }

    /* convers a decimal number into a binary number string */
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

        } catch (Exception $e) {
            return 'Error in rpd2b: ' . $e->getMessage();
        }

    }

    final private function rpmod($a, $b)
    {
        return bcmod($a, $b);
    }
    /* convers a binary number string into decimal */
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

        } catch (Exception $e) {
            return 'Error in rpb2d(): ' . $e->getMessage();
        }

    }

    /* encodes a hex number string into Base-58 */
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
                $hex     = gmp_init($this->decodeHex($hex));
                $result  = '';

                switch ($this->math_type) {
                    case 'GMP':
                        while (gmp_cmp($hex, 0) > 0) {
                            $dv     = gmp_div_q($hex, '58', GMP_ROUND_ZERO);
                            $rem    = gmp_strval(gmp_div_r($hex, '58', GMP_ROUND_ZERO));
                            $hex    = $dv;
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

                for ($i=0; $i < strlen($orighex) && substr($orighex, $i, 2) == '00'; $i += 2) {
                    $result = '1' . $result;
                }
            }

            return $result;

        } catch (Exception $e) {
            return 'Error in encodeBase58(): ' . $e->getMessage();
        }

    }

    /* decodes a Base-58 encoded value */
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
                        $result = gmp_mul($result, '58');
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

        } catch (Exception $e) {
            return 'Error in decodeBase58(): ' . $e->getMessage();
        }

    }

    /*  removes an '0x' from a number string if it's present */
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

        } catch (Exception $e) {
            return 'Error in remove0x(): ' . $e->getMessage();
        }

    }

    /* appends an '0x' to a hex number string if it's missing */
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

        } catch (Exception $e) {
            return 'Error in remove0x(): ' . $e->getMessage();
        }

    }

    /* decodes a hex number into decimal */
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
                        $result = gmp_mul($result, '16');
                        $result = gmp_strval(gmp_add($result, $current));
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

        } catch (Exception $e) {
            return 'Error in decodeHex(): ' . $e->getMessage();
        }

    }

    /* encodes a decimal number as hex */
    final public function encodeHex($dec)
    {

        try {

            settype($dec, 'string');

            if (trim($dec) == '' || empty($dec)) {
                die('Error in encodeHex(): Value passed was not a string.');
            }

            $chars  = '0123456789ABCDEF';
            $result = '';
            $dec = gmp_init($dec);

            $i = 0;

            switch ($this->math_type) {
                case 'GMP':
                    while (gmp_cmp($dec, 0) > 0) {
                        $i++;
                        $dv  = gmp_div_q($dec, '16', GMP_ROUND_ZERO);
                        $rem = gmp_strval(gmp_div_r($dec, '16', GMP_ROUND_ZERO));
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

        } catch (Exception $e) {
            return 'Error in encodeHex(): ' . $e->getMessage();
        }

    }

    /* converts hex value into byte array */
    final public function binconv($hex) {

        digits = array();

        try {

            for ($x=0; $x<256; $x++) {
                $digits[$x] = chr($x);
            }

            $dec  = self::add0x($hex);

            $byte = '';

            switch ($this->math_type) {
                case 'GMP':
                    while (gmp_cmp($dec, '0') > 0) {
                        $dv   = gmp_div_q($dec, '256', GMP_ROUND_ZERO);
                        $rem  = gmp_strval(gmp_mod($dec, '256'));
                        $dec  = $dv;
                        $byte = $byte . $digits[$rem];
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
     * @param  string $num The number to inverse modulo.
     * @param  string $mod The modulus.
     * @return string $a   The result.
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
     * Determines if two numbers are co-prime
     * using the Euclidean algorithm.
     * 
     * @param string $a The first number to compare.
     * @prarm string $b The second number to compare.
     * @return bool     The result of the determination.
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
