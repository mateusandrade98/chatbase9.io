<?php
session_start();
if(!isset($_POST['id_sala'])){
  exit;
}

if(!isset($_SESSION['token'])){
  exit;
}

$id_sala = $_POST['id_sala'];

$primeiro_acesso = 0;
$quantidade_mensagens = 0;

if(isset($_POST['quantidade_mensagens'])){
  $quantidade_mensagens = intval($_POST['quantidade_mensagens']);
}

if(isset($_POST['primeiro_acesso'])){
  $primeiro_acesso = 1;
}

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

?>

<?php
$usuarios_na_sala = $consultar->selecionar_usuarios_da_sala($db,$id_sala);
foreach ($usuarios_na_sala as $usuario_grupo) {
?>
<?php $usuario = $consultar->selecionar_informacoes_do_usuario($db,$usuario_grupo['id_usuario']); ?>
<div class="list-group-item" id="usuario_<?php echo $usuario['id']; ?>">
  <div class=" d-flex flex-row max-container text-break">
  <div class=""><?php if($usuario['ismaster']==0){if($usuario['id'] == $informacoes['id_admin']){ ?><small class="lider" <?php if($usuario['id']==$informacoes['id_admin']){ ?> data-toggle="tooltip" data-placement="top" title="Administrador da sala" <?php } ?>><i class="fas fa-crown"></i></small><?php }}else{?><small class="master" data-toggle="tooltip" data-placement="top" title="MASTER"><i class="fas fa-chess-king"></i></small><?php } ?><img class="rounded-circle align-self-center ml-2" src="imagem/<?php if($usuario['ismaster']==0){echo $usuario['avatar'].'.jpg';}else{if($usuario['nome']=='JOANDESON A.'){echo 'john.gif';}else{echo $usuario['avatar'].'.jpg';}} ?>" width="40px" height="40px" src=""  /></div>
  <div class="align-self-center ml-2 bd-highlight  p-2 mb-2">
    <p class="ml-3 <?php if($usuario['ismaster']==1){ ?>font-weight-bold<?php } ?>"><?php echo $tratamento->tratar_string($usuario['nome']); ?><?php if($usuario['ismaster'] == 1){ ?>(MASTER) <?php } ?><?php if($usuario['token'] == $_SESSION['token']){ ?> <i class="fas fa-laugh-beam" data-toggle="tooltip" data-placement="top" title="EU"></i><?php } ?></p>
  </div>
</div>

  <?php if($informacoes_usuario['id'] == $informacoes['id_admin'] || $informacoes_usuario['ismaster'] == 1){ ?>
    <?php if($usuario['id'] != $informacoes_usuario['id'] && $usuario['ismaster']==0){ ?>
      <div class="banir">
        <button class="btn btn-danger float-right" onclick="javascript:banir_usuario(<?php echo $usuario['id']; ?>);"><i class="fas fa-ban"></i> banir</button>
      </div>
    <?php } ?>
  <?php } ?>

</div>
<?php } ?>
