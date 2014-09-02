<?php

/*
	Rich Arbitrary Precision Integer Mathematics Library for PHP
	(c) 2014, Rich Morgan

*/


class RichArbitraryPrecisionIntegerMath
{

	private $internal = false;
	private $a_orig_size = 0;
	private $b_orig_size = 0;
	private $a_padded_size = 0;
	private $b_padded_size = 0;
	private $subtotal = '0';
	private $a_orig = '';
	private $b_orig = '';
	private $a_now = '';
	private $b_now = '';
	private $init_called = false;
	private $math_type = '';
	private $test = false;

	public function __construct($test = false)
	{

		if($test) {
			$this->math_type = 'RPM';
			return;
		}

		if (function_exists('gmp_add')) {
			$this->math_type = 'GMP';
		} elseif (function_exists('bcadd')) {
			$this->math_type = 'BCM';
		} else {
			$this->math_type = 'RPM';
		}

	}

	public function mul($x, $y)
	{

		switch($this->math_type) {
			case 'GMP':
				return gmp_strval(gmp_mul($x, $y));
				break;
			case 'BCM':
				return bcmul($x, $y);
				break;
			case 'RPM';
				return $this->rpmul($x, $y);
				break;
			default:
				return false;
				break;
		}
	}

	public function add($x, $y)
        {

                switch($this->math_type) {
                        case 'GMP':
                                return gmp_strval(gmp_add($x, $y));
                                break;
                        case 'BCM':
                                return bcadd($x, $y);
                                break;
                        case 'RPM';
                                return $this->rpadd($x, $y);
                                break;
                        default:
                                return false;
                                break;
                }
        }

	public function sub($x, $y)
        {

                switch($this->math_type) {
                        case 'GMP':
                                return gmp_strval(gmp_sub($x, $y));
                                break;
                        case 'BCM':
                                return bcsub($x, $y);
                                break;
                        case 'RPM';
                                return $this->rpsub($x, $y);
                                break;
                        default:
                                return false;
                                break;
                }
        }

	public function div($x, $y)
        {

                switch($this->math_type) {
                        case 'GMP':
                                return gmp_strval(gmp_div($x, $y));
                                break;
                        case 'BCM':
                                return bcdiv($x, $y);
                                break;
                        case 'RPM';
                                return $this->rpdiv($x, $y);
                                break;
                        default:
                                return false;
                                break;
                }
        }

	public function mod($x, $y)
        {

                switch($this->math_type) {
                        case 'GMP':
                                return gmp_strval(gmp_mod($x, $y));
                                break;
                        case 'BCM':
                                return bcmod($x, $y);
                                break;
                        case 'RPM';
                                return $this->rpmod($x, $y);
                                break;
                        default:
                                return false;
                                break;
                }
        }

	public function invmod($x, $y)
        {

                switch($this->math_type) {
                        case 'GMP':
                                return gmp_strval(gmp_invert($x, $y));
                                break;
                        case 'BCM':
                                return false;
                                break;
                        case 'RPM';
                                return $this->rpinvmod($x, $y);
                                break;
                        default:
                                return false;
                                break;
                }
        }

	public function comp($x, $y)
        {

                switch($this->math_type) {
                        case 'GMP':
                                return gmp_strval(gmp_comp($x, $y));
                                break;
                        case 'BCM':
                                return bccomp($x, $y);
                                break;
                        case 'RPM';
                                return $this->rpcomp($x, $y);
                                break;
                        default:
                                return false;
                                break;
                }
        }

	final private function rpmul($x, $y)
	{
		settype($a, 'string');
		settype($b, 'string');

		if (empty($x) || empty($y)) {
			return '0';
		}

		if (PHP_INT_SIZE > 4) {
			$maxint = 10;
		}  else {
			$maxint = 5;
		}

		$x_size = strlen($x);
		$y_size = strlen($y);
		$chunk  = 0;

		if ($x_size > $y_size) {
			$chunk = $x_size;
		} else {
			$chunk = $y_size;
		}

		if ($chunk < $maxint) {
			$small_ans = (int)((int)$x * (int)$y);
			return $small_ans;
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

		$z = $this->rpadd($a, $this->rpadd($d, $e));

		return $z;
	}

	final private function rpadd($a, $b)
	{

		settype($a, 'string');
		settype($b, 'string');

		if (empty($a) && !empty($b)) {
			return $b;
		}

		if (empty($b) && !empty($a)) {
			return $a;
		}

		if (empty($a) && empty($b)) {
			return '0';
		}

		$len_a = strlen($a);
		$len_b = strlen($b);

		if (PHP_INT_SIZE > 4) {
                        $maxint = 10;
                }  else {
                        $maxint = 5;
                }
		
		if ($len_a < $maxint && $len_b < $maxint) {
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

		if (empty($a) && !empty($b)) {
			return $b;
		}

                if (empty($b) && !empty($a)) {
			return $a;
		}

                if (empty($a) && empty($b)) {
			return '0';
		}

		while (strlen($a) > strlen($b)) {
			$b = '0' . $b;
		}

		while (strlen($b) > strlen($a)) {
			$a = '0' . $a;
		}

		$q = strlen($a) - 1;
		$c_temp = $s_temp = 0;
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

		while ($number_string[0] == '0' && strlen($number_string) > 0) {
			$number_string = substr($number_string, 1, strlen($number_string));
		}

		return $number_string;
	}


	final private function rpsub($a, $b)
	{

		settype($a, 'string');
                settype($b, 'string');

		$len_a = strlen($a);
                $len_b = strlen($b);

                if (PHP_INT_SIZE > 4) {
                        $maxint = 10;
                }  else {
                        $maxint = 5;
                }

                if ($len_a < $maxint && $len_b < $maxint) {
                        return ((int)$a - (int)$b);
                }


		$c = 0;
		$s = 0;
		$i = 0;
		$apad = 0;
		$bpad = 0;
		$result = '';
		$a_size = strlen($a);
		$b_size = strlen($b);
		$numerator   = '';
		$denominator = '';
		$sign = '';
		$sign_a = '';
		$sign_b = '';

		if ($a[0] == '-') {
                        $sign_a = '-';
			$a = substr($a, 1, strlen($a));
			$a_size = strlen($a);
                }

                if ($b[0] == '-') {
                        $sign_b = '-';
			$b = substr($b, 1, strlen($b));
	                $b_size = strlen($b);
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
				break;
		}

		while (strlen($numerator) > strlen($denominator)) {
			$denominator = '0' . $denominator;
		}

		$q             = strlen($numerator) - 1;
		$c_temp        = 0;
		$number_string = '';
		$s_temp        = 0;

		while ($q >= 0) {

			$num_temp    = (int)substr($numerator,   $q, 1);
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

		while (substr($result_a, 0, 1) === '0' && strlen($result_a) > 0) {
			$result_a = substr($result_a, 1, strlen($result_a));
		}

		return $sign . $result_a;
	}


	final public function rpdiv($a, $b)
	{

		settype($a, 'string');
                settype($b, 'string');

                $len_a = strlen($a);
                $len_b = strlen($b);

                if (PHP_INT_SIZE > 4) {
                        $maxint = 10;
                }  else {
                        $maxint = 5;
                }

                if ($len_a < $maxint && $len_b < $maxint) {
                        return ((int)$a / (int)$b);
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
				break;
			case -1:
				return array('quotient' => '0', 'remainder' => $a);
				break;
			default:
				return false;
				break;
		}

		$c_temp        = 0;
		$number_string = '';
		$s_temp        = 0;
		$result_r      = '';
		$passes        = array();
		$rem           = $quo;
		$qq            = 0;
		$mainbreak     = false;
		$chunk_size    = $b_size;
		$quotient      = '';

		$c_temp = substr($quo, 0, $chunk_size);

		$position = strlen($c_temp) - 1;

		while (!$mainbreak >= 0 && $qq < 10) {
			$i = 0;
			$break = false;

			while ($this->rpcomp($c_temp, $div) < 0) {
				//echo "\r\n  - my chunk of $c_temp is not large enough. ";

				$quotient = $quotient . '0';

				//echo "\r\n  - added a '0' to my answer so it's now $quotient ";

				$i++;
				$c_temp = $c_temp . substr($quo, $position + $i, 1);

				//echo "\r\n  - grabbing the next value which should be " . substr($quo,$position+$i,1);
			}

			//echo "\r\n\r\n using $c_temp as my working value now. \r\n";

			$position = $this->rpadd($position, $i);

			$i = 0;
			$chunk_size = 1;

			while (!$break) {
				$i++;

				$s_temp = $this->rpmul($div, $i);

				if ($this->rpcomp($s_temp, $c_temp) > 0) {
					//echo "\r\n  - $s_temp is larger than $c_temp so i am reducing $i by 1 ";

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

		while (substr($quotient, 0, 1) === '0' && strlen($quotient) > 0) {
			$quotient = substr($quotient, 1, strlen($quotient));
		}

		return array('quotient' => $quotient, 'remainder' => $rem);
	}


	final private function rppow($a, $b)
	{

		$c = 0;
		$s = 0;
		$i = 1;
		$rem = '';
		$result = '';
		$a_size = strlen($a);
		$b_size = strlen($b);
		$scale  = $a_size - $b_size;
		$c_temp        = 0;
		$number_string = '';
		$s_temp        = 0;
		$result_r      = '';
		$rem           = $a;

		$qq=0;

		while($this->rpcomp($b, $i) > 0 && $qq < 100) {
			$i = $this->rpadd($i, '1');
			$rem = $this->rpmul($rem, $a);
			$qq++;
		}

		return $rem;
	}


	final private function rpcomp($a, $b)
	{

		settype($a, 'string');
		settype($b, 'string');

		$result = $i = 0;
		$a_size = strlen($a);
		$b_size = strlen($b);

		if ($a_size > $b_size) {
			return 1;
		}

		if ($b_size > $a_size) {
			return -1;
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

		return $result;
	}


	final private function rpd2b($num)
	{

		try {
			if (empty($num)) {
				return false;
			}

			$tmp = $num;
			$bin = '';

			while ($this->rpcomp($tmp, '0') > 0) {
				if ($this->rpmod($tmp, '2') == '1' ) {
					$bin .= '1';
				} else {
					$bin .= '0';
				}

				$tmp = $this->rpdiv($tmp, '2');
			}

			return strrev($bin);

		} catch (Exception $e) {
			return 'Error in rpd2b: ' . $e->mesage;
		}

	}


	final private function rpb2d($num)
	{

		try {
			if (empty($num)) {
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
			return 'Error in rpb2d: ' . $e;
		}

	}

}
