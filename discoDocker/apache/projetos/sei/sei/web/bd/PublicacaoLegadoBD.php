<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 20/11/2013 - criado por mkr@trf4.jus.br
*
* Vers?o do Gerador de C?digo: 1.33.1
*
* Vers?o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class PublicacaoLegadoBD extends InfraBD {

  public function __construct(InfraIBanco $objInfraIBanco){
  	 parent::__construct($objInfraIBanco);
  }

}
?>