<?

/*
 * A simple Form sanity Validator
 *
 * by Martin Loitzl
 * 2004-06-09
 *
 * checks associative Arrays from forms for sanity
 * and returns an associative array with the original
 * data and error codes.
 *
 * supported:
 * text, number, date, email, 
 *
 * Error Codes:
 * NOT_GIVEN     field required, but variable empty
 * NOT_A_NUMBER  field is not a number
 * NOT_A_TEXT    field is not a text
 * NOT_A_DATE    -"-
 * ... see supported fields
 *
 */

/*
-- Example Arrays --
Fieldname --> Fieldtype
$field_type = array (
         "name" => "text",
	 "date" => "date",
	 "amount" => "currency",
	 "email" => "email");

Fieldname --> Fielddata
$field_data = array (
         "name" => "Martin",
	 "date" => "01.03.2004",
	 "amount" => "200,--",
	 "email" => "martin@loitzl.com");

Fieldname --> Importance
$field_required = array (
          "name" => "TRUE",
	  "date" => "FALSE", <-- FALSE can be ommited
	  "email" => "TRUE");
*/

define('FORM_VALIDATOR_LOADED', TRUE);

// TODO: Implement validation functions!!!


class FormValidator
{


  var $version_ = '1.0.0';


  function FormValidator($params)
  {
  }

  

  function checkFields( $field_type = null, $field_data = null , $field_required = null )
  {

    $field_error = array();

    while(list($field, $func) = each($field_type))
      {

	$ok = $this->$func($field_data[$field]);

	$not_given = $this->isGiven($field_data[$field], $field_required[$field]);



	if( $not_given == FALSE )
	  {
	    $field_error[$field] = "NOT_GIVEN";
	  }
	else if ( $ok == FALSE )
	  {
	    $field_error[$field] = "NOT_A_".strtoupper($func);
	  }
      }	

    return $field_error;
  }


  function isGiven($data, $required)
  {
    if($required == "TRUE" && empty($data))
      {
	return FALSE;
      }
    else
      {
	return TRUE;
      }
  }

  function text($data)
  {
    return !is_numeric($data);
  }

  function number($data)
  {
    return is_numeric($data);
  }


  function date($data)
  {
    return TRUE;
  }



  function apiVersion()
  {
    return $this->version_;
  }

  function checkRequiredFields()
  {

  }
}

?>