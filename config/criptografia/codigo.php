<?php
class informacoes{
  function makeSecretCode(){
    $secreto = mt_rand();
    $_SESSION['secret'] = $secreto;
    return $secreto;
  }
  function getSecretCode(){
    return $_SESSION['secret'];
  }
}
?>
