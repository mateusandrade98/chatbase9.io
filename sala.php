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

<!-- Código open source, usando para criptografia RSA usando javascript -->
<!-- Disponível em https://github.com/travist/jsencrypt -->
<script type="text/javascript" src="assets/rsa/jsencrypt.min.js"></script>

  <body>
    <?php

      if(!isset($_GET['id'])){
        ?>
        <div class="alert alert-danger" role="alert">
            <h4>ID inválido.</h4><br>
            <a href="." class="btn btn-outline-danger">Voltar</a>
        </div>
        <?php
        exit;
      }

      $session->destruir_sessao($db);

      if($consultar->existe_admin($db,intval($_GET['id'])) == 0){
        $consultar->criar_novo_admin($db,intval($_GET['id']));
      }

      if($consultar->checar_codigo_em_sala($db,$_SESSION['codigo'] / $secreto,intval($_GET['id'])) == 0){
        ?>
        <div class="alert alert-danger" role="alert">
            <h4>Sem permissão.</h4><br>
            <a href="." class="btn btn-outline-danger">Voltar</a>
        </div>
        <?php
        exit;
      }

      if($consultar->checar_token($db,$_SESSION['token']) == 0){
        header('Location:criarnick.php?r=criar');
        exit;
      }


      $informacoes = $consultar->selecionar_informacoes_sala($db,intval($_GET['id']));
      $informacoes_usuario = $consultar->selecionar_informacoes_pelo_o_token($db,$_SESSION['token']);

      $quantidade_de_usuarios = intval($consultar->quantidade_de_usuarios($db,$informacoes['id']));

      if($quantidade_de_usuarios == 0){
        $consultar->remover_bot($db,intval($_GET['id']));
      }

      $limite_de_usuarios = intval($informacoes['limite']);

      if($quantidade_de_usuarios>=$limite_de_usuarios){
        ?><div class="alert alert-danger" role="alert">
            <h4>Infelizmente está sala excedeu o seu limite.</h4><br>
            <small></small>
            <a href="." class="btn btn-outline-danger">Voltar</a>
        </div>
        <?php
        exit;
      }

      if($consultar->entrar_na_sala($db,intval($_GET['id']),$informacoes_usuario['id']) != 1){
        ?><div class="alert alert-danger" role="alert">
            <h4>Algo de inesperado ocorreu.</h4><br>
            <small></small>
            <a href="." class="btn btn-outline-danger">Voltar</a>
        </div>
        <?php
        exit;
      }

      if(count($informacoes) == 0){
        ?>
        <div class="alert alert-danger" role="alert">
            <h4>ID inválido.</h4><br>
            <a href="." class="btn btn-outline-danger">Voltar</a>
        </div>
        <?php
        exit;
      }


    ?>

    <script>
      document.title = '<?php echo $tratamento->tratar_string($informacoes['nome']); ?>';
      $('.collapse').collapse();
      $(function () {
        $('[data-toggle="tooltip"]').tooltip();
      });
    </script>

<?php $usuarios_na_sala = $consultar->selecionar_usuarios_da_sala($db,$informacoes['id']); ?>
<div class="modal fade" id="modalUsuarios" tabindex="-1" role="dialog" aria-labelledby="modalUsuarios" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle"><i class="fas fa-users"></i> Usuários</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="list-group" id="lista-usuarios">
          <!-- loader -->
          <div class="spinner-grow text-primary text-center" style="width: 3rem; height: 3rem;margin:auto;" role="status">
            <span class="sr-only">Carregando Mensagens...</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modalSair" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalCenterTitle">Deseja sair?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Sua sessão ainda ficará aberta nesta sala.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
        <a href="index.php" class="btn btn-secondary">Sair da sala</a>
      </div>
    </div>
  </div>
</div>

<nav class="navbar navbar-dark bg-primary">
  <a href="#" data-toggle="modal" data-target="#modalSair" class="navbar-brand"><i class="fas fa-arrow-left"></i> <?php echo $tratamento->tratar_string($informacoes['nome']); ?></a>
  <!--<div class="form-inline">
    <a class="btn btn-light pointer"><i class="fas fa-times"></i> Sair</a>
  </div>-->
</nav>
<div class="chat">
  <div class="accordion" id="accordionExample">
    <div class="card">
      <div class="card-header" id="headingOne">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalUsuarios">
          <i class="fas fa-users"></i> <span class="badge badge-light" id="users"><?php echo intval($consultar->quantidade_de_usuarios($db,$informacoes['id'])); ?></span>
        </button>
        <button type="button" onclick="javascript:scroll_to_bottom(1);" id="btNovo" style="display:none;" class="btn btn-primary"><i class="fas fa-comment"></i> <span class="badge badge-light"><st id="textNovo">+0</st></span></button>
        <input type="hidden" name="first" id="first" value="1" />
        <input type="hidden" name="VTscroll" id="VTscroll" value="0" />
        <input type="hidden" name="qntNovo" id="qntNovo" value="0" />
      <button class="btn btn-outline-primary float-right" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        <i class="fas fa-times"></i> Ocultar
      </button>
    </div>
    <div class="chat-scroll">
      <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
        <div class="card-body">
          <ul class="list-group content-fluid" id="chatting">
                <!-- loeader -->
                <div class="spinner-grow text-primary text-center" style="width: 3rem; height: 3rem;margin:auto;" role="status">
                  <span class="sr-only">Carregando Mensagens...</span>
                </div>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="imagemModal" tabindex="-1" role="dialog" aria-labelledby="modalImagemShow" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-body">
      	   <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
           <img src="" class="imagepreview" style="width: 100%;" >
           <a href="" class="btn btn-primary mt-3 container view-full-imagem" target="_blank"><i class="fas fa-image"></i> Ver imagem original</a>
         </div>
       </div>
     </div>
   </div>

  <form class="sender-chat" id="sender" enctype="multipart/form-data">
    <div class="container-fluid">
      <div class="progress mb-2" style="display:none;" id="fullbar">
        <div class="progress-bar" role="progressbar" id="upload-imagem" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">Enviando imagem...</div>
      </div>
      <div class="input-group">
        <input type="text" class="form-control rounded-pill" maxlength="1000" autocomplete="off" placeholder="enviar algo..." value="" name="sender" id="sender-texto">
        <div class="input-group-append">
            <button type="submit" style="opacity:0.7;" id="bt-sender" class="btn p-0 ml-2 text-primary font-size-sender"><i class="fas fa-paper-plane"></i></button>
            </div>
            <div class="flex-basis float-left">
              <input type="file" name="files[]" id="imagensDragons" class="hideFileUpload" accept="image/*" multiple/>
                <div id="files" class="files"></div>
              <button type="button" id="call_file" class="btn text-white bg-secondary ml-1 rounded-circle"><i class="fas fa-camera"></i></button>
          </div>
        </div>
      </div>
    </div>
  </form>

    <!-- \info/ -->
    <?php $qnt_mensagens = $consultar->quantidade_de_mensagens($db,intval($informacoes['id'])); ?>
    <input type="hidden" name="offset" id="offset" value="<?php if($qnt_mensagens > 10){ echo ($qnt_mensagens - 10); }else{ echo 0; } ?>" />
      <input type="hidden" name="usuarios_ativos" id="usuarios_ativos" value='<?php echo json_encode($usuarios_na_sala); ?>' />
        <input type="hidden" name="qnt_usuarios_ativos" id="qnt_usuarios_ativos" value="<?php echo $quantidade_de_usuarios; ?>" />
          <input type="hidden" name="qnt_mensagens" id="qnt_mensagens" value="<?php echo $qnt_mensagens; ?>" />
        <input type="hidden" name="preparado" id="preparado" value="0" />
    <!-- \info/ -->

    <!-- keys -->
    <input type="hidden" name="pubkey" id="pubkey" value="<?php echo $_SESSION['pubkey']; ?>" />
    <input type="hidden" name="privkey" id="privkey" value="<?php echo $_SESSION['privkey']; ?>" />
    <!-- -->

          <div aria-live="polite" aria-atomic="true">
      <div class="em-loa" id="append-toast">
    </div>
      </div>
    <script>

    function mudar_offset(){
      var offset = $('#offset');
      var offset_value = 0;
      if(offset.val() > 0){
        offset_value = offset.val() - 10;
      }else{
        offset_value = 0;
      }
      if(offset_value < 0){
        offset_value = 0;
      }
      offset.val(offset_value);
      atualizar_chat();
    }

    function append_toastDefault(texto){
      var toast = $("#append-toast");
      var id_sala = <?php echo $informacoes['id']; ?>;
      $.post("ajax/toast-default.php",{id_sala:id_sala,texto:texto},function(data){
        if(data!=''){
          toast.html(data);
          $('.toast').toast('show');
        }
      });
    }

    function append_toast(id_usuario,texto){
      var toast = $("#append-toast");
      var id_sala = <?php echo $informacoes['id']; ?>;
      $.post("ajax/toast.php",{id_usuario:id_usuario,id_sala:id_sala,texto:texto},function(data){
        if(data!=''){
          toast.html(data);
          $('.toast').toast('show');
        }
      });
    }

    $(".chat-scroll").scroll(function(){
      var isEnd = ($(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight);
      if(isEnd){
        $("#btNovo").fadeOut();
        $("#textNovo").html('+0');
        $("#first").val(0);
      }
      $("#VTscroll").val(isEnd);
    });

    $("#imagensDragons").change('change', function(){

    });

    function scroll_to_bottom(isButton=0){
      $(".chat-scroll").scrollTop($('#chatting')[0].scrollHeight);
      $(document.body).scrollTop($(document.body)[0].scrollHeight);
      if(isButton == 1){
        $("#btNovo").hide();
        $("#qntNovo").val(0);
      }
    }

    function show_full_imagem(data){
      $('.imagepreview').attr('src', data);
      $('.view-full-imagem').attr('href',data);
      $('#imagemModal').modal('show');
    }

    function showImagem(id_imagem){
      if($("#img_"+id_imagem).length == 0){
        $.post("ajax/carregar-imagem.php",{id_imagem:id_imagem,id_sala:<?php echo $informacoes['id']; ?>},function(data){
          if(data!=''){
            $("#"+id_imagem).html('<a onclick="javascript:show_full_imagem(\''+data+'&tipo=1\');" href="#'+data+'&tipo=1" target="_self" class="preview"><img src="" id="img_'+id_imagem+'" class="max-imagem rounded-2 preview-imagem" width="" height="" /></a>');
            $("#img_"+id_imagem).attr('src',data);
          }
        });
      }
    }

    function scroll_bottom(){
      var height =  $("#chatting").offset().top;
      var qnt = $("#qnt_mensagens").val();
      var first = $("#first").val();
      if(height > 0 && qnt >= 10 && first == 0){
        //append_toastDefault('+1 mensagem');
        //$(".chat-scroll").scrollTop($('#chatting')[0].scrollHeight);
        $("#btNovo").fadeIn();
        var VqntNovo = parseInt($("#qntNovo").val());
        var qntNovo = VqntNovo + 1
        $("#textNovo").html('+'+qntNovo);

        $("#first").val(0);
        $("#qntNovo").val(qntNovo);
      }
    }

    function atualizar_lista(){
      var lista = $("#lista-usuarios");
      $.post("ajax/lista.php",{id_sala:<?php echo $informacoes['id']; ?>},function(data){
        if(data!=''){
          lista.html(data);
        }
      });
    }

    function atualizar_digitando(Id,Nick,Avatar){
      var digit = $(".digitando");
      var myID = <?php echo $informacoes_usuario['id']; ?>;
      digit.html('');
      if(Id > 0){
        if(Id != myID){
          html = '<li class="list-group pt-1 pb-1">'+
            '<div class="d-flex flex-row">'+
              '<div class=""><img class="rounded-circle align-self-center ml-2" width="40px" height="40px" src="imagem/'+Avatar+'"  /></div>'+
              '<div class="align-self-center ml-2 bd-highlight  pt-3 pr-3 bg-white shadow rounded-2">'+
                '<h5 class="text-body ml-2">'+Nick+'</h5>'+
                '<p class="ml-3 text-justify font-weight-bold">Está digitando...</p>'+
              '</div>'+
            '</div>'+
          '</li>'+
          '<script>$("#bobo").fadeIn("slow", function() {'+
            '// animação completa'+
          '});<\/script>';

          digit.html(html);
          //scroll_bottom();
        }
      }
    }

    function sapo(){
      $(".encrypted").each(function() {
        if($(this).hasClass('encrypted')){
          var descriptografado = decrypt($(this).text());
          if(descriptografado != ''){
            $(this).html(descriptografado);
            $(this).removeClass('encrypted');
          }else{
            $(this).html('Mensagem criptografada.');
            $(this).css({'opacity':'0.5'});
          }
        }
      });
    }
    //setInterval(sapo,1000);

    function atualizar_chat(){
      var chat = $("#chatting");
      $.post("ajax/loader.php",{id_sala:<?php echo $informacoes['id']; ?>,offset:$('#offset').val(),primeiro_acesso:1,preparado:$('#preparado').val(),quantidade_mensagens:$("#qnt_mensagens").val()},function(data){
        if(data!=''){
          chat.html(data);
          scroll_bottom();
          if($("#preparado").val() == 0){
            $("#preparado").val(1);
          }
          //sapo();
        }
      });
    }

    <?php /*if($informacoes['id_admin'] == $informacoes_usuario['id'] || $informacoes_usuario['ismaster'] == 1){*/ ?>
      function banir_usuario(id_usuario){
        var id_sala = <?php echo $informacoes['id']; ?>;
        $.post("ajax/banir.php",{id_usuario:id_usuario,id_sala:id_sala},function(data){
          if(data!=null && data!=''){
            if(data['erro'] == 0){
             $('#usuario_'+id_usuario).fadeOut("slow");
             append_toastDefault('Um usuário foi banido.');
             atualizar_lista();
           }else{
             alert(data['mensagem']);
             window.location='index.php?r=';
           }
          }
        });
      }

    <?php /*}*/ ?>

    function aplicar_modificacoes(usuarios_ativos,qnt_usuarios_ativos,qnt_mensagens){
      var ultima_usuarios_ativos = $("#usuarios_ativos").val();
      var ultima_qnt_usuarios_ativos = $("#qnt_usuarios_ativos").val();
      var ultima_qnt_mensagens = $("#qnt_mensagens").val();

      var obj_ultimo_usuarios = JSON.parse(ultima_usuarios_ativos);
      var obj_novo_usuarios = JSON.parse(usuarios_ativos);

      if(qnt_usuarios_ativos != ultima_qnt_usuarios_ativos){
        $("#qnt_usuarios_ativos").val(qnt_usuarios_ativos);
        $("#users").html(qnt_usuarios_ativos);
        $("#usuarios_ativos").val(usuarios_ativos);
        if(qnt_usuarios_ativos < ultima_qnt_usuarios_ativos){
          append_toastDefault('Um usuário saiu.');
        }
        atualizar_lista();
      }

      if(qnt_mensagens != ultima_qnt_mensagens){
        var offset_value = $('#offset').val();
        if(qnt_mensagens > ultima_qnt_mensagens){
          if(qnt_mensagens > 10){
            offset_value = parseInt(offset_value) + (qnt_mensagens - ultima_qnt_mensagens);
          }else{
            offset_value = 0;
          }
        }else{
          if(qnt_mensagens > 10){
            offset_value = parseInt(offset_value) - (ultima_qnt_mensagens - qnt_mensagens);
          }else{
            offset_value = 0;
            if(offset_value < 0){
              offset_value = 0;
            }
          }
        }
        if(offset_value < 0){
          offset_value = 0;
        }
        $("#qnt_mensagens").val(qnt_mensagens);
        $('#offset').val(offset_value);
        atualizar_chat();
      }

      ultimas_lista_id = [];
      novos_lista_id = [];

      nova_quantidade_de_usuarios = obj_novo_usuarios.length - obj_ultimo_usuarios.length;
      if(nova_quantidade_de_usuarios > 0){
        for(i = ultima_qnt_usuarios_ativos;i < obj_novo_usuarios.length; i++){
          append_toast(obj_novo_usuarios[i]['id_usuario'],'Entrou na sala.');
          $("#usuarios_ativos").val(usuarios_ativos);
        }
      }
    }

    function atualizar_status(){
     $.post("ajax/status.php",{id_sala:<?php echo $informacoes['id']; ?>},function(data){
       if(data!=null && data!=''){
         if(data['erro'] == 0){
           digitando_json = JSON.parse(data['digitando']);
            if(digitando_json.length > 0){
              atualizar_digitando(digitando_json[0],digitando_json[1],digitando_json[2]);
            }else{
              atualizar_digitando(0,'','');
            }
          aplicar_modificacoes(data['usuarios_ativos'],data['qnt_usuarios_ativos'],data['qnt_mensagens']);
        }else{
          alert(data['mensagem']);
          window.location='index.php?r=';
        }
       }
     });
    }

     /*checar informações*/
     setInterval(atualizar_status, 1000);
     /**/

     $("#sender").keyup(function(){
       $.post("ajax/digitando.php",{id_sala:<?php echo $informacoes['id']; ?>},function(data) {
         if($("#sender-texto").val() == ""){
           $('#bt-sender').css({opacity:'0.7'});
         }else{
           $('#bt-sender').css({opacity:'1'});
         }
       });
     });

     function sender_imagem(url,thumbnailUrl,nome){
       $.post("ajax/sender-imagem.php",{url:url,thumbnailUrl:thumbnailUrl,nome:nome,id_sala:<?php echo $informacoes['id']; ?>},function(data) {
           if(data['erro'] == 0){
             //atualizar_status();
             $.post("ajax/loader.php",{id_sala:<?php echo $informacoes['id']; ?>,quantidade_mensagens:$("#qnt_mensagens").val()},function(data) {
                 if(data['erro'] != ''){
                   $("#chatting").append(data);
                   $(".chat-scroll").scrollTop($('#chatting')[0].scrollHeight);
                 }else{
                     alert(data['mensagem']);
                     window.location='index.php?r=';
                 }
             });
           }else{
             if(data['mensagem']!='Impossível enviar mensagem vázia.'){
               alert(data['mensagem']);
               window.location='index.php?r=';
             }
           }
       });
     }

     $('#call_file').click(function(){
        $('#imagensDragons').click();
      });

      $("#sender").submit(function(event) {
        var texto = $("#sender-texto");
        var valor = texto.val();
        texto.val("");
        if(valor.length > 0){
          valor_encriptado = encrypt(valor);
        }else{
          valor_encriptado = '';
        }
        $.post("ajax/sender.php",{texto:valor,id_sala:<?php echo $informacoes['id']; ?>},function(data) {
            if(data['erro'] == 0){
              //atualizar_status();
              $.post("ajax/loader.php",{id_sala:<?php echo $informacoes['id']; ?>,quantidade_mensagens:$("#qnt_mensagens").val()},function(data) {
                  if(data['erro'] != ''){
                    $("#chatting").append(data);
                    $('#bt-sender').css({opacity:'0.7'});
                    $(".chat-scroll").scrollTop($('#chatting')[0].scrollHeight);
                  }else{
                      alert(data['mensagem']);
                      window.location='index.php?r=';
                  }
              });
            }else{
              if(data['mensagem']!='Impossível enviar mensagem vázia.'){
                alert(data['mensagem']);
                window.location='index.php?r=';
              }
            }
        });
        event.preventDefault();
      });

      atualizar_chat();
      atualizar_lista();

    </script>

    <!-- Código open source para envio de arquivos usando AJAX, usando o JQuery -->
    <!-- Disponível em https://github.com/blueimp/jQuery-File-Upload -->
    <script type="text/javascript" src="assets/js/jquery-ui.js"></script>
    <script type="text/javascript" src="assets/js/jquery.fileupload.js"></script>
    <script type="text/javascript" src="assets/js/jquery.iframe-transport.js"></script>

    <script>
    $(function () {
            'use strict';

            var url = 'ajax/upload.php';
            $('#imagensDragons').fileupload({
                url: url,
                dataType: 'json',
                done: function (e, data) {
                    $("#fullbar").fadeOut();
                    $.each(data.result.files, function (index, file) {
                      //url,thumbnailUrl,nome
                      sender_imagem(file.url,file.thumbnailUrl,file.name);
                    });
                },
                progressall: function (e, data) {
                    $("#fullbar").fadeIn();
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    if(progress > 0){
                      $("#upload-imagem").css({width:progress+"%"});
                    }
                }
            }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');
        });
    </script>


    <script>
      var secreto = <?php echo $secreto; ?>;

      function encrypt(texto){
        const crypt = new JSEncrypt();
        crypt.setPublicKey($('#pubkey').val());
        var encrypted = crypt.encrypt(texto);
        if(encrypted){
          return encrypted;
        }else{
          return '';
        }
      }

      function decrypt(texto){
        const crypt = new JSEncrypt();
        crypt.setPrivateKey($('#privkey').val());
        var decrypted = crypt.decrypt(texto);
        if(decrypted){
          return decrypted;
        }else{
          return '';
        }
      }

    </script>

  </body>
</html>
