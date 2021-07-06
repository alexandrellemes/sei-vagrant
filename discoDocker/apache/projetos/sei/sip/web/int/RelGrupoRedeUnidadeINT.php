<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 20/08/2009 - criado por mga
*
* Vers�o do Gerador de C�digo: 1.28.0
*
* Vers�o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../Sip.php';

class RelGrupoRedeUnidadeINT extends InfraINT {

  public static function montarSelectIdUnidade($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdGrupoRede='', $numIdUnidade=''){
    $objRelGrupoRedeUnidadeDTO = new RelGrupoRedeUnidadeDTO();
    $objRelGrupoRedeUnidadeDTO->retNumIdGrupoRede();
    $objRelGrupoRedeUnidadeDTO->retNumIdUnidade();
    $objRelGrupoRedeUnidadeDTO->retNumIdUnidade();

    if ($numIdGrupoRede!==''){
      $objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede($numIdGrupoRede);
    }

    if ($numIdUnidade!==''){
      $objRelGrupoRedeUnidadeDTO->setNumIdUnidade($numIdUnidade);
    }

    $objRelGrupoRedeUnidadeDTO->setOrdNumIdUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
    $arrObjRelGrupoRedeUnidadeDTO = $objRelGrupoRedeUnidadeRN->listar($objRelGrupoRedeUnidadeDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjRelGrupoRedeUnidadeDTO, array('IdGrupoRede','IdUnidade'), 'IdUnidade');
  }
}
?>