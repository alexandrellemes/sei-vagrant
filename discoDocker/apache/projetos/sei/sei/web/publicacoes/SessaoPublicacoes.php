<?
  /*
  * TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
  * 17/04/2007 - CRIADO POR cle@trf4.gov.br
  */
   require_once dirname(__FILE__).'/../SEI.php';
   
  class SessaoPublicacoes extends InfraSessao {
    
    private static $instance = null;
    
    //SERVE A INST�NCIA ATIVA DA CLASSE OU UMA NOVA (SE N�O EXISTIR)
 	  public static function getInstance() {
	    if (self::$instance == null) {
	      SessaoSEI::getInstance(false,false);
        self::$instance = new SessaoPublicacoes();
	    }
	    return self::$instance; 
	  }
	  
    //MESMO CADASTRADO NO SISTEMA DE PERMISS�ES
    public function getStrSiglaOrgaoSistema() {
  		return ConfiguracaoSEI::getInstance()->getValor('SessaoSEI','SiglaOrgaoSistema');
	  }
	  
	  //MESMA CADASTRADA NO SISTEMA DE PERMISS�ES
    public function getStrSiglaSistema() {
		  return ConfiguracaoSEI::getInstance()->getValor('SessaoSEI','SiglaSistema');
	  }
    
    //USU�RIO � REDIRECIONADO PARA ESTE URL QUANDO A SESS�O � ENCERRADA OU O USU�RIO ALTEROU O URL (I.E.: QUERYSTRING)
	  public function getStrPaginaLogin(){
			return null;
		}	
		
  	public function getStrSipWsdl(){
			return null;
  	}
  	
    //PASSANDO SEMPRE TRUE EM TODOS, A VALIDA��O DO SIP EST� DESATIVADA (ESTES M�TODOS SOBRESCRITOS EXISTEM NA Infra.php)
    public function validarSessao(){
      return true;
    }
    
    public function assinarLink($strLink){
      
      if (strpos($strLink,'id_orgao_publicacao=')===false){
        if (isset($_GET['id_orgao_publicacao']) && $_GET['id_orgao_publicacao']!=''){
          if (strpos($strLink,'?')===false){
            $strLink .= '?';
          }else{
            $strLink .= '&';
          }
          $strLink .= 'id_orgao_publicacao='.$_GET['id_orgao_publicacao'];
        }
      }
      
      return $strLink;
    }
    
    public function validarLink($strLink=null){
      
      if (trim($_GET['id_orgao_publicacao'])==''){
        throw new InfraException('Link de publica��o incompleto.',null,null,false);
      }
      
      if (!is_numeric($_GET['id_orgao_publicacao'])){
        throw new InfraException('Link de publica��o inv�lido.',null,null,false);
      }
      
      $objOrgaoDTO = new OrgaoDTO();
      $objOrgaoDTO->setNumIdOrgao($_GET['id_orgao_publicacao']);
       
      $objOrgaoRN = new OrgaoRN();
      if ($objOrgaoRN->contarRN1354($objOrgaoDTO)==0){
        throw new InfraException('Dados do link de publica��o inv�lidos.');
      }
            
      return true;
    }
    
    public function validarPermissao($strRecurso,$strUnidade=null) {
      return true;
    }
    
    public function verificarPermissao($strRecurso,$strUnidade=null) {
      return true;
    }
  }
?>