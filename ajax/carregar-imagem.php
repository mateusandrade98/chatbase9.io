<?php
session_start();
if(!isset($_POST['id_sala'])){
  exit;
}

if(!isset($_POST['id_imagem'])){
  exit;
}

if(!isset($_SESSION['token'])){
  exit;
}

$id_sala = $_POST['id_sala'];
$id_imagem = $_POST['id_imagem'];

include_once('../config/dbconexao.php');
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

$img = $consultar->imagem_esta_na_sala($db,$id_imagem,$id_sala);
if(strlen($img['id']) == 0){
  exit;
}

if($consultar->atualizar_abrido($db,$informacoes_usuario['id'],$img['id_imagem']) == 1){

  //$url = $img['thumbnailUrl'];
  //header('Content-type:image/jpeg');
  //$data = file_get_contents($url);
  //$base64 = 'data:image/jpeg;base64,'.base64_encode($data);
  //echo $data;
  $token = md5($img['id_imagem'].$informacoes_usuario['id'].$id_sala.$informacoes['codigo']);
  echo 'config/imagem.php?id_sala='.$id_sala.'&id_imagem='.$img['id_imagem'].'&token='.$token;
  exit;

}else{
  header('Content-Type: application/json');
  echo json_encode(array("erro"=>1,"mensagem"=>"Erro na abertura da imagem."));
}
?>
