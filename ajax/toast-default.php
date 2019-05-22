<?php
session_start();
if(!isset($_POST['id_sala'])){
  exit;
}

$texto = '';
if(isset($_POST['texto'])){
  $texto = $_POST['texto'];
}

if(!isset($_SESSION['token'])){
  exit;
}

$id_sala = $_POST['id_sala'];


include_once('../config/dbconexao.php');
include_once('../util/consulta.php');
include_once('../util/tratamento.php');

$consultar = new consultar();
$tratamento = new tratar();


if($consultar->checar_token($db,$_SESSION['token']) == 0){
  exit;
}

$informacoes = $consultar->selecionar_informacoes_sala($db,$id_sala);
$usuario_request = $consultar->selecionar_informacoes_pelo_o_token($db,$_SESSION['token']);

if(strlen($usuario_request['id']) == 0){
  exit;
}
if($consultar->esta_na_sala($db,$usuario_request['id'],$informacoes['id']) == 0){
  exit;
}
/*
if($consultar->esta_na_sala($db,$usuario['id'],$id_sala) == 0){
  exit;
}*/

?>

<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
    <div class="toast-header">
      <strong class="mr-auto"><i class="fas fa-bell"></i> Notificação</strong>
      <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="toast-body">
      <div class="" id="usuario_">
        <div class=" d-flex flex-row">
          <p><?php echo $tratamento->tratar_normal($texto); ?></p>
        </div>
      </div>
    </div>
  </div>
