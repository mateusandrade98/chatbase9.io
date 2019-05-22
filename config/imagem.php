<?php
session_start();
if(!isset($_GET['id_sala'])){
  exit;
}

if(!isset($_GET['id_imagem'])){
  exit;
}

if(!isset($_SESSION['token'])){
  exit;
}

$id_sala = $_GET['id_sala'];
$id_imagem = $_GET['id_imagem'];

include_once('dbconexao.php');
include_once('../util/consulta.php');
include_once('../util/tratamento.php');

$consultar = new consultar();
$tratamento = new tratar();


if($consultar->checar_token($db,$_SESSION['token']) == 0){
  exit;
}

$informacoes = $consultar->selecionar_informacoes_sala($db,$id_sala);
$informacoes_usuario = $consultar->selecionar_informacoes_pelo_o_token($db,$_SESSION['token']);

if(strlen($informacoes_usuario['id']) == 0){
  exit;
}

if($consultar->esta_na_sala($db,$informacoes_usuario['id'],$id_sala) == 0){
  exit;
}

$img = $consultar->imagem_esta_na_sala($db,$id_imagem,intval($id_sala));
if(strlen($img['id']) == 0){
  exit;
}

if(!isset($_GET['token'])){
  echo 'Token não existe';
  exit;
}

$token = md5($img['id_imagem'].$informacoes_usuario['id'].$id_sala.$informacoes['codigo']);
if($_GET['token'] != $token){
  echo 'Token inválido';
  exit;
}

if(!isset($_GET['tipo'])){
  $tipo = 0;
}else{
  $tipo = intval($_GET['tipo']);
}

if($tipo == 0){
  $url = '../uploads/thumbnail/'.$img['nome'];
}else{
  $url = '../uploads/'.$img['nome'];
}

header('Content-type:image/jpeg');
$data = file_get_contents($url);
//$base64 = 'data:image/jpeg;base64,'.base64_encode($data);
echo $data;

?>
