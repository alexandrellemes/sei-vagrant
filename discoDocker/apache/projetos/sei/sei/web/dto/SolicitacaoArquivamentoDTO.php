<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 31/01/2008 - criado por marcio_db
*
* Vers?o do Gerador de C?digo: 1.13.1
*
* Vers?o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class SolicitacaoArquivamentoDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return null;
  }

  public function montar() {  
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdUnidade');
  	$this->adicionarAtributo(InfraDTO::$PREFIXO_ARR,'ObjProtocoloDTO');
  }
}
?>