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

$preparado = 0;

if(isset($_POST['quantidade_mensagens'])){
  $quantidade_mensagens = intval($_POST['quantidade_mensagens']);
}

if(isset($_POST['primeiro_acesso'])){
  $primeiro_acesso = 1;
}

if(isset($_POST['preparado'])){
  $preparado = $_POST['preparado'];
}

$offset = 0;
/*if($quantidade_mensagens > 0){
  if($quantidade_mensagens > 20){
    $offset = $quantidade_mensagens - 10;
  }else{
    $offset = $quantidade_mensagens;
  }
}*/
if(isset($_POST['offset'])){
  $offset = intval($_POST['offset']);
}

include_once('../config/dbconexao.php');
include_once('../util/consulta.php');
include_once('../util/tratamento.php');

require_once('../config/criptografia/rsa.php');

$consultar = new consultar();
$tratamento = new tratar();
$rsa = new criptografar();


if($consultar->checar_token($db,$_SESSION['token']) == 0){
  exit;
}

$informacoes = $consultar->selecionar_informacoes_pelo_o_token($db,$_SESSION['token']);

if(strlen($informacoes['id']) == 0){
  exit;
}

if($consultar->esta_na_sala($db,$informacoes['id'],$id_sala) == 0){
  exit;
}

?>


<?php

if($primeiro_acesso == 1){
  $conversas = $consultar->selecionar_ultimas_conversas($db,$id_sala,$offset);
}else{
  $conversas = $consultar->append_mensagem($db,$id_sala,$quantidade_mensagens);
}

?>
    <?php if($offset > 0){ ?><button class="btn btn-outline-primary mt-1 mb-1" onclick="javascript:mudar_offset();">Mostrar mais...</button><?php } ?>
        <?php
        $contando = 0;
        $lista = array();
        foreach ($conversas as $conversa) {

        if($conversa['id_bot'] == 0){
          $usuario = $consultar->selecionar_informacoes_do_usuario($db,$conversa['id_usuario']);
        }else{
          $usuario = $consultar->selecionar_info_robo($db,$conversa['id_usuario']);
        }
        $sala = $consultar->selecionar_informacoes_sala($db,$id_sala);

        $ultima_mensagem = $conversa['id_usuario'];
        array_push($lista,$ultima_mensagem);

        if($usuario['token'] == $_SESSION['token']){

        ?>

      <li class="list-group pt-1 pb-1">
        <div class="d-flex flex-row-reverse">
          <div class=""><?php if($usuario['ismaster']==0){if($conversa['id_usuario'] == $sala['id_admin']){ ?><small class="lider"><i class="fas fa-crown"></i></small><?php }}else{?><small class="master" data-toggle="tooltip" data-placement="top" title="MASTER"><i class="fas fa-chess-king"></i></small><?php } ?><img class="rounded-circle align-self-center ml-2" src="imagem/<?php if($usuario['ismaster']==0){echo $usuario['avatar'].'.jpg';}else{if($usuario['nome']=='JOANDESON A.'){echo 'john.gif';}else{echo $usuario['avatar'].'.jpg';}} ?>" width="40px" height="40px" src=""  /></div>
          <div class="align-self-center text-break max-container ml-2 bd-highlight <?php if($conversa['isImagem'] == 0){ ?> pt-3 pr-3 <?php }else{ ?> pt-2 pb-2 <?php } ?> rounded-2 bg-primary shadow">
            <?php if($conversa['isImagem'] == 0){ ?>
              <p class="ml-3 encrypted text-justify text-break"><?php echo $rsa->descriptografarTexto($conversa['texto']); ?></p>
            <?php }else{
                $imagem = $consultar->selecionar_imagem($db,$conversa['id_imagem']);
                if(strlen($imagem['nome']) > 0){
                  $nome = $imagem['nome'];
              ?>
              <div class="imagem-file pl-2 pr-2" id="<?php echo $imagem['id_imagem']; ?>" onclick="javascript:showImagem('<?php echo $imagem['id_imagem']; ?>');">
                <?php
                $explode = explode('|',$imagem['abrido']);
                $adicionado = false;
                foreach ($explode as $id) {
                  if($id == $usuario['id']){
                    $adicionado = true;
                  }
                }
                if($adicionado == false){
                ?>
                <div class="spinner-grow text-light" role="status">
                  <span class="sr-only">IMAGEM</span>
                </div>
                <p>
                  <?php
                    $imagem_texto = $tratamento->tratar_texto($conversa['texto']);
                    $imagem_texto = str_replace('{texto-abra}','<p><i class="fas fa-file-image"></i> Clique para abrir a imagem:</p>',$imagem_texto);
                    $imagem_texto = str_replace('{texto-nome}','<p class="font-weight-bold">'.$nome.'</p>',$imagem_texto);
                    echo $imagem_texto;
                    ?>
                </p>
              <?php }else{
                  $token = md5($imagem['id_imagem'].$usuario['id'].$id_sala.$sala['codigo']);
                ?>
                <a onclick="javascript:show_full_imagem('config/imagem.php?id_sala=<?php echo $sala['id']; ?>&id_imagem=<?php echo $imagem['id_imagem']; ?>&token=<?php echo $token; ?>&tipo=1');" href="#config/imagem.php?id_sala=<?php echo $sala['id']; ?>&id_imagem=<?php echo $imagem['id_imagem']; ?>&token=<?php echo $token; ?>&tipo=1" target="_self">
                  <img src="config/imagem.php?id_sala=<?php echo $sala['id']; ?>&id_imagem=<?php echo $imagem['id_imagem']; ?>&token=<?php echo $token; ?>" id="img_<?php echo $imagem['id_imagem']; ?>" id="img_<?php echo $imagem['id_imagem']; ?>" class="max-imagem rounded-2 preview-imagem" width="" height="" />
                </a>
              <?php } ?>
              </div>
            <?php
                }
              }
            ?>
          </div>
        </div>
      </li>

    <?php
  }else{
    ?>

    <li class="list-group pt-1 pb-1">
      <div class="d-flex flex-row">
        <div class=""><?php if($usuario['ismaster']==0){if($conversa['id_usuario'] == $sala['id_admin']){ ?><small class="lider" data-toggle="tooltip" data-placement="top" title="Administrador da sala"><i class="fas fa-crown"></i></small><?php }}else{?><small class="master" data-toggle="tooltip" data-placement="top" title="MASTER"><i class="fas fa-chess-king"></i></small><?php } ?><img class="rounded-circle align-self-center ml-2" width="40px" height="40px" src="<?php if($conversa['id_bot']==1){ echo 'bot/'; } ?>imagem/<?php if($usuario['ismaster']==0){echo $usuario['avatar'].'.jpg';}else{if($usuario['nome']=='JOANDESON A.'){echo 'john.gif';}else{echo $usuario['avatar'].'.jpg';}} ?>"  /></div>
        <div class="align-self-center text-break max-container ml-2 bd-highlight <?php if($conversa['isImagem'] == 0){ ?> pt-3 pr-3 <?php }else{ ?> pt-2 pb-2 pl-2 pr-2 <?php } ?> rounded-2 bg-white shadow">
          <?php if($contando > 0 && sizeof($lista) > 0){if($lista[($contando-1)] != $conversa['id_usuario']){ ?><h5 class="text-body <?php if($usuario['ismaster']==1){ ?>font-weight-bold<?php } ?> ml-2"><?php echo $tratamento->tratar_string($usuario['nome']); ?><?php if($usuario['ismaster'] == 1){ ?>(MASTER) <?php } if($conversa['id_bot']==1){ echo '(BOT)'; } ?></h5><?php }} ?>
          <?php if($conversa['isImagem'] == 0){ ?>
            <p class="ml-3 encrypted text-justify text-break" id="encriptado"><?php echo $tratamento->tratar_texto($rsa->descriptografarTexto($conversa['texto'])); ?></p>
          <?php }else{
              $imagem = $consultar->selecionar_imagem($db,$conversa['id_imagem']);
              if(strlen($imagem['nome']) > 0){
                $nome = $imagem['nome'];
            ?>
            <div class="imagem-file" id="<?php echo $imagem['id_imagem']; ?>" onclick="javascript:showImagem('<?php echo $imagem['id_imagem']; ?>');">
              <?php
              $explode = explode('|',$imagem['abrido']);
              $adicionado = false;
              foreach ($explode as $id) {
                if($id == $informacoes['id']){
                  $adicionado = true;
                }
              }
              if($adicionado == false){
              ?>
              <div class="spinner-grow text-primary" role="status">
                <span class="sr-only">IMAGEM</span>
              </div>
              <p>
                <?php
                  $imagem_texto = $tratamento->tratar_texto($conversa['texto']);
                  $imagem_texto = str_replace('{texto-abra}','<p><i class="fas fa-file-image"></i> Clique para abrir a imagem:</p>',$imagem_texto);
                  $imagem_texto = str_replace('{texto-nome}','<p class="font-weight-bold">'.$nome.'</p>',$imagem_texto);
                  echo $imagem_texto;
                  ?>
              </p>
            <?php }else{
                $token = md5($imagem['id_imagem'].$informacoes['id'].$id_sala.$sala['codigo']);
              ?>
              <a onclick="javascript:show_full_imagem('config/imagem.php?id_sala=<?php echo $sala['id']; ?>&id_imagem=<?php echo $imagem['id_imagem']; ?>&token=<?php echo $token; ?>&tipo=1');" href="#config/imagem.php?id_sala=<?php echo $sala['id']; ?>&id_imagem=<?php echo $imagem['id_imagem']; ?>&token=<?php echo $token; ?>&tipo=1" target="_self">
                <img src="config/imagem.php?id_sala=<?php echo $sala['id']; ?>&id_imagem=<?php echo $imagem['id_imagem']; ?>&token=<?php echo $token; ?>" id="img_<?php echo $imagem['id_imagem']; ?>" id="img_<?php echo $imagem['id_imagem']; ?>" class="max-imagem rounded-2 preview-imagem" width="" height="" />
              </a>
            <?php } ?>
            </div>
          <?php
              }
            }
          ?>
        </div>
      </div>
    </li>

    <?php
        }
        $contando++;
      }
    ?>

    <?php if($preparado == 0){ ?>
      <script>
        $(".chat-scroll").scrollTop($('#chatting')[0].scrollHeight);
        $(document.body).scrollTop($(document.body)[0].scrollHeight);
        $("#sender-texto").focus();
      </script>
    <?php } ?>

    <div class="digitando">

    </div>

<?php
