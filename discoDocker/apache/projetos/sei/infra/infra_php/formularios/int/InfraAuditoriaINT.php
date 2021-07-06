<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 24/10/2011 - criado por mga
*
* Vers�o do Gerador de C�digo: 1.32.1
*
* Vers�o no CVS: $Id$
*/

//require_once dirname(__FILE__).'/../Infra.php';

class InfraAuditoriaINT extends InfraINT {

  public static function montarSelectIdInfraAuditoria($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdUsuario='', $numIdUsuarioEmulador='', $numIdUnidade=''){
    $objInfraAuditoriaDTO = new InfraAuditoriaDTO();
    $objInfraAuditoriaDTO->retNumIdInfraAuditoria();
    $objInfraAuditoriaDTO->retNumIdInfraAuditoria();

    if ($numIdUsuario!==''){
      $objInfraAuditoriaDTO->setNumIdUsuario($numIdUsuario);
    }

    if ($numIdUsuarioEmulador!==''){
      $objInfraAuditoriaDTO->setNumIdUsuarioEmulador($numIdUsuarioEmulador);
    }

    if ($numIdUnidade!==''){
      $objInfraAuditoriaDTO->setNumIdUnidade($numIdUnidade);
    }

    $objInfraAuditoriaDTO->setOrdNumIdInfraAuditoria(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objInfraAuditoriaRN = new InfraAuditoriaRN();
    $arrObjInfraAuditoriaDTO = $objInfraAuditoriaRN->listar($objInfraAuditoriaDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjInfraAuditoriaDTO, 'IdInfraAuditoria', 'IdInfraAuditoria');
  }
}
?>