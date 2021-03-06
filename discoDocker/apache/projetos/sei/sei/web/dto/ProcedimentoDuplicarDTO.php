<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 20/01/2009 - criado por mga
*
* Vers?o do Gerador de C?digo: 1.25.0
*
* Vers?o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class ProcedimentoDuplicarDTO extends InfraDTO {

  public function getStrNomeTabela() {
    return null;
  }

  public function montar() {
    $this->adicionarAtributo(InfraDTO::$PREFIXO_DBL,'IdProcedimento');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR,'IdDocumentosProcesso');
    
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdOrgaoUsuario');    
    $this->adicionarAtributo(InfraDTO::$PREFIXO_NUM,'IdInteressado');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'SinProcessosRelacionados');
  }
}
?>