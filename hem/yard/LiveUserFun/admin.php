<?php
require_once ('conf.php');



if(!$usr->isLoggedIn()) 
  {
    $tpl =& new HTML_Template_IT('./');
    $tpl->loadTemplatefile('login_form.tpl', true, false);

    $tpl->setVariable('SELF_URL', $_SERVER['SCRIPT_NAME']);
    $login_form = $tpl->get();
  }
 else
   {
    echo "I'm in!<br/>";    
    echo "<a href=\"".$_SERVER['SCRIPT_NAME']."?logout=1\">Logout</a>";
   }


if(!empty($login_form)) echo $login_form;



?>