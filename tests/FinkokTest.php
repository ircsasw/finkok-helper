<?php
namespace ircsasw\finkok;

/**
*  Corresponding Class to test Finkok class
*
*  @author Arturo Ramos
*/
class FinkokTest extends PHPUnit_Framework_TestCase
{

    /**
    * Just check if the Finkok has no syntax error
    *
    * This is just a simple check to make sure your library has no syntax error. This helps you troubleshoot
    * any typo before you even use this library in a real project.
    *
    */
    public function testIsThereAnySyntaxError() {
        $var = new ircsasw\finkok\Finkok;
        $this->assertTrue(is_object($var));
        unset($var);
    }
}
