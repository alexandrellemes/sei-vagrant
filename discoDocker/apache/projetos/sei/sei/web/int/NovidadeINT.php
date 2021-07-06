<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 29/03/2010 - criado por mga
*
* Vers�o do Gerador de C�digo: 1.29.1
*
* Vers�o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class NovidadeINT extends InfraINT {

  public static function montarSelectTitulo($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdUsuario=''){
    $objNovidadeDTO = new NovidadeDTO();
    $objNovidadeDTO->retNumIdNovidade();
    $objNovidadeDTO->retStrTitulo();

    if ($numIdUsuario!==''){
      $objNovidadeDTO->setNumIdUsuario($numIdUsuario);
    }

    $objNovidadeDTO->setOrdStrTitulo(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objNovidadeRN = new NovidadeRN();
    $arrObjNovidadeDTO = $objNovidadeRN->listar($objNovidadeDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjNovidadeDTO, 'IdNovidade', 'Titulo');
  }
}
?>