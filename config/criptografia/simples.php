<?php
class simplesCriptografia{

  function ordutf8($string, &$offset, $chave) {
      $code = ord(substr($string, $offset,1));
      if ($code >= 128) {        //otherwise 0xxxxxxx
          if ($code < 224) $bytesnumber = 2;                //110xxxxx
          else if ($code < 240) $bytesnumber = 3;        //1110xxxx
          else if ($code < 248) $bytesnumber = 4;    //11110xxx
          $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
          for ($i = 2; $i <= $bytesnumber; $i++) {
              $offset ++;
              $code2 = ord(substr($string, $offset, 1)) - 128;        //10xxxxxx
              $codetemp = $codetemp*64 + $code2;
          }
          $code = $codetemp;
      }
      $offset += 1;
      if ($offset >= strlen($string)) $offset = -1;
      return ($code) * $chave;
  }

  function codificarTexto($chave,$texto){
    $offset = 0;
    $encriptado = [];
    while ($offset >= 0) {
        $retorno = $this->ordutf8(base64_encode($texto), $offset,$chave)."\n";
        if($retorno != 0){
          array_push($encriptado,$retorno);
        }
    }
    return $encriptado;
  }

  function decodificarTexto($chave,$chars){
    $base64 = '';
    foreach ($chars as $key) {
      $base64 .= chr(intval($key) / $chave);
    }
    return base64_decode($base64);
  }

}
?>
