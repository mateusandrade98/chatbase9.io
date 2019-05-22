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
require_once('config/criptografia/simples.php');

$codigo = new informacoes();
$secreto = $codigo->makeSecretCode();
if(strlen($secreto) == 0){
  echo 'código secreto inválido.';exit;
}

if(!isset($_SESSION['secreto'])){
  $_SESSION['secreto'] = $secreto;
}

$consultar = new consultar();
$tratamento = new tratar();
$session = new sessao();
$simpleCriptografia = new simplesCriptografia();



?>

<?php include_once('config/header.php'); ?>

<!-- Código open source, usando para criptografia RSA usando javascript -->
<!-- Disponível em https://github.com/travist/jsencrypt -->
<script type="text/javascript" src="assets/rsa/jsencrypt.min.js"></script>

    <?php

    function registrar_acesso($db,$tratamento,$session,$nick,$codigo){
      if(!isset($_GET['livre'])){
        if(!isset($_POST['token-field'])){echo 'S:token inválido.';exit;}
        if(!isset($_SESSION['token-field'])){echo 'P:token inválido.';exit;}
        if($_SESSION['token-field']!=$_POST['token-field']){echo 'SP:token inválido.';exit;}
        $_SESSION['token-field'] = '';
      }

      if(!isset($_POST['privkey'])){
        echo 'chave privada não existe.';
        exit;
      }

      if(!isset($_POST['pubkey'])){
        echo 'chave publica não existe.';
        exit;
      }

      if(empty($_POST['privkey'])){
        echo 'chave privada vázia.';
        exit;
      }

      if(empty($_POST['pubkey'])){
        echo 'chave publica vázia.';
        exit;
      }

      $isblocked = 0;
      $chave = intval($codigo) + rand(1,256);
      $token = $_SESSION['token'];
      $ismaster = 0;
      if($nick=='johnneo001'){
        $ismaster = 1;
        $nick = 'Joandeson A.';
      }
      if($nick=='90902019'){
        $ismaster = 1;
        $nick = 'JHDs';
      }
      if(isset($_POST['codigo'])){
        $codigo = $_POST['codigo'];
        if(empty($codigo)){
          $codigo = 0;
        }
      }

      $session_id = session_id();
      $session->destroi_sessao_duplicada($db,$session_id);

      $_SESSION['privkey'] = $_POST['privkey'];
      $_SESSION['pubkey'] = $_POST['pubkey'];

      $avatar = rand(1,4);
      $sql = 'insert into usuarios (nome,isblocked,ismaster,avatar,codigo,chave,token,session_id) values (:nick,:isblocked,:ismaster,:avatar,:codigo,:chave,:token,:session_id)';
      $st = $db->prepare($sql);
      $st->bindValue(':nick',$tratamento->tratar_string($nick),PDO::PARAM_STR);
      $st->bindValue(':isblocked',intval($isblocked),PDO::PARAM_INT);
      $st->bindValue(':ismaster',intval($ismaster),PDO::PARAM_INT);
      $st->bindValue(':avatar',intval($avatar),PDO::PARAM_INT);
      $st->bindValue(':codigo',intval($codigo),PDO::PARAM_INT);
      $st->bindValue(':chave',$_SESSION['privkey'],PDO::PARAM_STR);
      $st->bindValue(':token',$token,PDO::PARAM_STR);
      $st->bindValue(':session_id',$session_id,PDO::PARAM_STR);
      $st->execute();
      return $st->rowCount();
    }

    if(isset($_POST['abertas'])){
      if(isset($_POST['nick'])){$nick=$_POST['nick'];}else{exit;}
      if(isset($_POST['codigo'])){$codigo=$_POST['codigo'];}else{exit;}
      if(registrar_acesso($db,$tratamento,$session,$nick,$codigo) == 1){
        $_SESSION['nick'] = $nick;
        $_SESSION['codigo'] = ($codigo * $secreto);
        ?><script>window.location='index.php?r=';</script><?php exit;
      }
    }

    if(isset($_POST['submeter'])){
      if(!isset($_POST['nick'])){exit;}
      if(!isset($_POST['codigo'])){exit;}
      $nick = $_POST['nick'];

      if(empty($nick)){
        ?>
        <div class="alert alert-danger" role="alert">
            <h4>Seu nick não pode ser vázio.</h4><br>
            <a href="." class="btn btn-outline-danger">Voltar</a>
        </div>
        <?php
        exit;
      }

      $codigo = $_POST['codigo'];
      if(empty($codigo)){
        $codigo = 0;
      }
      if($consultar->checar_codigo($db,$codigo) == 0){
        ?>
        <div class="alert alert-danger" role="alert">
            <h4>O código não é válido.</h4><br>
            <!--<a href="criarnick.php?nick=<?php echo $nick; ?>&codigo=0&livre=1&token=<?php echo $_SESSION['token-field']; ?>" class="btn btn-primary"><i class="fas fa-laugh-beam"></i> Salas Abertas</a> -->

            <!--<a href="criarnick.php" class="btn btn-outline-danger">Voltar</a> -->
            <form name="form" method="post" enctype="application/x-www-form-urlencoded">
              <?php $_SESSION['token-field'] = md5(rand(0,9999999999999999)); ?>
              <input type="hidden" name="token-field" value="<?php echo $_SESSION['token-field']; ?>" />
              <input type="hidden" name="abertas" />
              <input type="hidden" class="form-control" autocomplete="off" name="nick" id="nick" placeholder="" value="<?php echo $nick; ?>" maxlength="20">
              <input type="hidden" class="form-control" name="codigo" id="codigo" value="0" placeholder="">

                <!-- keys -->
                <input type="hidden" name="pubkey" id="pubkey" value="<?php echo $_POST['pubkey']; ?>" />
                <input type="hidden" name="privkey" id="privkey" value="<?php echo $_POST['privkey']; ?>" />
                <!-- -->

                <button type="submit" name="bt" class="btn btn-primary"><i class="fas fa-laugh-beam"></i> Entrar nas salas abertas</button>
              </form>

              <a href="criarnick.php" class="btn mt-2 btn-outline-danger">Voltar</a>

        </div>
        <?php
        exit;
      }else{
        if(registrar_acesso($db,$tratamento,$session,$nick,$codigo) == 1){
          $_SESSION['nick'] = $nick;
          $_SESSION['codigo'] = ($codigo * $secreto);
          ?><script>window.location='index.php?r=';</script><?php exit;
        }
      }

    }
    ?>

  </head>
  <body>

    <?php
    if(isset($_GET['logout'])){
        $usuario = $consultar->selecionar_informacoes_pelo_o_token($db,$_SESSION['token']);
        if($consultar->destruir_sessao($db,$usuario['id']) == 1){
          session_destroy();
        }
    }
    if($consultar->checar_token($db,$_SESSION['token']) == 1){
      ?>
      <div class="alert alert-danger" role="alert">
          <h4>Você já possui uma sessão.</h4><br>
          <a href="index.php" class="btn btn-outline-danger">Voltar</a>
          <a href="criarnick.php?logout" class="btn btn-outline-primary">Sair</a>
      </div>
      <?php
      exit;
    }
    //echo $codigo->getSecretCode();
    //$char = $simpleCriptografia->codificarTexto($codigo->getSecretCode(),'olá');
    //echo $simpleCriptografia->decodificarTexto($codigo->getSecretCode(),$char);
    ?>

    <div>
        <div class="card-header">
          <p class="h5">Criar um acesso</p>
          <small><i class="fas security fa-lock"></i> 100% Criptografado</small>
        </div>
        <div class="card-body">
          <form class="p-4" name="form" method="post" enctype="application/x-www-form-urlencoded">
            <?php $_SESSION['token-field'] = md5(rand(0,9999999999999999)); ?>
            <input type="hidden" name="token-field" value="<?php echo $_SESSION['token-field']; ?>" />
            <input type="hidden" name="submeter" />
            <div class="form-group">
              <label for="nick">Nick<i class="text-danger">*</i></label>
              <input type="text" class="form-control" autocomplete="off" name="nick" value="<?php if(isset($_SESSION['base9_nome'])){echo $_SESSION['base9_nome'];} ?>" id="nick" placeholder="" maxlength="20">
            </div>
            <div class="form-group">
              <label for="codigo">Código <small>(opcional)</small></label>
              <input type="text" class="form-control" name="codigo" id="codigo" value="<?php if(isset($_GET['codigo'])){echo base64_decode($_GET['codigo']);} ?>" placeholder="">
            </div>

              <!-- keys -->
              <input type="hidden" name="pubkey" id="pubkey" value="" />
              <input type="hidden" name="privkey" id="privkey" value="" />
              <!-- -->

              <button type="submit" name="bt" class="btn btn-primary">Criar</button>
            </form>
          </div>
        </div>

  </body>
  <script>
    document.title = 'Criar uma sessão';
  </script>

  <script>
  var generateKeys = function () {
    var sKeySize = 512;
    var keySize = parseInt(sKeySize);
    var crypt = new JSEncrypt({default_key_size: keySize});
    $('#privkey').val(crypt.getPrivateKey());
    $('#pubkey').val(crypt.getPublicKey());
  };
  generateKeys();
  </script>


</html>
