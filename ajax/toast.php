<?php
session_start();
if(!isset($_POST['id_sala'])){
  exit;
}

if(!isset($_POST['id_usuario'])){
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
$id_usuario = $_POST['id_usuario'];


include_once('../config/dbconexao.php');
include_once('../util/consulta.php');
include_once('../util/tratamento.php');

$consultar = new consultar();
$tratamento = new tratar();


if($consultar->checar_token($db,$_SESSION['token']) == 0){
  exit;
}

$informacoes = $consultar->selecionar_informacoes_sala($db,$id_sala);
$usuario = $consultar->selecionar_informacoes_do_usuario($db,$id_usuario);
$usuario_request = $consultar->selecionar_informacoes_pelo_o_token($db,$_SESSION['token']);

if(strlen($usuario['id']) == 0){
  exit;
}
if($consultar->esta_na_sala($db,$usuario_request['id'],$informacoes['id']) == 0){
  exit;
}

if($consultar->esta_na_sala($db,$usuario['id'],$informacoes['id']) == 0){
  exit;
}

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
          <div class=""><?php if($usuario['ismaster']==0){if($usuario['id'] == $informacoes['id_admin']){ ?><small class="lider" <?php if($usuario['id']==$informacoes['id_admin']){ ?> data-toggle="tooltip" data-placement="top" title="Administrador da sala" <?php } ?>><i class="fas fa-crown"></i></small><?php }}else{?><small class="master" data-toggle="tooltip" data-placement="top" title="MASTER"><i class="fas fa-chess-king"></i></small><?php } ?><img class="rounded-circle align-self-center ml-2" src="imagem/<?php if($usuario['ismaster']==0){echo $usuario['avatar'].'.jpg';}else{if($usuario['nome']=='JOANDESON A.'){echo 'john.gif';}else{echo $usuario['avatar'].'.jpg';}} ?>" width="40px" height="40px" src=""  /></div>
          <div class="align-self-center ml-2 bd-highlight  p-2 mb-2">
            <p class="ml-3 font-weight-bold"><?php echo $tratamento->tratar_string($usuario['nome']); ?><?php if($usuario['ismaster'] == 1){ ?>(MASTER) <?php } ?><?php if($usuario['token'] == $_SESSION['token']){ ?> <i class="fas fa-laugh-beam" data-toggle="tooltip" data-placement="top" title="EU"></i><?php } ?></p>
            <p><?php echo $tratamento->tratar_normal($texto); ?></p>
          </div>
        </div>
      </div>
    </div>
