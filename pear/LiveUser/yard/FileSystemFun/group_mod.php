<?php

$options = array (
		  'filename' => '/Library/WebServer/Documents/martin/hem/yard/LiveUserAdminFun/right.constants.php'
		   );

if( is_writeable($options['filename']) )
  {
    echo "File ".$options['filename']." Writeable<br/>";
  }
 else
   {
     echo "File ".$options['filename']." not Writeable<br/>";
   }

if(chmod($options['filename'], '0777'))
  {
    if( is_writeable($options['filename']) )
      {
	echo "File ".$options['filename']." Writeable<br/>";
      }
    else
      {
	echo "File ".$options['filename']." not Writeable<br/>";
      }
  }
else
  {
    echo "Could not change File Permissions of ".$options['filename'];
  }


echo '<pre>$SERVER:';
print_r($_SERVER);
echo '</pre>';
?>