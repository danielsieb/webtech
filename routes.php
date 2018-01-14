<?php
  function call($action) {
    // require the controller
    require_once('controllers/view_controller.php');
    require_once('models/bild.php');
    require_once('models/suche.php');

    $controller = new ViewController();

    $controller->{ $action }();
  }

  // just a list of the controllers we have and their actions
  // we consider those "allowed" values
  $actions = array('home', 'search', 'error');

  // check that the requested controller and action are both allowed
  // if someone tries to access something else he will be redirected to the error action of the pages controller
  if (in_array($action, $actions)) {
    call($action);
  } else {
    call('error');
  }
?>