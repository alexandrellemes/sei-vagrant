<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 09/05/2013 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class ReplicacaoContextoDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return null;
  }

  public function montar() {
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'StaOperacao');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdContexto');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdSistema');
  }
}
?>