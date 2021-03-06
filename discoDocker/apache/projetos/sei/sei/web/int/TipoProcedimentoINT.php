<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 13/12/2007 - criado por mga
*
* Vers?o do Gerador de C?digo: 1.10.1
*
* Vers?o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class TipoProcedimentoINT extends InfraINT {

  public static function montarSelectNomeLiberados($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){

    $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
    $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
    $objTipoProcedimentoDTO->retStrNome();
    $objTipoProcedimentoDTO->retStrSinOuvidoria();

    $objUnidadeDTO = new UnidadeDTO();
    $objUnidadeDTO->setBolExclusaoLogica(false);
    $objUnidadeDTO->retStrSinOuvidoria();
    $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

    $objUnidadeRN = new UnidadeRN();
    $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

    if ($objUnidadeDTO->getStrSinOuvidoria()=='N'){
      $objTipoProcedimentoDTO->adicionarCriterio(array('SinOuvidoria','IdTipoProcedimento'),
          array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
          array('N',$strValorItemSelecionado),
          InfraDTO::$OPER_LOGICO_OR);
    }


    $objTipoProcedimentoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    if ($strValorItemSelecionado!=null){

      $objTipoProcedimentoDTO->setBolExclusaoLogica(false);
      $objTipoProcedimentoDTO->adicionarCriterio(array('SinAtivo','IdTipoProcedimento'),
          array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
          array('S',$strValorItemSelecionado),
          InfraDTO::$OPER_LOGICO_OR);
    }

    $objTipoProcedimentoRN = new TipoProcedimentoRN();

    $arrNaoPermitidos = InfraArray::indexarArrInfraDTO($objTipoProcedimentoRN->listarNaoLiberadosNaUnidade(),'IdTipoProcedimento');

    if (count($arrNaoPermitidos)){

      if ($strValorItemSelecionado!=null){
        unset($arrNaoPermitidos[$strValorItemSelecionado]);
      }

      if (count($arrNaoPermitidos)) {
        $objTipoProcedimentoDTO->setNumIdTipoProcedimento(array_keys($arrNaoPermitidos), InfraDTO::$OPER_NOT_IN);
      }
    }

    $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);

    foreach($arrObjTipoProcedimentoDTO as $objTipoProcedimentoDTO){
      if ($objTipoProcedimentoDTO->getStrSinOuvidoria()=='S'){
        $objTipoProcedimentoDTO->setStrNome($objTipoProcedimentoDTO->getStrNome().' (Ouvidoria)');
      }
    }

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjTipoProcedimentoDTO, 'IdTipoProcedimento', 'Nome');
  }

  public static function montarSelectNome($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $bolSomenteInterno=false){

    $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
    $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
    $objTipoProcedimentoDTO->retStrNome();
    $objTipoProcedimentoDTO->retStrSinOuvidoria();
    
    if ($bolSomenteInterno){
      $objTipoProcedimentoDTO->setStrSinInterno('S');
    }
    
    $objTipoProcedimentoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
    
    if ($strValorItemSelecionado!=null){
      
      $objTipoProcedimentoDTO->setBolExclusaoLogica(false);
      $objTipoProcedimentoDTO->adicionarCriterio(array('SinAtivo','IdTipoProcedimento'),
                                                array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
                                                array('S',$strValorItemSelecionado),
                                                InfraDTO::$OPER_LOGICO_OR);
    }
    
    $objTipoProcedimentoRN = new TipoProcedimentoRN();
    $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);
    
    foreach($arrObjTipoProcedimentoDTO as $objTipoProcedimentoDTO){
      if ($objTipoProcedimentoDTO->getStrSinOuvidoria()=='S'){
        $objTipoProcedimentoDTO->setStrNome($objTipoProcedimentoDTO->getStrNome().' (Ouvidoria)');
      }
    }

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjTipoProcedimentoDTO, 'IdTipoProcedimento', 'Nome');
  }
  
  public static function montarSelectSugestaoAssuntosRI0567($numIdTipoProcedimento){
    $objRelTipoProcedimentoAssuntoDTO = new RelTipoProcedimentoAssuntoDTO();
    $objRelTipoProcedimentoAssuntoDTO->retNumIdAssunto();
    $objRelTipoProcedimentoAssuntoDTO->retStrCodigoEstruturadoAssunto();
    $objRelTipoProcedimentoAssuntoDTO->retStrDescricaoAssunto();
    $objRelTipoProcedimentoAssuntoDTO->setNumIdTipoProcedimento($numIdTipoProcedimento);
    $objRelTipoProcedimentoAssuntoDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objRelTipoProcedimentoAssuntoRN = new RelTipoProcedimentoAssuntoRN();
    $arrObjRelTipoProcedimentoAssuntoDTO = $objRelTipoProcedimentoAssuntoRN->listarRN0192($objRelTipoProcedimentoAssuntoDTO);
    
    foreach($arrObjRelTipoProcedimentoAssuntoDTO as $dto){
      $dto->setStrDescricaoAssunto(AssuntoINT::formatarCodigoDescricaoRI0568($dto->getStrCodigoEstruturadoAssunto(),$dto->getStrDescricaoAssunto()));
    }

    return parent::montarSelectArrInfraDTO(null, null, null, $arrObjRelTipoProcedimentoAssuntoDTO, 'IdAssunto', 'DescricaoAssunto');
  }

  public static function autoCompletarTipoProcedimento($strPalavrasPesquisa){
            
    $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
    $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
    $objTipoProcedimentoDTO->retStrNome();
    
    $objTipoProcedimentoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objTipoProcedimentoRN = new TipoProcedimentoRN();
   
    $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);
        
    $strPalavrasPesquisa = trim($strPalavrasPesquisa);
    if ($strPalavrasPesquisa != ''){
      $ret = array();
      $strPalavrasPesquisa = strtolower($strPalavrasPesquisa);
      foreach($arrObjTipoProcedimentoDTO as $objTipoProcedimentoDTO){
        if (strpos(strtolower($objTipoProcedimentoDTO->getStrNome()),$strPalavrasPesquisa)!==false){
          $ret[] = $objTipoProcedimentoDTO;
        }
      }
    }else{
      $ret = $arrObjTipoProcedimentoDTO;
    } 
    return $ret;
  }
  
  public static function montarSelectOuvidoria($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){

    $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
    $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
    $objTipoProcedimentoDTO->retStrNome();
    $objTipoProcedimentoDTO->setStrSinOuvidoria('S');
    
    $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
    $numIdTipoEquivoco = $objInfraParametro->getValor('ID_TIPO_PROCEDIMENTO_OUVIDORIA_EQUIVOCO', false);
    if ($numIdTipoEquivoco != null){
      $objTipoProcedimentoDTO->setNumIdTipoProcedimento($numIdTipoEquivoco, InfraDTO::$OPER_DIFERENTE);
    }
    
    $objTipoProcedimentoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
    
    $objTipoProcedimentoRN = new TipoProcedimentoRN();
    $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);
    
    
    foreach($arrObjTipoProcedimentoDTO as $objTipoProcedimentoDTO){
      $objTipoProcedimentoDTO->setStrNome($objTipoProcedimentoDTO->getStrNome());
    }
  
    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjTipoProcedimentoDTO, 'IdTipoProcedimento', 'Nome');
  }
    
  public static function montarSelectNomeIndividuais($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){

    $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
    $objTipoProcedimentoDTO->setBolExclusaoLogica(false);
    $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
    $objTipoProcedimentoDTO->retStrNome();
    $objTipoProcedimentoDTO->setStrSinIndividual('S');
    
    $objTipoProcedimentoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objTipoProcedimentoRN = new TipoProcedimentoRN();
    $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjTipoProcedimentoDTO, 'IdTipoProcedimento', 'Nome');
  }
  
  public static function obterSugestoesHipoteseLegalGrauSigilo($arrIdTipoProcedimento){

    $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
    $objTipoProcedimentoDTO->setDistinct(true);
    $objTipoProcedimentoDTO->retNumIdHipoteseLegalSugestao();
    $objTipoProcedimentoDTO->retStrStaGrauSigiloSugestao();
    $objTipoProcedimentoDTO->setNumIdTipoProcedimento($arrIdTipoProcedimento, InfraDTO::$OPER_IN);

    $objTipoProcedimentoRN = new TipoProcedimentoRN();
    $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);

    $arrIdHipoteselegalSugestao = array_unique(InfraArray::converterArrInfraDTO($arrObjTipoProcedimentoDTO,'IdHipoteseLegalSugestao'));
    $arrStaGrauSigiloSugestao = array_unique(InfraArray::converterArrInfraDTO($arrObjTipoProcedimentoDTO,'StaGrauSigiloSugestao'));

    $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
    if (count($arrIdHipoteselegalSugestao)==1){
      $objTipoProcedimentoDTO->setNumIdHipoteseLegalSugestao($arrIdHipoteselegalSugestao[0]);
    }else{
      $objTipoProcedimentoDTO->setNumIdHipoteseLegalSugestao(null);
    }

    if (count($arrStaGrauSigiloSugestao)==1){
      $objTipoProcedimentoDTO->setStrStaGrauSigiloSugestao($arrStaGrauSigiloSugestao[0]);
    }else{
      $objTipoProcedimentoDTO->setStrStaGrauSigiloSugestao(null);
    }

    return $objTipoProcedimentoDTO;
  }
}
?>