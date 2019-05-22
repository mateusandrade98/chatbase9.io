<?php
session_start();
header('Content-Type: application/json');

$mensagem[0] = json_encode(array("erro"=>1,"mensagem"=>"Token não existe."));
$mensagem[1] = json_encode(array("erro"=>1,"mensagem"=>"Token inválido."));
$mensagem[2] = json_encode(array("erro"=>1,"mensagem"=>"Informações do usuário é inválido."));
$mensagem[3] = json_encode(array("erro"=>1,"mensagem"=>"Dados de envio são inválidos."));
$mensagem[4] = json_encode(array("erro"=>1,"mensagem"=>"Você não possui permissão."));
$mensagem[5] = json_encode(array("erro"=>1,"mensagem"=>"Algo de inesperado aconteceu."));
$mensagem[6] = json_encode(array("erro"=>1,"mensagem"=>"Impossível enviar dados vázia."));

$sucesso = json_encode(
  array(
    "erro"=>0,
    "mensagem"=>"usuário banido."
  )
);

include_once('../config/dbconexao.php');
include_once('../util/consulta.php');

$consultar = new consultar();

if(!isset($_POST['id_usuario'])){
  echo $mensagem[3];
  exit;
}

if(empty($_POST['id_usuario'])){
  echo $mensagem[6];
  exit;
}

if(!isset($_POST['id_sala'])){
  echo $mensagem[3];
  exit;
}

if(empty($_POST['id_sala'])){
  echo $mensagem[6];
  exit;
}

if(!isset($_SESSION['token'])){
  echo $mensagem[0];
  exit;
}

$id_sala = $_POST['id_sala'];
$id_usuario = $_POST['id_usuario'];

if($consultar->checar_token($db,$_SESSION['token']) == 0){
  echo $mensagem[1];
  exit;
}

$informacoes = $consultar->selecionar_informacoes_pelo_o_token($db,$_SESSION['token']);

if(strlen($informacoes['id']) == 0){
  echo $mensagem[2];
  exit;
}

/*desabilitar o banimento do master*/
$informacoes_usuario = $consultar->selecionar_informacoes_do_usuario($db,$id_usuario);
if($informacoes_usuario['ismaster'] == 1){
  echo $mensagem[4];
  exit;
}
/**/

/*master*/
if($informacoes['ismaster'] == 1){
    $consultar->banir_usuario($db,$id_usuario,$id_sala);
    echo $sucesso;exit;
}
/**/

if($consultar->esta_na_sala($db,$informacoes['id'],$id_sala) == 0){
  echo $mensagem[4];
  exit;
}

if($consultar->usuario_E_admin($db,$informacoes['id'],$id_sala) == 0){
  echo $mensagem[4];
  exit;
}

if($consultar->banir_usuario($db,$id_usuario,$id_sala) == 1){
  echo $sucesso;exit;
}

?>
