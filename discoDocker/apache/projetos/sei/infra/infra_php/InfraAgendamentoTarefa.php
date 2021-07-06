<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4Њ REGIУO
 *
 * 12/03/2013 - criado por MGA
 *
 * @package infra_php
 */


/*
CREATE TABLE infra_agendamento_tarefa
(
   id_infra_agendamento_tarefa int PRIMARY KEY NOT NULL,
   descricao varchar(500) NOT NULL,
   comando varchar(255) NOT NULL,
   sta_periodicidade_execucao char(1) NOT NULL,
   periodicidade_complemento varchar(100) NOT NULL,
   dth_ultima_execucao datetime,
   dth_ultima_conclusao datetime,
   sin_sucesso char(1) NOT NULL,
   parametro varchar(250),
   email_erro varchar(250),
   sin_ativo char(1) NOT NULL
);

CREATE UNIQUE INDEX PRIMARY ON infra_agendamento_tarefa(id_infra_agendamento_tarefa);
*/


abstract class InfraAgendamentoTarefa  {

  public static $IND_MINUTO = 'MINUTO';
  public static $IND_HORA = 'HORA';
  public static $IND_DIA_SEMANA = 'DIA_SEMANA';
  public static $IND_DIA_MES = 'DIA_MES';
  public static $IND_MES = 'MES';

  //public abstract static function getInstance();

  public function __construct(InfraConfiguracao $objInfraConfiguracao, InfraSessao $objInfraSessao, InfraIBanco $objInfraIBanco, InfraLog $objInfraLog){
    ConfiguracaoInfra::setObjInfraConfiguracao($objInfraConfiguracao);
    SessaoInfra::setObjInfraSessao($objInfraSessao);
    BancoInfra::setObjInfraIBanco($objInfraIBanco);
    LogInfra::setObjInfraLog($objInfraLog);
  }

  public function executar($strEmailErroRemetente = null, $strEmailErroDestinatario = null) {
    try {

      //////////////////////////////////////////////////////////////////////////////
      //InfraDebug::getInstance()->setBolLigado(false);
      //InfraDebug::getInstance()->setBolDebugInfra(true);
      //InfraDebug::getInstance()->limpar();
      //////////////////////////////////////////////////////////////////////////////

      // busca lista de tarefas ativas
      $objInfraAgendamentoTarefaDTO = new InfraAgendamentoTarefaDTO();
      $objInfraAgendamentoTarefaDTO->retTodos();

      $objInfraAgendamentoTarefaDTO->setStrSinAtivo('S');

      $objInfraAgendamentoTarefaRN = new InfraAgendamentoTarefaRN();
      $arrObjInfraAgendamentoTarefaDTO = $objInfraAgendamentoTarefaRN->listar($objInfraAgendamentoTarefaDTO);

      $arrDataHoraAtual = array(self::$IND_MINUTO => date('i'),
          self::$IND_HORA => date('G'),
          self::$IND_DIA_SEMANA => date('N'),
          self::$IND_DIA_MES => date('j'),
          self::$IND_MES => date('n'));

      foreach($arrObjInfraAgendamentoTarefaDTO as $objInfraAgendamentoTarefaDTO){
        /* @var $objInfraAgendamentoTarefaDTO InfraAgendamentoTarefaDTO */

        // verifica condiчуo de execuчуo
        $bolExecutar = false;

        if($objInfraAgendamentoTarefaDTO->getStrStaPeriodicidadeExecucao() == InfraAgendamentoTarefaRN::$PERIODICIDADE_EXECUCAO_MINUTO){

          $arrMinutoExecucao = explode(',', $objInfraAgendamentoTarefaDTO->getStrPeriodicidadeComplemento());
          // se o minuto estiver no periodicidade complemento nуo executa a tarefa
          if(in_array($arrDataHoraAtual[self::$IND_MINUTO], $arrMinutoExecucao)){
            $bolExecutar = true;
          }

        //verificar somente em hora cheia
        }else if (intval($arrDataHoraAtual[self::$IND_MINUTO]) == 0){

          switch($objInfraAgendamentoTarefaDTO->getStrStaPeriodicidadeExecucao()){


            case InfraAgendamentoTarefaRN::$PERIODICIDADE_EXECUCAO_HORA:
              $arrHoraExecucao = explode(',', $objInfraAgendamentoTarefaDTO->getStrPeriodicidadeComplemento());
              // se a hora estiver no periodicidade complemento executa a tarefa
              if(in_array($arrDataHoraAtual[self::$IND_HORA], $arrHoraExecucao)){
                $bolExecutar = true;
              }
              break;

            case InfraAgendamentoTarefaRN::$PERIODICIDADE_EXECUCAO_DIA_SEMANA:
              $arrDiaHoraExecucao = explode(',', $objInfraAgendamentoTarefaDTO->getStrPeriodicidadeComplemento());
              // se dia da semana/hora estiver no periodicidade complemento executa a tarefa
              if(in_array($arrDataHoraAtual[self::$IND_DIA_SEMANA].'/'.$arrDataHoraAtual[self::$IND_HORA], $arrDiaHoraExecucao)){
                $bolExecutar = true;
              }
              break;

            case InfraAgendamentoTarefaRN::$PERIODICIDADE_EXECUCAO_DIA_MES:
              $arrDiaHoraExecucao = explode(',', $objInfraAgendamentoTarefaDTO->getStrPeriodicidadeComplemento());
              // se dia do mъs/hora estiver no periodicidade complemento executa a tarefa
              if(in_array($arrDataHoraAtual[self::$IND_DIA_MES].'/'.$arrDataHoraAtual[self::$IND_HORA], $arrDiaHoraExecucao)){
                $bolExecutar = true;
              }
              break;

            case InfraAgendamentoTarefaRN::$PERIODICIDADE_EXECUCAO_DIA_ANO:
              $arrDiaMesHoraExecucao = explode(',', $objInfraAgendamentoTarefaDTO->getStrPeriodicidadeComplemento());
              // se dia do mъs/mъs/hora estiver no periodicidade complemento executa a tarefa
              if(in_array($arrDataHoraAtual[self::$IND_DIA_MES].'/'.$arrDataHoraAtual[self::$IND_MES].'/'.$arrDataHoraAtual[self::$IND_HORA], $arrDiaMesHoraExecucao)){
                $bolExecutar = true;
              }
              break;

            default:
              break;
          }
        }

        //executa, se necessсrio
        if($bolExecutar){

          try{
            $objInfraAgendamentoTarefaRN->executar($objInfraAgendamentoTarefaDTO);
          }catch(Exception $e){

            $strAssunto = 'Agendamento FALHOU';

            $strErro = '';
            $strErro .= 'Servidor: '.gethostname()."\n\n";
            $strErro .= 'Data/Hora: '.InfraData::getStrDataHoraAtual()."\n\n";
            $strErro .= 'Comando: '.$objInfraAgendamentoTarefaDTO->getStrComando().'('.$objInfraAgendamentoTarefaDTO->getStrParametro().')'."\n\n";
            $strErro .= 'Erro: '.InfraException::inspecionar($e);

            LogInfra::getInstance()->gravar($strAssunto."\n\n".$strErro);

            if(!is_null($strEmailErroRemetente)){

              if (!is_null($objInfraAgendamentoTarefaDTO->getStrEmailErro())){
                InfraMail::enviarConfigurado(ConfiguracaoInfra::getInstance(), $strEmailErroRemetente, $objInfraAgendamentoTarefaDTO->getStrEmailErro(), null, null, $strAssunto, $strErro);
              }else if (!is_null($strEmailErroDestinatario)){
                InfraMail::enviarConfigurado(ConfiguracaoInfra::getInstance(), $strEmailErroRemetente, $strEmailErroDestinatario, null, null, $strAssunto, $strErro);
              }
            }
          }
        }
      }
    }catch(Exception $e){

      $strAssunto = 'Erro executando agendamentos.';
      $strErro = InfraException::inspecionar($e);

      LogInfra::getInstance()->gravar($strAssunto."\n\n".$strErro);

      if (!is_null($strEmailErroRemetente) && !is_null($strEmailErroDestinatario)){
        InfraMail::enviarConfigurado(ConfiguracaoInfra::getInstance(), $strEmailErroRemetente, $strEmailErroDestinatario, null, null, $strAssunto, $strErro);
      }
    }
  }
}
?>