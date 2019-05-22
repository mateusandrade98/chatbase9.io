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

$sucesso = json_encode(array("erro"=>0,"mensagem"=>"enviado com sucesso."));

include_once('../config/dbconexao.php');
include_once('../util/consulta.php');
include_once('../util/tratamento.php');

require_once('../config/criptografia/rsa.php');

$consultar = new consultar();
$tratamento = new tratar();
$rsa = new criptografar();

//url:url,thumbnailUrl:thumbnailUrl,nome:nome

if(!isset($_POST['url'])){
  echo $mensagem[3];
  exit;
}

if(!isset($_POST['thumbnailUrl'])){
  echo $mensagem[3];
  exit;
}

if(!isset($_POST['nome'])){
  echo $mensagem[3];
  exit;
}

if(empty($_POST['url'])){
  echo $mensagem[6];
  exit;
}

if(empty($_POST['thumbnailUrl'])){
  echo $mensagem[6];
  exit;
}

if(empty($_POST['nome'])){
  echo $mensagem[6];
  exit;
}

if(!isset($_POST['id_sala'])){
  echo $mensagem[3];
  exit;
}

if(!isset($_SESSION['token'])){
  echo $mensagem[0];
  exit;
}

$id_sala = $_POST['id_sala'];
$url = $_POST['url'];
$thumbnailUrl = $_POST['thumbnailUrl'];
$nome = $_POST['nome'];

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

//<p><i class="fas fa-file-image"></i>Clique para abrir a imagem:</p>
//<p class="font-weight-bold">'.$nome.'</p>
$texto = '{texto-abra} {texto-nome}';
$id_imagem = md5($nome);

$tnome = md5($nome.$_SESSION['token'].rand(0,99999999)).'.jpg';

if(file_exists('../uploads/'.$nome)){
  $rn = rename('../uploads/'.$nome,'../uploads/'.$tnome).'.jpg';
}

if(file_exists('../uploads/thumbnail/'.$nome)){
  $rnth = rename('../uploads/thumbnail/'.$nome,'../uploads/thumbnail/'.$tnome).'.jpg';
}

$url = str_replace(urlencode($nome),urlencode($tnome),$url);
$thumbnailUrl = str_replace(urlencode($nome),urlencode($tnome),$thumbnailUrl);
$nome = $tnome;

if($consultar->enviar_imagem($db,$nome,$id_sala,$id_imagem,$informacoes) == 0){
  echo $mensagem[5];
  exit;
}

if($consultar->enviar_messagem($db,$id_sala,$texto,$informacoes,1,$id_imagem) == 1){
  echo $sucesso;
  exit;
}else{
  echo $mensagem[5];
}

?>
