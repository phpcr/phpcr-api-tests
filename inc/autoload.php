<?php
/* 
 * An autoload array to enable autoloading in sf2 (jackrabbit_importexport is used
 * in CoreBundle). Further classes can be added if needed
 * TODO: Temporary hack, remove once importXML is implemented and use session->importXML
 */
return array(
  'jackrabbit_importexport' => __DIR__.'/importexport.php',
);
