<?php
class executar{

  function ajuda($db,$consultar,$info,$param,$rsa){
    $robo = $consultar->selecionar_info_robo($db,$info['id_robo']);//id-robo
    $sala = $consultar->selecionar_informacoes_sala($db,$info['id_sala']);//id-sala
    $comando = str_replace('{bot-nome}',$robo['nome'],$param);//comando
    $comando = str_replace('{nome-sala}',$sala['nome'],$comando);
    $comando = str_replace('{bot-alias}',$robo['alias'],$comando);
    return $consultar->enviar_messagem($db,$sala['id'],$rsa->criptografarTexto($comando),$robo,0,0,$info['id_robo']);
  }

  function banir($db,$consultar,$info){
    /*desabilitar o banimento do master*/
    $informacoes_usuario = $consultar->selecionar_informacoes_do_usuario($db,$info['id_usuario']);//id-usuario

    if($informacoes_usuario['ismaster'] == 1){
      return 0;
    }
    /**/

    if($consultar->esta_na_sala($db,$informacoes_usuario['id'],$info['id_sala']) == 0){
      return 0;
    }

    if($consultar->usuario_E_admin($db,$informacoes_usuario['id'],$info['id_sala']) == 1){
      return 0;
    }

    if($consultar->banir_usuario($db,$info[0],$info['id_sala']) == 1){
      return 1;
    }

  }

  function banirAll($db,$consultar,$info){

    $informacoes = $consultar->selecionar_informacoes_do_usuario($db,$info['id_usuario']);//id-usuario

    /*master*/
    if($informacoes['ismaster'] == 1){
        $sala = $consultar->selecionar_usuarios_da_sala($db,$info['id_sala']);//id-sala
        foreach ($sala as $usuario) {
          $info = $consultar->selecionar_informacoes_do_usuario($db,$usuario['id_usuario']);
          if($info['ismaster'] == 0){
            $consultar->banir_usuario($db,$info['id'],$usuario['id_sala']);
          }
        }
        return 1;
    }
    /**/

  }

  function falar($db,$consultar,$info,$texto,$rsa){
    $robo = $consultar->selecionar_info_robo($db,$info['id_robo']);//id-robo
    $sala = $consultar->selecionar_informacoes_sala($db,$info['id_sala']);//id-sala
    return $consultar->enviar_messagem($db,$sala['id'],$rsa->criptografarTexto($texto),$robo,0,0,$robo['id']);
  }

  function regras($db,$consultar,$info,$param,$rsa){
    $robo = $consultar->selecionar_info_robo($db,$info['id_robo']);//id-robo
    $sala = $consultar->selecionar_informacoes_sala($db,$info['id_sala']);//id-sala
    return $consultar->enviar_messagem($db,$sala['id'],$rsa->criptografarTexto($param),$robo,0,0,$robo['id']);
  }

  function jujuba($db,$consultar,$parametros,$info,$rsa){
    $robo = $consultar->selecionar_info_robo($db,$info['id_robo']);//id-robo
    //$usuario = $consultar->selecionar_informacoes_do_usuario($db,$info['id_usuario']);//id-usuario
    $sala = $consultar->selecionar_informacoes_sala($db,$info['id_sala']);//id-sala
    //$comando = str_replace('{nome-usuario}',$info['nome'],$comando);//comando
    //$comando = str_replace('{texto}',$info[4],$comando);//texto
    return $consultar->enviar_messagem($db,$info['id_sala'],$rsa->criptografarTexto($parametros),$robo,0,0,$robo['id']);
  }

}

class robo{
  function obterConfiguracoes(){
    $config = file_get_contents(__DIR__.'/config.json');
    return json_decode($config);
  }

  function executar_comando($db,$consultar,$comando,$parametros,$info,$rsa){
    $cmd = new executar();
    if($comando == '!ajuda'){
      return $cmd->ajuda($db,$consultar,$info,$parametros,$rsa);
    }
    if($comando == '!banir'){
      return $cmd->banir($db,$consultar,$info);
    }
    if($comando == '!killall'){
      return $cmd->banirAll($db,$consultar,$info);
    }
    if($comando == '!regras'){
      return $cmd->regras($db,$consultar,$info,$parametros,$rsa);
    }
    if($comando == '!jujuba'){
      return jujuba($db,$consultar,$parametros,$info,$rsa);
    }
  }

  function executou_comando($texto){
    //!([a-zA-Z]+)
    preg_match('/!([a-zA-Z]+)/',$texto,$match);
    return (count($match) > 0);
  }

  function executar_fala($db,$consultar,$texto,$info,$rsa){
    $parametros = $this->comando_bot($db,$consultar,$texto,$info);
    $comandos = ['frase','piada','ajuda','regras'];
    $xxx = '';
    if(strlen($parametros) > 0){
      foreach ($comandos as $key) {
        $alias = $key;
        $preg = '/(';
        for($i=0;$i<strlen($alias);$i++){
          $preg .= '['.strtoupper($alias[$i]).'-'.strtolower($alias[$i]).']';
        }
        $preg .= ')(.*)/';
        preg_match($preg,$texto,$match);
        if(count($match) > 0){
          $exec = new executar();
          switch ($match[1]) {
            case 'frase':
                $json_frase = file_get_contents(__DIR__.'/../bot/frases.json');
                $frases = json_decode($json_frase,true);
                $newFrase = $frases['corno'][rand(0,count($frases['corno']))];
                $exec->falar($db,$consultar,$info,$newFrase,$rsa);
              break;

            case 'piada':
                $json_piada = file_get_contents(__DIR__.'/../bot/piadas.json');
                $piadas = json_decode($json_piada,true);
                $newPiada = $piadas['corno'][rand(0,count($piadas['corno']))];
                $exec->falar($db,$consultar,$info,$newPiada,$rsa);
              break;

            case 'regras':
                $param = $this->comando($db,'!regras');
                $param = explode('--|--',$param)[1];
                $exec->regras($db,$consultar,$info,$param,$rsa);
              break;

            case 'ajuda':
                $param = $this->comando($db,'!ajuda');
                $param = explode('--|--',$param)[1];
                $exec->ajuda($db,$consultar,$info,$param,$rsa);
              break;

            default:
              break;
          }
        }
      }
      return $xxx;
    }
    return false;
  }

  function comando_bot($db,$consultar,$texto,$info){
    $robo = $consultar->selecionar_info_robo($db,$info['id_robo']);
    $alias = $robo['alias'];
    $preg = '/(^';
    for($i=0;$i<strlen($alias);$i++){
      $preg .= '['.strtoupper($alias[$i]).'-'.strtolower($alias[$i]).']';
    }
    $preg .= ')(.*)/';
    preg_match($preg,$texto,$match);
    $param = '';
    if(count($match) > 1){
      $param = $match[2];
    }
    $ma = explode(" ",$param);
    if(count($ma) > 1){
      return $ma[1];
    }
    return $param;
  }

  function comando($db,$cmd){
    $config = $this->obterConfiguracoes();

    $comando = [];
    foreach ($config[0] as $r) {
      array_push($comando,$r);
    }
    $execute = [];
    foreach ($config[1] as $r) {
      array_push($execute,$r);
    }

    for($i=0;$i<count($comando[0]);$i++){
      if($cmd == $comando[0][$i]){
        return $comando[0][$i].'--|--'.$execute[0][$i];
      }
    }
    return 0;
  }

}
?>
