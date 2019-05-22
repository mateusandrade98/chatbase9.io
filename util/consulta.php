<?php
class consultar
{
  function selecionar_info_robo($db,$id_robo){
    $sql = 'select * from bot where id=:id_robo';
    $st = $db->prepare($sql);
    $st->bindValue(':id_robo',$id_robo,PDO::PARAM_INT);
    $st->execute();
    return $st->fetch(PDO::FETCH_ASSOC);
  }

  function remover_bot($db,$id_sala){
    $sql = 'delete from mensagens where id_bot!=0 and id_sala=:id_sala';

      $st=$db->prepare($sql);
      $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
      $st->execute();

    return $st->rowCount();
  }

  function atualizar_abrido($db,$id_usuario,$id_imagem){
    $imagem = $this->selecionar_imagem($db,$id_imagem);
    $explode = explode('|',$imagem['abrido']);
    $adicionar = true;
    foreach ($explode as $id) {
      if($id == $id_usuario){
        $adicionar = false;
      }
    }
    if($adicionar == false){
      return 0;
    }
    $opened = $imagem['abrido'].$id_usuario.'|';
    $sql = 'update imagens set abrido=:abrido where id_imagem=:id_imagem';
    $st = $db->prepare($sql);
    $st->bindValue(':abrido',$opened,PDO::PARAM_STR);
    $st->bindValue(':id_imagem',$id_imagem,PDO::PARAM_STR);
    $st->execute();
    return $st->rowCount();
  }

  function selecionar_imagem($db,$id_imagem){
    $sql = "select * from imagens where id_imagem=:id_imagem";
    $st = $db->prepare($sql);
    $st->bindValue(":id_imagem",$id_imagem,PDO::PARAM_STR);
    $st->execute();
    return $st->fetch(PDO::FETCH_ASSOC);
  }

  function imagem_esta_na_sala($db,$id_imagem,$id_sala){
    $sql = "select * from imagens where id_imagem=:id_imagem and id_sala=:id_sala";
    $st = $db->prepare($sql);
    $st->bindValue(":id_imagem",$id_imagem,PDO::PARAM_STR);
    $st->bindValue(":id_sala",$id_sala,PDO::PARAM_STR);
    $st->execute();
    return $st->fetch(PDO::FETCH_ASSOC);
  }

  function enviar_imagem($db,$nome,$id_sala,$id_imagem,$informacoes){
    $sql = "insert into imagens (nome,id_sala,id_imagem,id_usuario,abrido) values (:nome,:id_sala,:id_imagem,:id_usuario,:abrido)";
    $st = $db->prepare($sql);
    $st->bindValue(":nome",$nome,PDO::PARAM_STR);
    $st->bindValue(":id_sala",$id_sala,PDO::PARAM_INT);
    $st->bindValue(":id_imagem",$id_imagem,PDO::PARAM_INT);
    $st->bindValue(":id_usuario",$informacoes['id'],PDO::PARAM_INT);
    $st->bindValue(":abrido",$informacoes['id'].'|',PDO::PARAM_STR);
    $st->execute();
    return $st->rowCount();
  }

  function enviar_messagem($db,$id_sala,$texto,$informacoes,$isImagem=0,$id_imagem=0,$robo=0){
    $sql = "insert into mensagens (id_usuario,id_sala,id_imagem,texto,isImagem,id_bot) values (:id_usuario,:id_sala,:id_imagem,:texto,:isImagem,:id_bot)";
    $st = $db->prepare($sql);
    $st->bindValue(":id_usuario",$informacoes['id'],PDO::PARAM_INT);
    $st->bindValue(":id_sala",$id_sala,PDO::PARAM_INT);
    $st->bindValue(":id_imagem",$id_imagem,PDO::PARAM_INT);
    $st->bindValue(":texto",$texto,PDO::PARAM_STR);
    $st->bindValue(":isImagem",$isImagem,PDO::PARAM_INT);
    $st->bindValue(":id_bot",$robo,PDO::PARAM_INT);
    $st->execute();
    return $st->rowCount();
  }

  function checar_codigo_em_sala($db,$codigo,$id_sala){
    $sql = 'select codigo from salas where codigo=:codigo and id=:id_sala';
    $st = $db->prepare($sql);
    $st->bindValue(':codigo',$codigo,PDO::PARAM_INT);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->execute();
    return $st->rowCount();
  }

  function atualizar_digitacao($db,$id_usuario,$id_sala){
    $sql = 'update salas set id_digitando=:id_usuario where id=:id_sala';
    $st = $db->prepare($sql);
    $st->bindValue(':id_usuario',$id_usuario,PDO::PARAM_INT);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->execute();
    return $st->rowCount();
  }

  function remover_digitando($db,$id_sala){
    $sql = 'update salas set id_digitando=0 where id=:id_sala';
    $st = $db->prepare($sql);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->execute();
    return $st->rowCount();
  }

  function banir_usuario($db,$id_usuario,$id_sala){
    $sql[0] = 'delete from grupos where id_usuario=:id_usuario and id_sala=:id_sala';
    $sql[1] = 'delete from mensagens where id_usuario=:id_usuario and id_sala=:id_sala';

    for($i=0;$i<2;$i++){
      $st=$db->prepare($sql[$i]);
      $st->bindValue(':id_usuario',$id_usuario,PDO::PARAM_INT);
      $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
      $st->execute();
    }
    return $st->rowCount();
  }

  function existe_admin($db,$id_sala){
    $informacoes_da_sala = $this->selecionar_informacoes_sala($db,$id_sala);
    return $this->esta_na_sala($db,$informacoes_da_sala['id_admin'],$id_sala);
  }

  function usuario_E_admin($db,$id_usuario,$id_sala){
    $sql = 'select codigo from salas where id=:id_sala and id_admin=:id_usuario';
    $st = $db->prepare($sql);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->bindValue(':id_usuario',$id_usuario,PDO::PARAM_INT);
    $st->execute();
    return $st->rowCount();
  }

  function criar_novo_admin($db,$id_sala){
    if($this->quantidade_de_usuarios($db,$id_sala) > 0){
      $novo_admin = $this->selecionar_usuarios_da_sala($db,$id_sala,'asc')[0]['id_usuario'];
      $sql = 'update salas set id_admin=:id_admin where id=:id_sala';
      $st = $db->prepare($sql);
      $st->bindValue(':id_admin',$novo_admin,PDO::PARAM_INT);
      $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
      $st->execute();
    }
  }

  function destruir_sessao($db,$id_usuario){
    return 1;
    $sql[0] = 'delete from usuarios where id=:id_usuario';
    $sql[1] = 'delete from grupos where id_usuario=:id_usuario';
    $sql[2] = 'delete from mensagens where id_usuario=:id_usuario';
    $sql[3] = 'delete from imagens where id_usuario=:id_usuario';

    for($i=0;$i<4;$i++){
      if($i==3){
        $s = 'select * from imagens where id_usuario=:id_usuario';
        $q = $db->prepare($s);
        $q->bindValue(':id_usuario',$id_usuario,PDO::PARAM_INT);
        $q->execute();
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
          if(file_exists('uploads/'.$r['nome'])){
            unlink(__DIR__.'/../uploads/'.$r['nome']);
            unlink(__DIR__.'/../uploads/thumbnail/'.$r['nome']);
          }
        }
      }
      $st=$db->prepare($sql[$i]);
      $st->bindValue(':id_usuario',$id_usuario,PDO::PARAM_INT);
      $st->execute();
    }
    return $st->rowCount();
  }

  function checar_codigo($db,$codigo){
    $sql = 'select codigo from salas where codigo=:codigo';
    $st = $db->prepare($sql);
    $st->bindValue(':codigo',$codigo,PDO::PARAM_INT);
    $st->execute();
    return $st->rowCount();
  }

  function selecionar_lista($db,$codigo){
    $sql = 'select * from salas where codigo=:codigo order by relevancia DESC';
    $st = $db->prepare($sql);
    $st->bindValue(':codigo',$codigo,PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  function checar_token($db,$token){
    $sql = 'select token from usuarios where token=:token';
    $st = $db->prepare($sql);
    $st->bindValue(':token',$token,PDO::PARAM_STR);
    $st->execute();
    return $st->rowCount();
  }

  function selecionar_informacoes_pelo_o_token($db,$token){
    $sql = 'select * from usuarios where token=:token';
    $st = $db->prepare($sql);
    $st->bindValue(':token',$token,PDO::PARAM_STR);
    $st->execute();
    return $st->fetch(PDO::FETCH_ASSOC);
  }

  function selecionar_informacoes_sala($db,$id_sala){
    $sql = 'select * from salas where id=:id_sala order by relevancia desc';
    $st = $db->prepare($sql);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->execute();
    return $st->fetch(PDO::FETCH_ASSOC);
  }

  function quantidade_de_usuarios($db,$id_sala){
    $sql = 'select * from grupos where id_sala=:id_sala';
    $st = $db->prepare($sql);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->execute();
    return $st->rowCount();
  }

  function selecionar_ultimas_conversas($db,$id_sala,$offset){
    $limit = $this->quantidade_de_mensagens($db,$id_sala);
    $sql = 'select * from mensagens where id_sala=:id_sala order by id ASC limit :limit offset :offset';
    $st = $db->prepare($sql);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->bindValue(':offset',$offset,PDO::PARAM_INT);
    $st->bindValue(':limit',$limit,PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  function selecionar_informacoes_do_usuario($db,$id_usuario){
    $sql = 'select * from usuarios where id=:id_usuario';
    $st = $db->prepare($sql);
    $st->bindValue(':id_usuario',$id_usuario,PDO::PARAM_INT);
    $st->execute();
    return $st->fetch(PDO::FETCH_ASSOC);
  }

  function entrar_na_sala($db,$id_sala,$id_usuario){
    $sql = 'select * from grupos where id_sala=:id_sala and id_usuario=:id_usuario';
    $st = $db->prepare($sql);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->bindValue(':id_usuario',$id_usuario,PDO::PARAM_INT);
    $st->execute();
    if($st->rowCount() == 0){
      $sql = 'insert into grupos (id_usuario,id_sala) values (:id_usuario,:id_sala)';
      $st = $db->prepare($sql);
      $st->bindValue(':id_usuario',intval($id_usuario),PDO::PARAM_INT);
      $st->bindValue(':id_sala',intval($id_sala),PDO::PARAM_INT);
      $st->execute();
      return $st->rowCount();
    }else {
      return 1;
    }
  }

  function selecionar_usuarios_da_sala($db,$id_sala,$order=''){
    $sql = 'select * from grupos where id_sala=:id_sala order by id '.$order;
    $st = $db->prepare($sql);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  function esta_na_sala($db,$id_usuario,$id_sala){
    $sql = 'select * from grupos where id_usuario=:id_usuario and id_sala=:id_sala';
    $st = $db->prepare($sql);
    $st->bindValue(':id_usuario',$id_usuario,PDO::PARAM_INT);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->execute();
    return $st->rowCount();
  }

  function append_mensagem($db,$id_sala,$quantidade_mensagens){
    $sql = 'select * from mensagens where id_sala=:id_sala order by id ASC limit :limit offset :offset';
    $st = $db->prepare($sql);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->bindValue(':offset',$quantidade_mensagens,PDO::PARAM_INT);
    $st->bindValue(':limit',10,PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  function quantidade_de_mensagens($db,$id_sala){
    $sql = 'select * from mensagens where id_sala=:id_sala';
    $st = $db->prepare($sql);
    $st->bindValue(':id_sala',$id_sala,PDO::PARAM_INT);
    $st->execute();
    return $st->rowCount();
  }
}
?>
