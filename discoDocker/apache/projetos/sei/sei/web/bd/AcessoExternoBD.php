<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 10/06/2010 - criado por fazenda_db
*
* Vers?o do Gerador de C?digo: 1.29.1
*
* Vers?o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class AcessoExternoBD extends InfraBD {

  public function __construct(InfraIBanco $objInfraIBanco){
  	 parent::__construct($objInfraIBanco);
  }

}
?>