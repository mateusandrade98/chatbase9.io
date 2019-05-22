<?php
class keys{

  function obterChavePrivada(){
    if(file_exists(__DIR__.'/../../chaves/privada.pem')){
      return file_get_contents(__DIR__.'/../../chaves/privada.pem');
    }else{
      return '';
    }
  }

  function obterChavePublica(){
    if(file_exists(__DIR__.'/../../chaves/publica.pem')){
      return file_get_contents(__DIR__.'/../../chaves/publica.pem');
    }else{
      return '';
    }
  }

}

class criptografar{

  function criptografarTexto($texto){
    $key = new keys();
    $maxlen = 53;
    $len = strlen($texto);
    $resultado = '';
    $offset = 0;
    if($len > $maxlen){
      $tabulacao = ceil($len/$maxlen);
      while($len > $offset){
        if($offset == 0){
          $resultado .= substr($texto,0,53).'(bloco)';
        }else{
          $resultado .= substr($texto,$offset,53).'(bloco)';
        }
        $offset +=  53;
      }
    }else{
      $resultado .= $texto.'(bloco)';
    }

    $split = explode('(bloco)',$resultado);
    $completo = '';
    $publica = $key->obterChavePublica();
    foreach ($split as $bloco) {
      if(strlen($bloco) > 0){
        if(openssl_public_encrypt($bloco,$encriptado,$publica)){
          $completo .= base64_encode($encriptado).'--end--';
        }
      }
    }
    return $completo;
  }

  function descriptografarTexto($encriptado){
    $key = new keys();
    $privada = $key->obterChavePrivada();
    $split = explode('--end--',$encriptado);
    $completo = '';
    foreach ($split as $bloco) {
      if(strlen($bloco) > 0){
        $bloco = base64_decode($bloco);
        if(openssl_private_decrypt($bloco,$descriptografado,$privada)){
          $completo .= $descriptografado;
        }
      }
    }
    return $completo;
  }

  function criarArquivo($arq,$data){
    $f = fopen($arq,'wb');
    fwrite($f,$data);
    fclose($f);
  }

  function arquivo($action, $arquivo) {
      $arquivo = __DIR__.'/../../uploads/a64d0d4dbdeeb7a45bdedb985033c0ca.jpeg';
      $output = false;
      $encrypt_method = "AES-256-CBC";
      $secret_key = '666';
      $secret_iv = 6666666666666666;
      // hash
      $key = hash('sha256', $secret_key);

      $f = fopen($arquivo,'rb');
      $r = fread($f,filesize($arquivo));
      fclose($f);

      // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
      $iv = substr(hash('sha256', $secret_iv), 0, 16);
      if ( $action == 'en' ) {
          $output = openssl_encrypt($r, $encrypt_method, $key, 0, $iv);
          //$output = base64_encode($output);
      } else if( $action == 'de' ) {
          $output = openssl_decrypt($r, $encrypt_method, $key, 0, $iv);
      }

      $this->criarArquivo($arquivo.'_crypted.jpeg',$output);
  }

}
?>
