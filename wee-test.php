<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
spl_autoload_register(
        function ($class)
        {
            include './' . $class . '.php';
        });

//$var1 = new SplMaxHeap();
//for ($i = 10; $i < 110; $i = $i + 10){
//    $var1->insert($i);
//}
//
//for ($i = 10; $i < 110; $i = $i + 10){
//    $current = $var1->current();
//    echo "<br> $current";
//}
echo memory_get_usage()."\n"; // display 627760
$array = array();
$array[] = 1;
$array[] = 2;
echo memory_get_usage()."\n"; // 824744, so 196984 for $array
unset( $array );

echo memory_get_usage()."\n"; // 627792
$spl= new SplFixedArray(14);
$spl[0] = 1;
$spl[1] = 2;
echo memory_get_usage()."\n"; //644944, so just 17151 for $spl !!!

//$var1 = new WEECLASS();
//$var1->init();
//$var2 = clone $var1;
//
//echo "<br>After Init: <br>";
//echo "var 1:<br>";
//print_r($var1);

//echo "var 2:<br> ";
//print_r($var2);
//
//$var1->mod();
//echo "<br>After Mod: <br>";
//echo "var 1:<br>";
//print_r($var1);
//echo "var 2:<br> ";
//print_r($var2);
//
//$var1->mod2();
//echo "<br>After Mod 2: <br>";
//echo "var 1:<br>";
//print_r($var1);
//echo "var 2:<br> ";
//print_r($var2);

?>
