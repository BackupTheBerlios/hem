<?php
define('TEMPLATE_HANDLER_LOADED', TRUE);

// Include the API we're abstracting here
require_once('HTML/Template/IT.php');

class TemplateHandler
{
  function TemplateHandler($template_dir = null)
  {
    $this->tpl_ = new HTML_Template_IT($template_dir);
  }

  function loadTemplateFile($template_file = null, $remove_unknown_vars = FALSE, $remove_empty_blocks = FALSE)
  {
    return $this->tpl_->loadTemplatefile($template_file, $remove_unknown_vars, $remove_empty_blocks);
  }

  function setVar($place_holder = null, $value = null)
  {
    $this->tpl_->setVariable($place_holder, $value);
  }

  function show($block = null)
  {
    if($block == null)
      $this->tpl_->show();
    else
      $this->tpl_->show($block);

  }

  function get($block = null)
  {
    if($block == null)
      return $this->tpl_->get();
    else
      return $this->tpl_->get($block);
  }

  function parse($block = "__global", $flag_recursion = FALSE)
  {
    $this->tpl_->parse($block, $flag_recursion);
  }

  function parseCurrentBlock()
  {
    $this->tpl_->parseCurrentBlock();
  }

  function setCurrentBlock($block = "__global")
  {
    $this->tpl_->setCurrentBlock($block);
  }



}



?>