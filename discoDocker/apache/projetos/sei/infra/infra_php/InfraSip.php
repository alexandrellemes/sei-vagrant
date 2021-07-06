<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 28/06/2006 - criado por MGA
*
* @package infra_php
*/


class InfraSip {
  
	private $objInfraSessao=null;
  
  //IMPORTANTE: a ordem deve ser a mesma em que os elementos s�o adicionados no array
  public static $WS_LOGIN_PERMISSAO_UNIDADES = 0;
  public static $WS_LOGIN_PERMISSAO_RECURSOS = 1;
  public static $WS_LOGIN_PERMISSAO_MENU = 2;

  public static $WS_LOGIN_PERMISSAO_UNIDADES_ID = 0;
  public static $WS_LOGIN_PERMISSAO_UNIDADES_SIGLA = 1;
  public static $WS_LOGIN_PERMISSAO_UNIDADES_DESCRICAO = 2;
  public static $WS_LOGIN_PERMISSAO_UNIDADES_ID_ORGAO = 3;
  public static $WS_LOGIN_PERMISSAO_UNIDADES_ID_ORIGEM = 4;
  
  public static $WS_LOGIN_UNIDADE_PADRAO_ID = 0;
  public static $WS_LOGIN_UNIDADE_PADRAO_SIGLA = 1;

  public static $WS_LOGIN_ORGAO_ID = 0;
  public static $WS_LOGIN_ORGAO_SIGLA = 1;
  public static $WS_LOGIN_ORGAO_DESCRICAO = 2;
  
  public static $WS_UNIDADE_ID = 0;
  public static $WS_UNIDADE_ORGAO_ID = 1;
  public static $WS_UNIDADE_SIGLA = 2;
  public static $WS_UNIDADE_DESCRICAO = 3;
  public static $WS_UNIDADE_SIN_ATIVO = 4;
  public static $WS_UNIDADE_SUBUNIDADES = 5;
  public static $WS_UNIDADE_UNIDADES_SUPERIORES = 6;
  public static $WS_UNIDADE_ID_ORIGEM = 7;

  public static $WS_USUARIO_ID = 0;
  public static $WS_USUARIO_ID_ORIGEM = 1;
  public static $WS_USUARIO_ORGAO_ID = 2;
  public static $WS_USUARIO_SIGLA = 3;
  public static $WS_USUARIO_NOME = 4;
  public static $WS_USUARIO_SIN_ATIVO = 5;
  public static $WS_USUARIO_UNIDADES = 6;  
  
  public static $WS_PERFIL_ID = 0;
  public static $WS_PERFIL_NOME = 1;
  public static $WS_PERFIL_DESCRICAO = 2;
  public static $WS_PERFIL_SIN_ATIVO = 3;

  public static $WS_ACESSO_DATA_HORA = 0;
  public static $WS_ACESSO_NAVEGADOR = 1;
  public static $WS_ACESSO_IP = 2;


  public function __construct($objInfraSessao){
	  $this->objInfraSessao = $objInfraSessao;
	}

	private function getSipWebService(){
	  
	  $strWSDL = $this->objInfraSessao->getStrSipWSDL();
	  
		if ($strWSDL==null){
		  throw new InfraException('Arquivo WSDL do Sistema de Permiss�es n�o informado.');
		}

		try{
      //Verifica se � uma URL ou arquivo v�lido
  	  if (!InfraUtil::isBolUrlValida($strWSDL)){ 
  	    if(!@file_get_contents($strWSDL)) {
          throw new InfraException('Arquivo WSDL '.$strWSDL.' n�o encontrado.');
  	    }
      }
		}catch(Exception $e){
		  throw new InfraException('Falha na conex�o com o Sistema de Permiss�es.',$e);
		}
		  
		try{
      //Web Service Sip
		  $objSipWS = new SoapClient($strWSDL, array('encoding'=>'ISO-8859-1'));
		}catch(Exception $e){
		  throw new InfraException('Erro acessando o Sistema de Permiss�es.');
		}
		return $objSipWS;
	}
	
	public function validarLogin($numIdLogin,$numIdSistema,$numIdUsuario,$strHashAgente){
    try {

      $objWS = $this->getSipWebService();
      $ret = $objWS->validarLogin($numIdLogin,$numIdSistema,$numIdUsuario,$strHashAgente);
      return $ret;

    }catch(Exception $e){
      $this->processarExcecao('Erro validando login no Sistema de Permiss�es.', $e);
    }
	}

	public function loginUnificado($strLink,$strHashAgente){
    try{
      $objWS = $this->getSipWebService();
      $ret = $objWS->loginUnificado($this->objInfraSessao->getStrSiglaOrgaoSistema(), $this->objInfraSessao->getStrSiglaSistema(), $strLink, $strHashAgente);
      return $ret;
    }catch(Exception $e){
      $this->processarExcecao('Erro no login unificado do Sistema de Permiss�es.',$e);
    }
	}
	
	public function removerLogin($strLink,$numIdUsuario){
    try{
      $objWS = $this->getSipWebService();
      $ret = $objWS->removerLogin($this->objInfraSessao->getStrSiglaOrgaoSistema(), $this->objInfraSessao->getStrSiglaSistema(), $strLink, $numIdUsuario);
      return $ret;
    }catch(Exception $e){
      $this->processarExcecao('Erro removendo login no Sistema de Permiss�es.',$e);
    }
	}
	
	public function carregarUnidades($numIdSistema,$numIdUsuario=null,$numIdUnidade=null){
    try {
      $objWS = $this->getSipWebService();
      $ret = $objWS->carregarUnidades($numIdSistema,$numIdUsuario,$numIdUnidade);
      return $ret;
    }catch(Exception $e){
      $this->processarExcecao('Erro buscando unidades no Sistema de Permiss�es.',$e);
    }
  }

	public function carregarUsuarios($numIdSistema,$numIdUnidade=null,$strNomeRecurso=null,$strNomePerfil=null){
    try {
      $objWS = $this->getSipWebService();
      $ret = $objWS->carregarUsuarios($numIdSistema,$numIdUnidade,$strNomeRecurso,$strNomePerfil);
      return $ret;
    }catch(Exception $e){
      $this->processarExcecao('Erro buscando usu�rios no Sistema de Permiss�es.',$e);
    }
	}

	public function carregarPerfis($numIdSistema,$numIdUsuario=null,$numIdUnidade=null){
    try{
      $objWS = $this->getSipWebService();
      $ret = $objWS->carregarPerfis($numIdSistema,$numIdUsuario,$numIdUnidade);
      return $ret;
    }catch(Exception $e){
      $this->processarExcecao('Erro buscando perfis no Sistema de Permiss�es.',$e);
    }
  }

  public function listarAcessos($numIdSistema,$numIdUsuario){
    try{
      $objWS = $this->getSipWebService();
      $ret = $objWS->listarAcessos($numIdSistema,$numIdUsuario);
      return $ret;
    }catch(Exception $e){
      $this->processarExcecao('Erro listando acessos no Sistema de Permiss�es.',$e);
    }
  }

	public function autenticar($numIdOrgao,$numIdContexto,$strSigla,$strSenha){
	  try {
			for ($i = 0; $i < strlen($strSenha); $i++) {
				$strSenha[$i] = ~$strSenha[$i];
			}

			$objWS = $this->getSipWebService();
			$ret = $objWS->autenticar($numIdOrgao, $numIdContexto, $strSigla, base64_encode($strSenha));
			return $ret;

		}catch(Exception $e){
      $this->processarExcecao('Erro autenticando usu�rio.',$e);
		}
	}

	private function processarExcecao($str, $e){

    if ($e instanceof SoapFault && InfraException::getTipoInfraException($e)=='INFRA_VALIDACAO') {
      throw new InfraException($e->faultstring, $e);
    }

    throw new InfraException($str, $e);

  }
}
?>