<?php
class sessao{
  function caminho_da_sessao(){
    return session_save_path();
  }

  function checar_existe_sessao($token){
    $path = $this->caminho_da_sessao();
    return file_exists($path.'/sess_'.$token);
  }

  function destruir_($db,$id_usuario){
    $sql[0] = 'delete from usuarios where id=:id_usuario';
    $sql[1] = 'delete from grupos where id_usuario=:id_usuario';
    $sql[2] = 'delete from mensagens where id_usuario=:id_usuario';

    for($i=0;$i<3;$i++){
      $st=$db->prepare($sql[$i]);
      $st->bindValue(':id_usuario',$id_usuario,PDO::PARAM_STR);
      $st->execute();
    }
  }

  function destroi_sessao_duplicada($db,$session_id){
      $sql = 'select session_id,id from usuarios where session_id=:session_id';
      $st = $db->prepare($sql);
      $st->bindValue(':session_id',$session_id,PDO::PARAM_STR);
      $st->execute();
      if($st->rowCount() > 0){
        while($session=$st->fetch(PDO::FETCH_ASSOC)){
          $this->destruir_($db,$session['id']);
        }
      }
  }

  function destruir_sessao($db){
    $sql = 'select * from usuarios';
    $st = $db->prepare($sql);
    $st->execute();
    if($st->rowCount() > 0){
      $sess = $st->fetchAll(PDO::FETCH_ASSOC);
      foreach ($sess as $session) {
          if($this->checar_existe_sessao($session['session_id']) == False){
            $this->destruir_($db,$session['id']);
          }
        }
      }
    }
  }
?>
