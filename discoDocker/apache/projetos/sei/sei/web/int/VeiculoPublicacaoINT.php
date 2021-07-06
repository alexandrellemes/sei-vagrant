<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 24/07/2013 - criado por mkr@trf4.jus.br
*
* Vers�o do Gerador de C�digo: 1.33.1
*
* Vers�o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class VeiculoPublicacaoINT extends InfraINT {

  public static function montarSelectNome($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objVeiculoPublicacaoDTO = new VeiculoPublicacaoDTO();
    $objVeiculoPublicacaoDTO->retNumIdVeiculoPublicacao();
    $objVeiculoPublicacaoDTO->retStrNome();

    if ($strValorItemSelecionado!=null){
      $objVeiculoPublicacaoDTO->setBolExclusaoLogica(false);
      $objVeiculoPublicacaoDTO->adicionarCriterio(array('SinAtivo','IdVeiculoPublicacao'),array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),array('S',$strValorItemSelecionado),InfraDTO::$OPER_LOGICO_OR);
    }

    $objVeiculoPublicacaoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objVeiculoPublicacaoRN = new VeiculoPublicacaoRN();
    $arrObjVeiculoPublicacaoDTO = $objVeiculoPublicacaoRN->listar($objVeiculoPublicacaoDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjVeiculoPublicacaoDTO, 'IdVeiculoPublicacao', 'Nome');
  }

  public static function montarSelectStaTipo($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objVeiculoPublicacaoRN = new VeiculoPublicacaoRN();
    $arrObjTipoDTO = $objVeiculoPublicacaoRN->listarValoresTipo();
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjTipoDTO, 'StaTipo', 'Descricao');
  }
  
  public static function montarSelectNomePesquisa($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objVeiculoPublicacaoDTO = new VeiculoPublicacaoDTO();
    $objVeiculoPublicacaoDTO->retNumIdVeiculoPublicacao();
    $objVeiculoPublicacaoDTO->retStrNome();
  
    if ($strValorItemSelecionado!=null){
      $objVeiculoPublicacaoDTO->setBolExclusaoLogica(false);
      $objVeiculoPublicacaoDTO->adicionarCriterio(array('SinAtivo','IdVeiculoPublicacao'),array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),array('S',$strValorItemSelecionado),InfraDTO::$OPER_LOGICO_OR);
    }
    $objVeiculoPublicacaoDTO->setStrSinExibirPesquisaInterna('S');
    $objVeiculoPublicacaoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
  
    $objVeiculoPublicacaoRN = new VeiculoPublicacaoRN();
    $arrObjVeiculoPublicacaoDTO = $objVeiculoPublicacaoRN->listar($objVeiculoPublicacaoDTO);
  
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjVeiculoPublicacaoDTO, 'IdVeiculoPublicacao', 'Nome');
  }
  
}
?>