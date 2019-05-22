<?php
session_start();
header('Content-Type: application/json');

$mensagem[0] = json_encode(array("erro"=>1,"mensagem"=>"Token não existe."));
$mensagem[1] = json_encode(array("erro"=>1,"mensagem"=>"Token inválido."));
$mensagem[2] = json_encode(array("erro"=>1,"mensagem"=>"Informações do usuário é inválido."));
$mensagem[3] = json_encode(array("erro"=>1,"mensagem"=>"Dados de envio são inválidos."));
$mensagem[4] = json_encode(array("erro"=>1,"mensagem"=>"Você não possui permissão."));
$mensagem[5] = json_encode(array("erro"=>1,"mensagem"=>"Algo de inesperado aconteceu."));
$mensagem[6] = json_encode(array("erro"=>1,"mensagem"=>"Impossível enviar mensagem vázia."));

$sucesso = json_encode(array("erro"=>0,"mensagem"=>"keyup enviado com sucesso."));

include_once('../config/dbconexao.php');
include_once('../util/consulta.php');
include_once('../util/tratamento.php');

$consultar = new consultar();
$tratamento = new tratar();

if(!isset($_POST['id_sala'])){
  echo $mensagem[3];
  exit;
}

if(!isset($_SESSION['token'])){
  echo $mensagem[0];
  exit;
}

$id_sala = $_POST['id_sala'];

if($consultar->checar_token($db,$_SESSION['token']) == 0){
  echo $mensagem[1];
  exit;
}

$informacoes = $consultar->selecionar_informacoes_pelo_o_token($db,$_SESSION['token']);

if(strlen($informacoes['id']) == 0){
  echo $mensagem[2];
  exit;
}

if($consultar->esta_na_sala($db,$informacoes['id'],$id_sala) == 0){
  echo $mensagem[4];
  exit;
}

if($consultar->atualizar_digitacao($db,$informacoes['id'],$id_sala) == 1){
echo $sucesso;
exit;
}

?>
