<?php

//very powerful
//pass in an array (I created a static one) with say field names and field values from a database
//class variables with the same names as the fields are populated by the foreach. no manual assignments!

class test
{
    private $name, $email;

    function __construct()
    {
        $this->setNames();
    }

    function setNames(){
        $arr = array("name" => "bob", "email" => "dd@gg.com");

        foreach($arr as $key => $val)
        {
            $this->$key = $val;
            //$this->varName(compact($key));
        }

        echo $this->name;
        echo $this->email;
    }
}

$my_class = new test();
//var_dump( $my_class );
