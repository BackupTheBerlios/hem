<?php
define("THEME_HANDLER_LOADED", TRUE);

class Theme 
{

  function Theme(& $dbh)
  {
    global $DB_PREFIX;

    $this->dbh_ = $dbh;
    $this->themes_tbl_ = $DB_PREFIX . "themes";

    $this->dbh_ = $dbh;

  }

  function addTheme($id, $css_file_location, $name)
  {
    if(!empty($css_file_location) && !empty($name) )
      {
	$query = "INSERT INTO $this->themes_tbl_ ".
	  "(theme_id, css_file_name, theme_name ) ".
	  "VALUES ".
	  "('$id', '$css_file_location', '$name' )";
	$result = $this->dbh_->query($query);
	echo "Test me! class.Theme.php:18";
	if($result == TRUE) return TRUE;
	else return FALSE;
      }
    return FALSE; 
    
  }


  function delTheme($theme_id)
  {
    // TODO: Write me please!

  }


  function getThemeCSS($theme_id)
  {
    if($theme_id != null)
      {
	$query = "SELECT css_file_name FROM $this->themes_tbl_ WHERE theme_id = '$theme_id'";
	$result = $this->dbh_->query($query);
	
	
	if( ($result != null) && ($result->numRows() == 1) )
	  {
	    $row = $result->fetchRow();
	    return $row->css_file_name;
	  }
	echo $query;
      }
    

  }

}




?>