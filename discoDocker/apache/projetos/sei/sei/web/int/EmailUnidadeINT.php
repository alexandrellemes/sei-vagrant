<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 07/06/2010 - criado por fazenda_db
*
* Vers?o do Gerador de C?digo: 1.29.1
*
* Vers?o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class EmailUnidadeINT extends InfraINT {

  public static function montarSelectEmail($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objEmailUnidadeDTO = new EmailUnidadeDTO();
    $objEmailUnidadeDTO->retNumIdEmailUnidade();
    $objEmailUnidadeDTO->retStrEmail();
    $objEmailUnidadeDTO->retStrDescricao();
    $objEmailUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

    $objEmailUnidadeDTO->setOrdStrEmail(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objEmailUnidadeRN = new EmailUnidadeRN();
    $arrObjEmailUnidadeDTO = $objEmailUnidadeRN->listar($objEmailUnidadeDTO);
    
    $arrEmailUnidade = array();
    
    foreach($arrObjEmailUnidadeDTO as $objEmailUnidadeDTO){
    	$arrEmailUnidade[EmailINT::formatarNomeEmailRI0960(SessaoSEI::getInstance()->getStrSiglaOrgaoUnidadeAtual(),$objEmailUnidadeDTO->getStrDescricao(),$objEmailUnidadeDTO->getStrEmail())] = InfraString::formatarXML(EmailINT::formatarNomeEmailRI0960(SessaoSEI::getInstance()->getStrSiglaOrgaoUnidadeAtual(),$objEmailUnidadeDTO->getStrDescricao(),$objEmailUnidadeDTO->getStrEmail()));
    }
  
    return parent::montarSelectArray($strPrimeiroItemValor, $strPrimeiroItemDescricao, PaginaSEI::tratarHTML($strValorItemSelecionado), $arrEmailUnidade);
  }
}
?>