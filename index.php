<?php
session_start();
if(!isset($_COOKIE['token'])){
  $_SESSION['token'] = md5(rand(0,999999999999));
  setcookie("token",$_SESSION['token'], time()+3600);
}else{
  $_SESSION['token'] = $_COOKIE['token'];
}

require_once('config/dbconexao.php');
require_once('util/consulta.php');
require_once('util/tratamento.php');
require_once('util/sessions.php');
require_once('config/criptografia/codigo.php');



$consultar = new consultar();
$tratamento = new tratar();
$session = new sessao();
$codigo = new informacoes();

$secreto = $codigo->getSecretCode();

?>

<?php include_once('config/header.php'); ?>

  <body>

    <?php

      $session->destruir_sessao($db);

      if($consultar->checar_token($db,$_SESSION['token']) == 0){
        header('Location:criarnick.php?r=criar');
        exit;
      }
      if(!isset($_SESSION['codigo'])){
        echo 'código inválido.';
        exit;
      }
      $listas = $consultar->selecionar_lista($db,$_SESSION['codigo'] / $secreto);

    ?>
    <nav class="navbar navbar-dark bg-primary">
      <a href="index.php" class="navbar-brand"><?php if($_SESSION['codigo']==0){ ?><i class="fas fa-laugh-beam"></i> Salas Abertas<?php }else{ ?><i class="fas fa-lock"></i> Salas com o código(<?php echo $_SESSION['codigo'] / $secreto; ?>)<?php } ?></a>
      <div class="form-inline">
        <button class="btn btn-outline-dark pointer mr-2" onclick="javascript:window.location='criarnick.php?logout';"><i class="fas fa-sign-out-alt"></i></button>
        <a class="btn btn-light pointer"><i class="fas fa-plus-circle"></i> Criar Sala</a>
      </div>
    </nav>
    <div class="list-group">
      <?php foreach ($listas as $lista) { ?>
      <a href="sala.php?id=<?php echo $lista['id']; ?>" class="list-group-item list-group-item-action">
          <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1 font-weight-bold"><?php echo $tratamento->tratar_string($lista['nome']); ?></h5>
            <i class="fas fa-laugh-beam text-primary"></i>
          </div>
        <small class="text-muted"><?php echo $tratamento->tratar_string($consultar->quantidade_de_usuarios($db,$lista['id'])); ?>/<?php echo $tratamento->tratar_string($lista['limite']); ?> USUÁRIOS</small>
      </a>
    <?php } ?>
    </div>
  </body>
  <script>
    document.title = 'Lista de salas';
  </script>
</html>
