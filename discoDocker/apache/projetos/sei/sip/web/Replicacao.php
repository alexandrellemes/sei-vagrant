<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
 * 
 * 07/05/2014 - criado por MGA
 *
 */

require_once dirname(__FILE__).'/Sip.php';

class Replicacao {

  private static $instance = null;
  private $arrObjWS = null;
  private $arrHierarquia = null;
  
 	public static function getInstance() { 
	    if (self::$instance == null) { 
        self::$instance = new Replicacao();
	    } 
	    return self::$instance; 
	} 
 	 
	public function __construct(){
	  $this->arrObjWS = array();
	  $this->arrHierarquia = array();
	}
  
  public function obterServico(ReplicacaoServicoDTO $parObjReplicacaoServicoDTO){
    try{
      
      $ret = null;
      
      $strSiglaSistema = '';
      
      $numIdSistema = $parObjReplicacaoServicoDTO->getNumIdSistema();
      
      if (!isset($this->arrObjWS[$numIdSistema])){
        
        $objSistemaDTO = new SistemaDTO();
        $objSistemaDTO->retNumIdSistema();
        $objSistemaDTO->retStrSigla();
        $objSistemaDTO->retStrWebService();
        $objSistemaDTO->setNumIdSistema($numIdSistema);
        
        $objSistemaRN = new SistemaRN();
        $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);

        if ($objSistemaDTO!=null) {

          $strSiglaSistema = $objSistemaDTO->getStrSigla();

          if (!InfraString::isBolVazia($objSistemaDTO->getStrWebService())) {

            if (!@file_get_contents($objSistemaDTO->getStrWebService())) {
              throw new InfraException('Web service n?o encontrado.');
            }

            $objWS = new SoapClient($objSistemaDTO->getStrWebService(), array('encoding' => 'ISO-8859-1'));

            $this->arrObjWS[$numIdSistema][0] = $objSistemaDTO->getStrSigla();
            $this->arrObjWS[$numIdSistema][1] = $objWS;
            $this->arrObjWS[$numIdSistema][2] = array();

            if (InfraWS::isBolServicoExiste($objWS, 'replicarUsuario')) {
              $this->arrObjWS[$numIdSistema][2][] = 'replicarUsuario';
            }

            if (InfraWS::isBolServicoExiste($objWS, 'replicarUnidade')) {
              $this->arrObjWS[$numIdSistema][2][] = 'replicarUnidade';
            }

            if (InfraWS::isBolServicoExiste($objWS, 'replicarOrgao')) {
              $this->arrObjWS[$numIdSistema][2][] = 'replicarOrgao';
            }

            if (InfraWS::isBolServicoExiste($objWS, 'replicarContexto')) {
              $this->arrObjWS[$numIdSistema][2][] = 'replicarContexto';
            }

            if (InfraWS::isBolServicoExiste($objWS, 'replicarPermissao')) {
              $this->arrObjWS[$numIdSistema][2][] = 'replicarPermissao';
            }

            if (InfraWS::isBolServicoExiste($objWS, 'replicarAssociacaoUsuarioUnidade')) {
              $this->arrObjWS[$numIdSistema][2][] = 'replicarAssociacaoUsuarioUnidade';
            }
          }
        }
      }
      
      if (isset($this->arrObjWS[$numIdSistema]) && in_array($parObjReplicacaoServicoDTO->getStrNomeOperacao(),$this->arrObjWS[$numIdSistema][2])){
        $ret = new ReplicacaoServicoDTO();
        $ret->setNumIdSistema($numIdSistema);
        $ret->setStrSiglaSistema($this->arrObjWS[$numIdSistema][0]);
        $ret->setObjWebService($this->arrObjWS[$numIdSistema][1]);
      }
      
      return $ret;
      
    } catch(Exception $e){
      throw new InfraException('Erro obtendo Web Service do sistema '.$strSiglaSistema.'.',$e);
    }
  }
  
  public function obterHierarquia(SistemaDTO $objSistemaDTO){
    try{
      
      $numIdSistema = $objSistemaDTO->getNumIdSistema();
      $numIdUnidade = $objSistemaDTO->getNumIdUnidade();
      
      if (!isset($this->arrHierarquia[$numIdSistema][$numIdUnidade])){
        $objSistemaRN = new SistemaRN();
        $this->arrHierarquia[$numIdSistema][$numIdUnidade] = InfraArray::indexarArrInfraDTO($objSistemaRN->listarHierarquia($objSistemaDTO),'IdUnidade');
      }

      return $this->arrHierarquia[$numIdSistema][$numIdUnidade];
      
    } catch(Exception $e){
      throw new InfraException('Erro obtendo hierarquia para replica??o.',$e);
    }
  }
}
?>