<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
 * 
 * 30/05/2006 - criado por MGA
 *
 * @package infra_php
 */

/*
CREATE TABLE infra_parametro
(
	nome  varchar(100)  NOT NULL ,
	valor  varchar(max)  NULL ,
	CONSTRAINT  pk_infra_parametro PRIMARY KEY (nome)
);

*/
 		
class InfraParametro extends InfraRN {

	public function __construct(InfraIBanco $objInfraIBanco){
    BancoInfra::setObjInfraIBanco($objInfraIBanco);
	}
	
  protected function inicializarObjInfraIBanco(){
    return BancoInfra::getInstance();
  }
  
  protected function getValorConectado($strNome, $bolErroNaoEncontrado=true) {

    if (InfraDebug::isBolProcessar()) {
      InfraDebug::getInstance()->gravarInfra('[InfraParametro->getStrValor] ' . $strNome);
    }

    $objInfraParametroDTO = new InfraParametroDTO();
    $objInfraParametroDTO->retStrValor();
    $objInfraParametroDTO->setStrNome($strNome);

    $objInfraParametroRN = new InfraParametroRN();
    $objInfraParametroDTO = $objInfraParametroRN->consultar($objInfraParametroDTO);

    if ($objInfraParametroDTO==null) {
      if ($bolErroNaoEncontrado){
        throw new InfraException('Par?metro '.$strNome.' n?o encontrado.');
      }else{
        return null;
      }
    }

    return $objInfraParametroDTO->getStrValor();
  }  

  protected function listarValoresConectado($arrNomes=null, $bolErroNaoEncontrado=true) {

    if (InfraDebug::isBolProcessar()) {
      InfraDebug::getInstance()->gravarInfra('[InfraParametro->listarValores] ');
    }

    $objInfraParametroDTO = new InfraParametroDTO();
    $objInfraParametroDTO->retStrNome();
    $objInfraParametroDTO->retStrValor();

    if (is_array($arrNomes) && count($arrNomes) > 0) {
      $objInfraParametroDTO->setStrNome($arrNomes, InfraDTO::$OPER_IN);
    }

    $objInfraParametroRN = new InfraParametroRN();
    $arrObjInfraParametroDTO = $objInfraParametroRN->listar($objInfraParametroDTO);

    $ret = array();
    foreach($arrObjInfraParametroDTO as $objInfraParametroDTO){
      $ret[$objInfraParametroDTO->getStrNome()] = ($objInfraParametroDTO->getStrValor()==null)?'':$objInfraParametroDTO->getStrValor();
    }

    if ( $bolErroNaoEncontrado && is_array($arrNomes)){
      foreach($arrNomes as $strNome){
        if (!isset($ret[$strNome])){
          throw new InfraException('Par?metro '.$strNome.' n?o encontrado.');
        }
      }
    }

    return $ret;
  }  
  
  protected function setValorControlado($strNome,$strValor) {

    if (InfraDebug::isBolProcessar()) {
      InfraDebug::getInstance()->gravarInfra('[InfraParametro->setStrValor] ' . $strNome . ': ' . $strValor);
    }

    $objInfraParametroRN = new InfraParametroRN();

    if (!$this->isSetValor($strNome)){

      $objInfraParametroDTO = new InfraParametroDTO();
      $objInfraParametroDTO->setStrValor($strValor);
      $objInfraParametroDTO->setStrNome($strNome);
      $objInfraParametroRN->cadastrar($objInfraParametroDTO);

    }else{

      $objInfraParametroDTO = new InfraParametroDTO();
      $objInfraParametroDTO->setStrValor($strValor);
      $objInfraParametroDTO->setStrNome($strNome);
      $objInfraParametroRN->alterar($objInfraParametroDTO);
    }

  }
  
  protected function isSetValorConectado($strNome) {

    if (InfraDebug::isBolProcessar()) {
      InfraDebug::getInstance()->gravarInfra('[InfraParametro->isSetStrValor] ' . $strNome);
    }

    $objInfraParametroDTO = new InfraParametroDTO();
    $objInfraParametroDTO->retStrValor();
    $objInfraParametroDTO->setStrNome($strNome);

    $objInfraParametroRN = new InfraParametroRN();
    $objInfraParametroDTO = $objInfraParametroRN->consultar($objInfraParametroDTO);

    return ($objInfraParametroDTO != null);
  }
}
 
?>