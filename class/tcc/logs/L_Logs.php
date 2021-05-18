<?php

class Logs {

  private $acao;

  private $tccid;

  private $userid;

  private $groupid;

  private $tipo_log;

  private $nome_arquivo;

  private $caminho_arquivo;

  private $timecreated;

  private $timemodified;

  public function __construct($tccid, $userid, $groupid, $tipo_log){
    $this->tccid = $tccid;

    $this->userid = $userid;

    $this->groupid = $groupid;

    $this->tipo_log = $tipo_log;
  }

  public function log($acao, $nome_arquivo= "",  $caminho_arquivo = "" ){
    global $DB;
    $log = array();

    $log['tccid'] = $this->tccid;
    $log['userid'] = $this->userid;
    $log['groupid'] = $this->groupid;
    $log['acao'] = $acao;
    $log['nome_arquivo'] = $nome_arquivo;
    $log['caminho_arquivo'] = $caminho_arquivo;
    $log['tipo_log'] = $this->tipo_log;
    $log['timecreated'] = time();

    $id = $DB->insert_record('tcccafeead_logs_sistema', $log, true);
  }





}

?>
