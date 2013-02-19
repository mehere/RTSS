<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of weespl
 *
 * @author Wee
 */
class weespl extends SplHeap{
//  public static $bar = "choclate bar";

  public function compare($value1, $value2)
  {
      return ($value1 < $value2) ? 1 : -1;

  }





}
?>
