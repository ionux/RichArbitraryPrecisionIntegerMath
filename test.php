<?php

include('class.RAPIM.php');

$math = new RichArbitraryPrecisionIntegerMath(true);
$number_of_loops = 100;

for ($qq=0; $qq < $number_of_loops; $qq++) {
        $x = mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand();
        $y = mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand();

        $digit_length = strlen($x);

        // Division
        $myans = $math->div($x,$y);
        $gmpans = gmp_strval(gmp_div($x,$y));

        echo '<pre>Digit size: ' . $digit_length . "\r\n\r\n" . 'MY ans:  ' . $myans['quotient'];

        echo "\r\nGMP ans: " . $gmpans;

        if ($myans['quotient'] != $gmpans)
                echo " - ERROR!!!!\r\n</pre>";
        else
            	echo " - ok\r\n</pre>";

        // Multiplication
        $myans = $math->mul($x,$y);
        $gmpans = gmp_strval(gmp_mul($x,$y));

        echo '<pre>Digit size: ' . $digit_length . "\r\n\r\n" . 'MY ans:  ' . $myans;

        echo "\r\nGMP ans: " . $gmpans;

        if ($myans != $gmpans)
                echo " - ERROR!!!!\r\n</pre>";
        else
            	echo " -ok\r\n</pre>";

        // Addition
        $myans = $math->add($x,$y);
        $gmpans = gmp_strval(gmp_add($x,$y));

        echo '<pre>Digit size: ' . $digit_length . "\r\n\r\n" . 'MY ans:  ' . $myans;

        echo "\r\nGMP ans: " . $gmpans;

        if ($myans != $gmpans)
                echo " - ERROR!!!!\r\n</pre>";
        else
            	echo " -ok\r\n</pre>";

        // Subtraction
        $myans = $math->sub($x,$y);
        $gmpans = gmp_strval(gmp_sub($x,$y));

        echo '<pre>Digit size: ' . $digit_length . "\r\n\r\n" . 'MY ans:  ' . $myans;

        echo "\r\nGMP ans: " . $gmpans;

        if ($myans != $gmpans)
                echo " - ERROR!!!!\r\n</pre>";
        else
            	echo " -ok\r\n</pre>";
}
