<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 01/12/2011 - criado por bcu
*
* Vers�o do Gerador de C�digo: 1.32.1
*
* Vers�o no CVS: $Id: SecaoDocumentoBD.php 7873 2013-08-20 14:57:48Z bcu $
*/

require_once dirname(__FILE__).'/../../SEI.php';

class SecaoDocumentoBD extends InfraBD {

  public function __construct(InfraIBanco $objInfraIBanco){
  	 parent::__construct($objInfraIBanco);
  }

}
?>