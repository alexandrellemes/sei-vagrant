<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 20/12/2007 - criado por mga
*
* Vers?o do Gerador de C?digo: 1.12.0
*
* Vers?o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class MudarNivelAcessoDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return null;
  }

  public function montar() {
  	$this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'StaOperacao');
  	$this->adicionarAtributo(InfraDTO::$PREFIXO_DBL,'IdProtocolo');
  	$this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'StaNivel');
  	$this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'SinLancarAndamento');
  }
}
?>