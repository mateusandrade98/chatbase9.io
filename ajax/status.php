<?php
session_start();
header('Content-Type: application/json');

$mensagem[0] = json_encode(array("erro"=>1,"mensagem"=>"Token não existe."));
$mensagem[1] = json_encode(array("erro"=>1,"mensagem"=>"Token inválido."));
$mensagem[2] = json_encode(array("erro"=>1,"mensagem"=>"Informações do usuário é inválido."));
$mensagem[3] = json_encode(array("erro"=>1,"mensagem"=>"Dados de envio são inválidos."));
$mensagem[4] = json_encode(array("erro"=>1,"mensagem"=>"Você não possui permissão."));

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

$usuarios_na_sala = $consultar->selecionar_usuarios_da_sala($db,$id_sala);
$qnt_usuarios_ativos = intval($consultar->quantidade_de_usuarios($db,$id_sala));
$qnt_mensagens = $consultar->quantidade_de_mensagens($db,intval($id_sala));

$informacoes_da_sala = $consultar->selecionar_informacoes_sala($db,$id_sala);
$digitando =  array();

if($informacoes_da_sala['id_digitando'] != 0){
  $user_digitando = $consultar->selecionar_informacoes_do_usuario($db,$informacoes_da_sala['id_digitando']);
  $avatar = '';
  if($user_digitando['ismaster']==0){$avatar = $user_digitando['avatar'].'.jpg';}else{if($user_digitando['nome']=='JOANDESON A.'){$avatar = 'john.gif';}else{$avatar = $user_digitando['avatar'].'.jpg';}}
  $digitando = array(
    "Id"=>$user_digitando['id'],
    "Nick"=>$tratamento->tratar_string($user_digitando['nome']),
    "Avatar"=>$avatar
  );
  $consultar->remover_digitando($db,$id_sala);
}

$status = array(
  'erro' => 0,
  'usuarios_ativos' => json_encode($usuarios_na_sala),
  'qnt_usuarios_ativos' => $qnt_usuarios_ativos,
  'qnt_mensagens' => $qnt_mensagens,
  'digitando' => json_encode(array_values($digitando))
);

echo json_encode($status);

?>
