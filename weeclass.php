<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of wee-test-class
 *
 * @author Wee
 */
class WEECLASS
{

//  public static $bar = "choclate bar";


    public $spl1;

    public function init()
    {
        $this->spl1 = new weespl();
        $object1 = new weeclass2(100);
        $object2 = new weeclass2(200);
        $this->spl1->insert($object1);
        $this->spl1->insert($object2);

    }

    public function mod()
    {
        $object3 = new weeclass2(300);
        $this->spl1->insert($object3);
    }

    public function mod2()
    {
        /* @var $this->spl1 weespl */
        $object = $this->spl1->current();
        /* @var $object weeclass2*/
        $object->val = 999;
    }

    public function __clone()
    {
        $this->spl1 = clone $this->spl1;
    }

}

//WEECLASS::init();
?>
