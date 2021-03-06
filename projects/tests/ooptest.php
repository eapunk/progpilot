<?php

$framework->add_testbasis("./tests/oop/simple1.php");
$framework->add_output("./tests/oop/simple1.php", array("\$instance1->boum2"));
$framework->add_output("./tests/oop/simple1.php", array("12"));
$framework->add_output("./tests/oop/simple1.php", "xss");

$framework->add_testbasis("./tests/oop/simple2.php");
$framework->add_output("./tests/oop/simple2.php", array("\$instance1->boum2"));
$framework->add_output("./tests/oop/simple2.php", array("11"));
$framework->add_output("./tests/oop/simple2.php", "xss");

$framework->add_testbasis("./tests/oop/simple3.php");
$framework->add_output("./tests/oop/simple3.php", array("\$_GET[\"p\"]"));
$framework->add_output("./tests/oop/simple3.php", array("9"));
$framework->add_output("./tests/oop/simple3.php", "xss");

$framework->add_testbasis("./tests/oop/simple4.php");
$framework->add_output("./tests/oop/simple4.php", array("\$_GET[\"p\"]"));
$framework->add_output("./tests/oop/simple4.php", array("10"));
$framework->add_output("./tests/oop/simple4.php", "xss");

$framework->add_testbasis("./tests/oop/simple5.php");
$framework->add_output("./tests/oop/simple5.php", array("\$instance->boum1"));
$framework->add_output("./tests/oop/simple5.php", array("25"));
$framework->add_output("./tests/oop/simple5.php", "xss");

$framework->add_testbasis("./tests/oop/simple6.php");
$framework->add_output("./tests/oop/simple6.php", array("\$instance->boum1"));
$framework->add_output("./tests/oop/simple6.php", array("24"));
$framework->add_output("./tests/oop/simple6.php", "xss");

$framework->add_testbasis("./tests/oop/simple7.php");
$framework->add_output("./tests/oop/simple7.php", array("\$instance->boum1"));
$framework->add_output("./tests/oop/simple7.php", array("15"));
$framework->add_output("./tests/oop/simple7.php", "xss");

$framework->add_testbasis("./tests/oop/simple8.php");
$framework->add_output("./tests/oop/simple8.php", array("\$instance1->boum2"));
$framework->add_output("./tests/oop/simple8.php", array("16"));
$framework->add_output("./tests/oop/simple8.php", "xss");

$framework->add_testbasis("./tests/oop/simple9.php");
$framework->add_output("./tests/oop/simple9.php", array("\$boum1"));
$framework->add_output("./tests/oop/simple9.php", array("7"));
$framework->add_output("./tests/oop/simple9.php", "xss");

$framework->add_testbasis("./tests/oop/simple10.php");
$framework->add_output("./tests/oop/simple10.php", array("\$instance1->boum1"));
$framework->add_output("./tests/oop/simple10.php", array("5"));
$framework->add_output("./tests/oop/simple10.php", "xss");

$framework->add_testbasis("./tests/oop/simple11.php");
$framework->add_output("./tests/oop/simple11.php", array("\$instance1->boum1"));
$framework->add_output("./tests/oop/simple11.php", array("18"));
$framework->add_output("./tests/oop/simple11.php", "xss");

$framework->add_testbasis("./tests/oop/simple12.php");
$framework->add_output("./tests/oop/simple12.php", array("\$instance1->boum1"));
$framework->add_output("./tests/oop/simple12.php", array("9"));
$framework->add_output("./tests/oop/simple12.php", "xss");

$framework->add_testbasis("./tests/oop/simple13.php");
$framework->add_output("./tests/oop/simple13.php", array("\$instance1->boum1"));
$framework->add_output("./tests/oop/simple13.php", array("14"));
$framework->add_output("./tests/oop/simple13.php", "xss");

$framework->add_testbasis("./tests/oop/simple14.php");
$framework->add_output("./tests/oop/simple14.php", array("\$instance1->boum2"));
$framework->add_output("./tests/oop/simple14.php", array("11"));
$framework->add_output("./tests/oop/simple14.php", "xss");

$framework->add_testbasis("./tests/oop/simple15.php");
$framework->add_output("./tests/oop/simple15.php", array("\$this->boum1"));
$framework->add_output("./tests/oop/simple15.php", array("5"));
$framework->add_output("./tests/oop/simple15.php", "xss");

$framework->add_testbasis("./tests/oop/simple16.php");
$framework->add_output("./tests/oop/simple16.php", array("\$instance1->boum1"));
$framework->add_output("./tests/oop/simple16.php", array("5"));
$framework->add_output("./tests/oop/simple16.php", "xss");

$framework->add_testbasis("./tests/oop/simple17.php");
$framework->add_output("./tests/oop/simple17.php", array("\$this->boum1[0]"));
$framework->add_output("./tests/oop/simple17.php", array("9"));
$framework->add_output("./tests/oop/simple17.php", "xss");

$framework->add_testbasis("./tests/oop/simple18.php");
$framework->add_output("./tests/oop/simple18.php", array("\$instance1->boum1"));
$framework->add_output("./tests/oop/simple18.php", array("15"));
$framework->add_output("./tests/oop/simple18.php", "xss");

$framework->add_testbasis("./tests/oop/simple19.php");
$framework->add_output("./tests/oop/simple19.php", array("\$testc1->object1->object2"));
$framework->add_output("./tests/oop/simple19.php", array("29"));
$framework->add_output("./tests/oop/simple19.php", "xss");
$framework->add_output("./tests/oop/simple19.php", array("\$newtestc1->object1"));
$framework->add_output("./tests/oop/simple19.php", array("37"));
$framework->add_output("./tests/oop/simple19.php", "xss");
$framework->add_output("./tests/oop/simple19.php", array("\$newsettestc1->object1"));
$framework->add_output("./tests/oop/simple19.php", array("7"));
$framework->add_output("./tests/oop/simple19.php", "xss");

$framework->add_testbasis("./tests/oop/simple20.php");
$framework->add_output("./tests/oop/simple20.php", array("\$this->member2"));
$framework->add_output("./tests/oop/simple20.php", array("7"));
$framework->add_output("./tests/oop/simple20.php", "xss");

$framework->add_testbasis("./tests/oop/simple21.php");
$framework->add_output("./tests/oop/simple21.php", array("\$query"));
$framework->add_output("./tests/oop/simple21.php", array("23"));
$framework->add_output("./tests/oop/simple21.php", "xml_injection");
$framework->add_output("./tests/oop/simple21.php", array("\$res"));
$framework->add_output("./tests/oop/simple21.php", array("28"));
$framework->add_output("./tests/oop/simple21.php", "xss");

$framework->add_testbasis("./tests/oop/simple22.php");
$framework->add_output("./tests/oop/simple22.php", array("\$a->data"));
$framework->add_output("./tests/oop/simple22.php", array("11"));
$framework->add_output("./tests/oop/simple22.php", "xss");
$framework->add_output("./tests/oop/simple22.php", array("\$this->data"));
$framework->add_output("./tests/oop/simple22.php", array("6"));
$framework->add_output("./tests/oop/simple22.php", "xss");
$framework->add_output("./tests/oop/simple22.php", array("\$a->data"));
$framework->add_output("./tests/oop/simple22.php", array("11"));
$framework->add_output("./tests/oop/simple22.php", "xss");

$framework->add_testbasis("./tests/oop/simple23/a.php");
$framework->add_output("./tests/oop/simple23/a.php", array("\$sql"));
$framework->add_output("./tests/oop/simple23/a.php", array("10"));
$framework->add_output("./tests/oop/simple23/a.php", "sql_injection");
