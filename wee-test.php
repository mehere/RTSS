<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//include './weeclass.php';

//$tester = new WEECLASS();
//echo $tester->test();
$arr = array('a','b','c','d');
$cur = current($arr);
echo $cur;
unset($arr[key($arr)]);
$cur = current($arr);
echo $cur;
unset($arr[key($arr)]);
$cur = current($arr);
echo $cur;
unset($arr[key($arr)]);
$cur = current($arr);
echo $cur;
unset($arr[key($arr)]);
$cur = current($arr);
echo $cur;
unset($arr[key($arr)]);

////print_r($cur);
//if (empty($cur)){echo "abc";}
//$type = "Norm";
//echo "construct{$type}Teacher";

//$arr1 = array();
//$arr2 = array("b1"=>1,"b2"=>2,"b3"=>3);
//$arr3 = array_merge($arr1,$arr2);
//print_r($arr3);
//$noElements = count($arr1);
//echo $noElements;
?>
