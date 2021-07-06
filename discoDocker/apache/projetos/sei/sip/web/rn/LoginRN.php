<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 26/10/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class LoginRN extends InfraRN {
	
  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSip::getInstance();
  }
	
  protected function autenticarConectado(LoginDTO $objLoginDTO) {

    try{

      
      $objInfraException = new InfraException();
      
			if (InfraString::isBolVazia($objLoginDTO->getNumIdOrgaoUsuario())){
			  $objInfraException->lancarValidacao('�rg�o do usu�rio n�o informado.');
			}
	
			/*
			if (InfraString::isBolVazia($objLoginDTO->getNumIdContexto())){
			  $objInfraException->lancarValidacao('Contexto do usu�rio n�o informado.');
			}
	    */
			
			if (InfraString::isBolVazia($objLoginDTO->getStrSiglaUsuario())){
				$objInfraException->lancarValidacao('Sigla do usu�rio n�o informada.');
			}
			
			if (InfraString::isBolVazia($objLoginDTO->getStrSenhaUsuario())){
				$objInfraException->lancarValidacao('Senha do usu�rio n�o informada.');
			}
			

			//Converte usuario para minusculas
			$strUsuario = strtolower(trim($objLoginDTO->getStrSiglaUsuario()));
			
			$bolEmulacao = false;
			
			if (strpos($strUsuario,'#')!==false){
			  $arr = explode('#',$strUsuario);

			  if (count($arr)!=3 || trim($arr[0])=='' || trim($arr[1])=='' || trim($arr[2])==''){
          $objInfraException->lancarValidacao('Dados para emula��o incompletos, utilize: sigla_usuario_administrador#sigla_usuario_emulado#sigla_orgao_usuario_emulado');     			    
			  }
			  
			  
	      $strUsuarioEmulador = trim($arr[0]);
	      $strUsuario = trim($arr[1]);
	      $strOrgaoUsuario = strtoupper(trim($arr[2]));
	    
			  
			  $objLoginDTO->setStrSiglaUsuario($strUsuarioEmulador);
			  
			  //Verifica se o usu�rio � administrador do sistema
			  $this->buscarDadosSistema($objLoginDTO);
			  $this->buscarDadosUsuario($objLoginDTO);
			  
			  $objAdminstradorSistemaDTO = new AdministradorSistemaDTO();
			  $objAdminstradorSistemaDTO->setNumIdUsuario($objLoginDTO->getNumIdUsuario());
			  $objAdminstradorSistemaDTO->setNumIdSistema($objLoginDTO->getNumIdSistema());
			  $objAdminstradorSistemaRN = new AdministradorSistemaRN();
			  if ($objAdminstradorSistemaRN->contar($objAdminstradorSistemaDTO)==0){
			    $objInfraException->lancarValidacao('Usu�rio '.$strUsuarioEmulador.' n�o � administrador do sistema.');
			  }
			  
			  $bolEmulacao = true;
			}else{
			  $objLoginDTO->setStrSiglaUsuario($strUsuario);
			}
			
			
			//Obtem IP do LDAP para o �rg�o			
			$objOrgaoDTO = new OrgaoDTO();
			$objOrgaoDTO->retNumIdOrgao();
			$objOrgaoDTO->retStrSigla();
			$objOrgaoDTO->retStrSinAutenticar();
			$objOrgaoDTO->setNumIdOrgao($objLoginDTO->getNumIdOrgaoUsuario());
			
			$objOrgaoRN = new OrgaoRN();
			$objOrgaoDTO = $objOrgaoRN->consultar($objOrgaoDTO);
			
			$objContextoDTO = null;
      if (!InfraString::isBolVazia($objLoginDTO->getNumIdContexto())){
  			$objContextoDTO = new ContextoDTO();
  			$objContextoDTO->retStrBaseDnLdap();
  			$objContextoDTO->setNumIdContexto($objLoginDTO->getNumIdContexto());
  			
  			$objContextoRN = new ContextoRN();
  			$objContextoDTO = $objContextoRN->consultar($objContextoDTO);
      }
	
			$objLoginDTO->setNumIdGrupoRede(null);
			//$objLoginDTO->setStrDnUsuario(null);

			if ($objOrgaoDTO->getStrSinAutenticar()=='N'){
				
			  if ($objLoginDTO->getStrSiglaUsuario() != $objLoginDTO->getStrSenhaUsuario()){
				  $objInfraException->lancarValidacao(InfraLDAP::$MSG_USUARIO_SENHA_INVALIDA);
				}
									
			}else{
			  
			  if (!method_exists(ConfiguracaoSip::getInstance(),'autenticar') || !ConfiguracaoSip::getInstance()->autenticar($objLoginDTO)){
					
	   			$objInfraLDAP = new InfraLDAP();
	   			
	   			$arrLDAP = null;
	   			
	   			$objRelOrgaoAutenticacaoDTO = new RelOrgaoAutenticacaoDTO();
	   			$objRelOrgaoAutenticacaoDTO->retNumIdServidorAutenticacao();
	   			$objRelOrgaoAutenticacaoDTO->setNumIdOrgao($objOrgaoDTO->getNumIdOrgao());
	   			$objRelOrgaoAutenticacaoDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);
	   			
	   			$objRelOrgaoAutenticacaoRN = new RelOrgaoAutenticacaoRN();
	   			$arrObjRelOrgaoAutenticacaoDTO = $objRelOrgaoAutenticacaoRN->listar($objRelOrgaoAutenticacaoDTO);
	   			
	   			if (count($arrObjRelOrgaoAutenticacaoDTO)==0){
	   			  $objInfraException->lancarValidacao('Nenhum servidor de autentica��o configurado para o �rg�o.');
	   			}
	   			
	   			$objServidorAutenticacaoRN = new ServidorAutenticacaoRN();
	   			
	   			$numServidoresAutenticacao = count($arrObjRelOrgaoAutenticacaoDTO);
	   			
	   			for($i=0;$i<$numServidoresAutenticacao;$i++){
	   			
  	   			$objServidorAutenticacaoDTO = new ServidorAutenticacaoDTO();
  	   			$objServidorAutenticacaoDTO->retStrStaTipo();
  	   			$objServidorAutenticacaoDTO->retStrEndereco();
  	   			$objServidorAutenticacaoDTO->retNumPorta();
  	   			$objServidorAutenticacaoDTO->retNumVersao();
  	   			$objServidorAutenticacaoDTO->retStrSufixo();
  	   			$objServidorAutenticacaoDTO->retStrUsuarioPesquisa();
  	   			$objServidorAutenticacaoDTO->retStrSenhaPesquisa();
  	   			$objServidorAutenticacaoDTO->retStrContextoPesquisa();
  	   			$objServidorAutenticacaoDTO->retStrAtributoFiltroPesquisa();
  	   			$objServidorAutenticacaoDTO->retStrAtributoRetornoPesquisa();
  	   			$objServidorAutenticacaoDTO->setNumIdServidorAutenticacao($arrObjRelOrgaoAutenticacaoDTO[$i]->getNumIdServidorAutenticacao());
  	   			
  	   			$objServidorAutenticacaoDTO = $objServidorAutenticacaoRN->consultar($objServidorAutenticacaoDTO);

  	   			try{
  	   			  
    	   			$arrLDAP = $objInfraLDAP->pesquisaAvancada($objServidorAutenticacaoDTO->getStrStaTipo(),
                                           	   			     $objServidorAutenticacaoDTO->getStrEndereco(),
                                            	   			   $objServidorAutenticacaoDTO->getNumPorta(),
                                            	   			   $objServidorAutenticacaoDTO->getStrUsuarioPesquisa(),
                                            	   			   $objServidorAutenticacaoDTO->getStrSenhaPesquisa(),
                                            	   			   (InfraString::isBolVazia($objServidorAutenticacaoDTO->getStrContextoPesquisa()) && $objContextoDTO!=null)?$objContextoDTO->getStrBaseDnLdap():$objServidorAutenticacaoDTO->getStrContextoPesquisa(),
                                            	   			   $objServidorAutenticacaoDTO->getStrAtributoFiltroPesquisa(),
                                            	   			   $objServidorAutenticacaoDTO->getStrAtributoRetornoPesquisa(),
                                            	   			   (InfraString::isBolVazia($objServidorAutenticacaoDTO->getStrSufixo())?$objLoginDTO->getStrSiglaUsuario():$objLoginDTO->getStrSiglaUsuario().$objServidorAutenticacaoDTO->getStrSufixo()),
                                            	   			   $objLoginDTO->getStrSenhaUsuario(),
    	   			                                           $objServidorAutenticacaoDTO->getNumVersao());
    	   			
        	   			//sair no primeiro que autenticar
        	   			break;
    	   			 
  	   			}catch(Exception $e){
  	   			  
	  			    //se for o �ltimo servidor de autentica��o associado
  	   			  if ($i == ($numServidoresAutenticacao-1)){
  	   			    throw $e;
  	   			  }
  	   			  
  	   			}
	   			}	   			

	        if (is_array($arrLDAP) && $arrLDAP[InfraLDAP::$LDAP_GRUPO_REDE]!=''){
	          
	          $objGrupoRedeDTO = new GrupoRedeDTO();
	          $objGrupoRedeDTO->retNumIdGrupoRede();
	          $objGrupoRedeDTO->setStrOuLdap(InfraUtil::filtrarISO88591($arrLDAP[InfraLDAP::$LDAP_GRUPO_REDE]));
	          $objGrupoRedeDTO->setNumIdOrgao($objLoginDTO->getNumIdOrgaoUsuario());
	          
	          $objGrupoRedeRN = new GrupoRedeRN();
	          $objGrupoRedeDTO = $objGrupoRedeRN->consultar($objGrupoRedeDTO);

	          if ($objGrupoRedeDTO != null){
              $objLoginDTO->setNumIdGrupoRede($objGrupoRedeDTO->getNumIdGrupoRede());
	          }else{
              //LogSip::getInstance()->gravar('GRUPO DE REDE NAO CADASTRADO ('.$objLoginDTO->getStrSiglaUsuario().'/'.$objOrgaoDTO->getStrSigla().'): '.$arrLDAP[InfraLDAP::$LDAP_GRUPO_REDE]);
	          }
	          //$objLoginDTO->setStrDnUsuario(InfraUtil::filtrarISO88591($arrLDAP[InfraLDAP::$LDAP_DN]));
	        }
				}
			}

      if ($bolEmulacao){
        
      	//busca orgao do usuario emulado
        $objOrgaoDTO = new OrgaoDTO();
	      $objOrgaoDTO->retNumIdOrgao();
	      $objOrgaoDTO->setStrSigla($strOrgaoUsuario);
	      
	      $objOrgaoRN = new OrgaoRN();
	      $objOrgaoDTO = $objOrgaoRN->consultar($objOrgaoDTO); 
	      
	      if ($objOrgaoDTO==null){
          $objInfraException->lancarValidacao('Org�o do usu�rio emulado n�o encontrado.');     
	      }
			  
	      $objLoginDTO->setNumIdOrgaoUsuario($objOrgaoDTO->getNumIdOrgao());
	      
				//Busca ID do Usuario emulado
  			$objUsuarioDTO = new UsuarioDTO();
  			$objUsuarioDTO->retNumIdUsuario();
  			$objUsuarioDTO->retStrSigla();
  			$objUsuarioDTO->setStrSigla($strUsuario);
  			$objUsuarioDTO->setNumIdOrgao($objOrgaoDTO->getNumIdOrgao());			
  			$objUsuarioRN = new UsuarioRN();
  			$objUsuarioDTO = $objUsuarioRN->consultar($objUsuarioDTO);
  			if ($objUsuarioDTO===null){
  			  $objInfraException->lancarValidacao('Usu�rio emulado n�o encontrado no Sistema de Permiss�es.');
  			}
  			
  			$objLoginDTO->setStrSiglaUsuario($strUsuario);
  			$objLoginDTO->setNumIdUsuarioEmulador($objLoginDTO->getNumIdUsuario());
        $objLoginDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
	      
        /*
        //Busca o primeiro contexto do �rg�o emulado
        $objContextoDTO = new ContextoDTO();
        $objContextoDTO->retNumIdContexto();
        $objContextoDTO->retStrDescricaoOrgao();
        $objContextoDTO->setNumIdOrgao($objOrgaoDTO->getNumIdOrgao());
        $objContextoDTO->setNumMaxRegistrosRetorno(1);
        
        $objContextoRN = new ContextoRN();
        $objContextoDTO = $objContextoRN->consultar($objContextoDTO);
        
        if ($objContextoDTO==null){
          $objInfraException->lancarValidacao('Nenhum contexto encontrado no SIP para o �rg�o emulado.');
        }
        
        $objLoginDTO->setNumIdContexto($objContextoDTO->getNumIdContexto());
        $objLoginDTO->setStrDescricaoOrgaoContexto($objContextoDTO->getStrDescricaoOrgao());
        $objLoginDTO->setStrSiglaOrgaoContexto($strOrgaoUsuario);
        
        */
        $objLoginDTO->setNumIdContexto(null);
        $objLoginDTO->setStrDescricaoOrgaoContexto(null);
        $objLoginDTO->setStrSiglaOrgaoContexto(null);
        
       
        
      }else{
      	$objLoginDTO->setNumIdUsuarioEmulador(null);
      }									

    }catch(Exception $e){
    	
      if ($e instanceof InfraException){ 
         if ($e->contemValidacoes()){
           throw $e;
         }
      }

      /*
      try{
			  LogSip::getInstance()->gravar(InfraException::inspecionar($e));
			}catch(Exception $e2){}
			*/
      
			//N�o mostra a exce��o porque o erro do php mostra a senha do usuario
			$objInfraException->lancarValidacao("Erro autenticando usu�rio.");
    }
  }
  
	private function buscarDadosSistema(LoginDTO $objLoginDTO){
	  try{
	    $objInfraException = new InfraException();

			if (InfraString::isBolVazia($objLoginDTO->getStrSiglaOrgaoSistema())){
				$objInfraException->lancarValidacao('Sigla do �rg�o do Sistema n�o informada.');
			}
			
			if (InfraString::isBolVazia($objLoginDTO->getStrSiglaSistema())){
				$objInfraException->lancarValidacao('Sigla do Sistema n�o informada.');
			}
			
			//Busca ID do �rg�o do Sistema
			$objOrgaoDTO = new OrgaoDTO();
			$objOrgaoDTO->retNumIdOrgao();
			$objOrgaoDTO->retStrDescricao();
			$objOrgaoDTO->setStrSigla($objLoginDTO->getStrSiglaOrgaoSistema());
			$objOrgaoRN = new OrgaoRN();
			$objOrgaoDTO = $objOrgaoRN->consultar($objOrgaoDTO);
			if ($objOrgaoDTO===null){
			  $objInfraException->lancarValidacao('�rg�o \''.$objLoginDTO->getStrSiglaOrgao().'\' do Sistema n�o encontrado no Sistema de Permiss�es.');
			}
			$objLoginDTO->setNumIdOrgaoSistema($objOrgaoDTO->getNumIdOrgao());
			$objLoginDTO->setStrDescricaoOrgaoSistema($objOrgaoDTO->getStrDescricao());
			
			//Busca ID do Sistema
			$objSistemaDTO = new SistemaDTO();
			$objSistemaDTO->retNumIdSistema();
			$objSistemaDTO->retStrSigla();
			$objSistemaDTO->retStrPaginaInicial();
			$objSistemaDTO->setStrSigla($objLoginDTO->getStrSiglaSistema());
			$objSistemaDTO->setNumIdOrgao($objLoginDTO->getNumIdOrgaoSistema());
			$objSistemaRN = new SistemaRN();
			$objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);
			if ($objSistemaDTO===null){
			  $objInfraException->lancarValidacao('Sistema \''.$objLoginDTO->getStrSiglaSistema().'\' n�o encontrado no Sistema de Permiss�es.');
			}
			
			$objLoginDTO->setNumIdSistema($objSistemaDTO->getNumIdSistema());
			$objLoginDTO->setStrPaginaInicialSistema($objSistemaDTO->getStrPaginaInicial());
			
			$objInfraException->lancarValidacoes();
			
    }catch(Exception $e){
      throw new InfraException("Erro buscando dados do sistema.",$e);
    }
			
	}

	private function buscarDadosUsuario(LoginDTO $objLoginDTO){
	  try{
	    $objInfraException = new InfraException();

			if (InfraString::isBolVazia($objLoginDTO->getStrSiglaUsuario())){
				$objInfraException->lancarValidacao('Sigla do Usu�rio n�o informada.');
			}
			
			$objOrgaoRN = new OrgaoRN();
			
			if (!InfraString::isBolVazia($objLoginDTO->getNumIdContexto())){
			  
			  $objContextoDTO = new ContextoDTO();
			  $objContextoDTO->retNumIdOrgao();
			  $objContextoDTO->setNumIdContexto($objLoginDTO->getNumIdContexto());
			  
			  $objContextoRN = new ContextoRN();
			  $objContextoDTO = $objContextoRN->consultar($objContextoDTO); 
			  
			  if ($objContextoDTO==null){
			    $objInfraException->lancarValidacao('Contexto ['.$objLoginDTO->getNumIdContexto().'] n�o encontrado no Sistema de Permiss�es.');
			  }
			  
  			//Busca dados do Contexto
  			$objOrgaoDTO = new OrgaoDTO();
  			$objOrgaoDTO->retNumIdOrgao();
  			$objOrgaoDTO->retStrSigla();
  			$objOrgaoDTO->retStrDescricao();
  			$objOrgaoDTO->setNumIdOrgao($objContextoDTO->getNumIdOrgao());
  			
  			$objOrgaoDTO = $objOrgaoRN->consultar($objOrgaoDTO);
  			if ($objOrgaoDTO===null){
  			  $objInfraException->lancarValidacao('�rg�o ['.$objContextoDTO->getNumIdOrgao().'] do Contexto n�o encontrado no Sistema de Permiss�es.');
  			}
  			$objLoginDTO->setNumIdOrgaoContexto($objOrgaoDTO->getNumIdOrgao());
        $objLoginDTO->setStrSiglaOrgaoContexto($objOrgaoDTO->getStrSigla());
        $objLoginDTO->setStrDescricaoOrgaoContexto($objOrgaoDTO->getStrDescricao());
			}else{
  			$objLoginDTO->setNumIdOrgaoContexto(null);
        $objLoginDTO->setStrSiglaOrgaoContexto(null);
        $objLoginDTO->setStrDescricaoOrgaoContexto(null);
			}
			
			
			//Busca dados do Contexto
			$objOrgaoDTO = new OrgaoDTO();
			$objOrgaoDTO->retNumIdOrgao();
			$objOrgaoDTO->retStrSigla();
			$objOrgaoDTO->retStrDescricao();
			$objOrgaoDTO->setNumIdOrgao($objLoginDTO->getNumIdOrgaoUsuario());
			
			$objOrgaoDTO = $objOrgaoRN->consultar($objOrgaoDTO);
			if ($objOrgaoDTO===null){
			  $objInfraException->lancarValidacao('�rg�o ['.$objContextoDTO->getNumIdOrgao().'] do Usu�rio n�o encontrado no Sistema de Permiss�es.');
			}
			
			//Busca ID do Usuario
			$objUsuarioDTO = new UsuarioDTO();
			$objUsuarioDTO->retNumIdUsuario();
			$objUsuarioDTO->retStrSigla();
			$objUsuarioDTO->setStrSigla($objLoginDTO->getStrSiglaUsuario());
			$objUsuarioDTO->setNumIdOrgao($objLoginDTO->getNumIdOrgaoUsuario());			
			$objUsuarioRN = new UsuarioRN();
			$objUsuarioDTO = $objUsuarioRN->consultar($objUsuarioDTO);
			if ($objUsuarioDTO===null){
			  $objInfraException->lancarValidacao('Usu�rio \''.$objLoginDTO->getStrSiglaUsuario().' / '.$objOrgaoDTO->getStrSigla().'\' n�o encontrado no Sistema de Permiss�es.');
			}
      $objLoginDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
      
			$objInfraException->lancarValidacoes();
			
    }catch(Exception $e){
      throw new InfraException("Erro buscando dados do usu�rio.",$e);
    }
			
	}
		
  protected function cadastrarControlado(LoginDTO $objLoginDTO){

    try{

		  $this->buscarDadosSistema($objLoginDTO);
		  $this->buscarDadosUsuario($objLoginDTO);

      $objLoginDTO->unSetStrSenhaUsuario();

      $objLoginDTO->setDthLogin(InfraData::getStrDataHoraAtual());

			$objLoginDTO->setStrHashInterno(hash('SHA512',
                                                     mt_rand().
                                                     $objLoginDTO->__toString().
                                                     uniqid(mt_rand(), true)
          )
      );

			$objLoginDTO->setStrHashUsuario(hash('WHIRLPOOL',
                                                      uniqid(mt_rand(), true).
                                                      $objLoginDTO->__toString().
                                                      mt_rand()
          )
      );

			$objLoginDTO->setStrIdLogin(hash('SHA512',
                                                 mt_rand().
                                                 $objLoginDTO->__toString().
                                                 uniqid(mt_rand(), true)
          )
      );

      $objLoginDTO->setStrHashAgente(SessaoSip::gerarHashAgente());

			$objLoginDTO->setStrSinValidado('N');

      $objLoginBD = new LoginBD($this->getObjInfraIBanco());
      $ret = $objLoginBD->cadastrar($objLoginDTO);

			return $ret;
			
    }catch(Exception $e){
      throw new InfraException("Erro cadastrando dados de login.",$e);
    }
  }
	
	protected function validarControlado(LoginDTO $parObjLoginDTO){
		try{
		  
		  $objInfraException = new InfraException();
		  
			$objLoginDTO = new LoginDTO();
			$objLoginDTO->retTodos(true);
			$objLoginDTO->setStrIdLogin($parObjLoginDTO->getStrIdLogin());
			$objLoginDTO->setNumIdSistema($parObjLoginDTO->getNumIdSistema());
			$objLoginDTO->setNumIdUsuario($parObjLoginDTO->getNumIdUsuario());
			//$objLoginDTO->setNumIdContexto($parObjLoginDTO->getNumIdContexto());
			$objLoginDTO->setStrSinValidado('N');
			
			$objLoginDTO = $this->consultar($objLoginDTO);
			
			
			if ($objLoginDTO!=null){

				$dthLimite = InfraData::calcularData(ConfiguracaoSip::getInstance()->getValor('Sip', 'TempoLimiteValidacaoLogin', false, 60),InfraData::$UNIDADE_SEGUNDOS,InfraData::$SENTIDO_ADIANTE,$objLoginDTO->getDthLogin());
				if (InfraData::compararDataHora($dthLimite, InfraData::getStrDataHoraAtual()) > 0){
					throw new InfraException('Tempo limite para valida��o do login esgotado.');
				}

			  $dto = new LoginDTO();
  			$dto->setStrIdLogin($objLoginDTO->getStrIdLogin());
  			$dto->setNumIdSistema($objLoginDTO->getNumIdSistema());
  			$dto->setNumIdUsuario($objLoginDTO->getNumIdUsuario());
  			//$dto->setNumIdContexto($objLoginDTO->getNumIdContexto());
  			
  			//valida se o USER_AGENT � o mesmo do login (n�o pode mudar entre o login e a validacao)
  			//Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727)
  			
  		  //if ($objLoginDTO->getStrHashAgente()!=$parObjLoginDTO->getStrHashAgente()){
  		    //$objInfraException->lancarValidacao('Agente de acesso ao login inv�lido.');
  		    //throw new InfraException('Agente de acesso ao login inv�lido.');
  		  //}
			  
			  $dto->setStrSinValidado('S');
			  
        $objLoginBD = new LoginBD($this->getObjInfraIBanco());
        $objLoginBD->alterar($dto);

			}

			/*
			//exclui registros com mais de 12 horas
			$dto = new LoginDTO();
			$dto->retStrIdLogin();
			$dto->retNumIdSistema();
			$dto->retNumIdUsuario();
			$dto->retNumIdContexto();
			$dto->setDthLogin(InfraData::calcularData(12,InfraData::$UNIDADE_HORAS,InfraData::$SENTIDO_ATRAS,InfraData::getStrDataHoraAtual()),InfraDTO::$OPER_MENOR_IGUAL);
			$this->excluir($this->listar($dto));
			*/
			
			return $objLoginDTO;
			
    }catch(Exception $e){
      throw new InfraException("Erro validando dados de login.",$e);
    }
	}
	
  protected function consultarConectado(LoginDTO $objLoginDTO){
    try {

       //N�o valida permiss�o porque � acessado pelo procedimento de login
			 /////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('login_consultar',__METHOD__,$objLoginDTO);
			/////////////////////////////////////////////////////////////////

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objLoginBD = new LoginBD($this->getObjInfraIBanco());
      $ret = $objLoginBD->consultar($objLoginDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Login.',$e);
    }
  }
	
  protected function contarConectado(LoginDTO $objLoginDTO){
    try {

       //N�o valida permiss�o porque � acessado pelo procedimento de login
			 /////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('login_consultar',__METHOD__,$objLoginDTO);
			/////////////////////////////////////////////////////////////////

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objLoginBD = new LoginBD($this->getObjInfraIBanco());
      $ret = $objLoginBD->contar($objLoginDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Login.',$e);
    }
  }
  
  protected function excluirControlado($arrObjLoginDTO){
    try {

       //N�o valida permiss�o porque � acessado pelo procedimento de login
			 /////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('login_excluir',__METHOD__,$arrObjLoginDTO);
			 /////////////////////////////////////////////////////////////////

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();
			
      $objLoginBD = new LoginBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjLoginDTO);$i++){
        $objLoginBD->excluir($arrObjLoginDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Login.',$e);
    }
  }
	
  protected function listarConectado(LoginDTO $objLoginDTO) {
    try {

			/////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('login_listar',__METHOD__,$objLoginDTO);
			/////////////////////////////////////////////////////////////////


      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objLoginBD = new LoginBD($this->getObjInfraIBanco());
      $ret = $objLoginBD->listar($objLoginDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando logins.',$e);
    }
  }
  
  protected function logarControlado(LoginDTO $parObjLoginDTO) {
    try {
  		
  		if (($parObjLoginDTO = $this->validar($parObjLoginDTO))==null){
  		  return null;
  		}
      
      $objInfraSessaoDTO = $this->loginInterno($parObjLoginDTO);
      
      $objLoginDTOAuditoria = clone($parObjLoginDTO);
      $objLoginDTOAuditoria->setArrHierarquia(null);
      AuditoriaSip::getInstance()->auditar('login_padrao', __METHOD__, $objLoginDTOAuditoria);
      
      return $objInfraSessaoDTO;

    }catch(Exception $e){
      throw new InfraException('Erro efetuando login.',$e);
    }
  }
  
  protected function loginUnificadoControlado(LoginDTO $parObjLoginDTO) {
    try {
  		
      $objInfraSessaoDTO = null;
      
      $objInfraException = new InfraException();
      
      $strLink = $parObjLoginDTO->getStrLink();
      
      $numPosHash = strpos($strLink,'&infra_hash=');
      if ($numPosHash===false){
        //$objInfraException->lancarValidacao('Hash n�o localizado no link externo.');
        return null;
      }
  
      $strHashLink = substr($strLink, $numPosHash + strlen('&infra_hash='), 192);
      if (strlen($strHashLink)!=192){
        //$objInfraException->lancarValidacao('Tamanho do hash inv�lido no link externo.');
        return null;
      }
      
      $strHashUsuario = substr($strHashLink,64);
      
      
      $objLoginDTO = new LoginDTO();
      
      $objLoginDTO->retStrIdLogin();
      $objLoginDTO->retNumIdSistema();
      $objLoginDTO->retDthLogin();
      $objLoginDTO->retNumIdUsuario();
      $objLoginDTO->retNumIdContexto();
      $objLoginDTO->retNumIdGrupoRede();
      //$objLoginDTO->retStrDnUsuario();
      $objLoginDTO->retStrHashInterno();
      $objLoginDTO->retStrHashUsuario();
      $objLoginDTO->retStrHashAgente();
      //$objLoginDTO->retStrSinValidado();
      $objLoginDTO->retNumIdOrgaoUsuario();
      $objLoginDTO->retStrSiglaOrgaoUsuario();
      $objLoginDTO->retStrDescricaoOrgaoUsuario();
      $objLoginDTO->retStrSiglaUsuario();
      $objLoginDTO->retStrNomeUsuario();
      $objLoginDTO->retStrIdOrigemUsuario();
      //$objLoginDTO->retNumIdOrgaoSistema();
      $objLoginDTO->retStrSiglaOrgaoSistema();
      $objLoginDTO->retStrSiglaSistema();
      //$objLoginDTO->retStrPaginaInicialSistema();
      $objLoginDTO->retNumIdOrgaoContexto();
      $objLoginDTO->retStrSiglaOrgaoContexto();
      $objLoginDTO->retStrDescricaoOrgaoContexto();
      
			$objLoginDTO->retStrSiglaOrgaoUsuarioEmulador();
			$objLoginDTO->retStrDescricaoOrgaoUsuarioEmulador();
			$objLoginDTO->retNumIdOrgaoUsuarioEmulador();
			$objLoginDTO->retNumIdUsuarioEmulador();
			$objLoginDTO->retStrSiglaUsuarioEmulador();
			$objLoginDTO->retStrNomeUsuarioEmulador();
      
      
      $objLoginDTO->setStrHashUsuario($strHashUsuario);
      $objLoginDTO->setDthLogin(InfraData::calcularData(12,InfraData::$UNIDADE_HORAS,InfraData::$SENTIDO_ATRAS,InfraData::getStrDataHoraAtual()),InfraDTO::$OPER_MAIOR_IGUAL);
      $objLoginDTO->setStrSinValidado('S');
      
      $arrObjLoginDTO = $this->listar($objLoginDTO);

      $bolLinkValido = false;
      foreach($arrObjLoginDTO as $objLoginDTO){
        
        //InfraDebug::getInstance()->gravar($objLoginDTO->getStrHashInterno());

        if ($objLoginDTO->getStrSiglaOrgaoSistema()!=$parObjLoginDTO->getStrSiglaOrgaoSistema() || $objLoginDTO->getStrSiglaSistema()!=$parObjLoginDTO->getStrSiglaSistema()) {

          SessaoSip::getInstance()->setStrHashInterno($objLoginDTO->getStrHashInterno());
          SessaoSip::getInstance()->setStrHashUsuario($objLoginDTO->getStrHashUsuario());

          if (SessaoSip::getInstance()->verificarLink($parObjLoginDTO->getStrLink())) {
            $bolLinkValido = true;
            break;
          }
        }
      }
      
      if ($bolLinkValido && $objLoginDTO->getStrHashAgente()==$parObjLoginDTO->getStrHashAgente()){
          
        $objLoginDTO->setStrSiglaOrgaoSistema($parObjLoginDTO->getStrSiglaOrgaoSistema());
        $objLoginDTO->setNumIdOrgaoSistema(null);
        $objLoginDTO->setStrSiglaSistema($parObjLoginDTO->getStrSiglaSistema());
        $objLoginDTO->setNumIdSistema(null);
        
  		  $this->buscarDadosSistema($objLoginDTO);
        
        $objInfraSessaoDTO = $this->loginInterno($objLoginDTO);
                
        AuditoriaSip::getInstance()->auditar('login_unificado', __METHOD__, $objLoginDTO);
      }
      
      return $objInfraSessaoDTO;
      
    }catch(Exception $e){
      throw new InfraException('Erro realizando login unificado.',$e);
    }
  }  
  
  private function loginInterno(LoginDTO $objLoginDTO){
    try{
      
      /*
      InfraDebug::getInstance()->setBolLigado(false);
      InfraDebug::getInstance()->setBolDebugInfra(false);
      InfraDebug::getInstance()->limpar();
      
      $mi = memory_get_usage();
      $numSeg = InfraUtil::verificarTempoProcessamento();
      InfraDebug::getInstance()->gravar($objLoginDTO->getNumIdUsuario().'/'.$objLoginDTO->getNumIdContexto());
      */
      
      
      $objInfraSessaoDTO = new InfraSessaoDTO();
			$objInfraSessaoDTO->setStrSiglaOrgaoSistema($objLoginDTO->getStrSiglaOrgaoSistema());
			$objInfraSessaoDTO->setStrDescricaoOrgaoSistema($objLoginDTO->getStrDescricaoOrgaoSistema());
			$objInfraSessaoDTO->setNumIdOrgaoSistema($objLoginDTO->getNumIdOrgaoSistema());
			$objInfraSessaoDTO->setStrSiglaSistema($objLoginDTO->getStrSiglaSistema());
			$objInfraSessaoDTO->setNumIdSistema($objLoginDTO->getNumIdSistema());
			$objInfraSessaoDTO->setStrPaginaInicial($objLoginDTO->getStrPaginaInicialSistema());
			$objInfraSessaoDTO->setStrSiglaOrgaoUsuario($objLoginDTO->getStrSiglaOrgaoUsuario());
			$objInfraSessaoDTO->setStrDescricaoOrgaoUsuario($objLoginDTO->getStrDescricaoOrgaoUsuario());
			$objInfraSessaoDTO->setNumIdOrgaoUsuario($objLoginDTO->getNumIdOrgaoUsuario());
			$objInfraSessaoDTO->setNumIdContextoUsuario($objLoginDTO->getNumIdContexto());
			//$objInfraSessaoDTO->setStrDnUsuario($objLoginDTO->getStrDnUsuario());
			$objInfraSessaoDTO->setNumIdUsuario($objLoginDTO->getNumIdUsuario());
			$objInfraSessaoDTO->setStrSiglaUsuario($objLoginDTO->getStrSiglaUsuario());
			$objInfraSessaoDTO->setStrNomeUsuario($objLoginDTO->getStrNomeUsuario());
			$objInfraSessaoDTO->setStrIdOrigemUsuario($objLoginDTO->getStrIdOrigemUsuario());
			$objInfraSessaoDTO->setStrHashInterno($objLoginDTO->getStrHashInterno());
			$objInfraSessaoDTO->setStrHashUsuario($objLoginDTO->getStrHashUsuario());
			$objInfraSessaoDTO->setStrSiglaOrgaoUsuarioEmulador($objLoginDTO->getStrSiglaOrgaoUsuarioEmulador());
			$objInfraSessaoDTO->setStrDescricaoOrgaoUsuarioEmulador($objLoginDTO->getStrDescricaoOrgaoUsuarioEmulador());
			$objInfraSessaoDTO->setNumIdOrgaoUsuarioEmulador($objLoginDTO->getNumIdOrgaoUsuarioEmulador());
			$objInfraSessaoDTO->setNumIdUsuarioEmulador($objLoginDTO->getNumIdUsuarioEmulador());
			$objInfraSessaoDTO->setStrSiglaUsuarioEmulador($objLoginDTO->getStrSiglaUsuarioEmulador());
			$objInfraSessaoDTO->setStrNomeUsuarioEmulador($objLoginDTO->getStrNomeUsuarioEmulador());
			$objInfraSessaoDTO->setNumVersaoSip(SIP_VERSAO);
			$objInfraSessaoDTO->setNumVersaoInfraSip(VERSAO_INFRA);
			
			 
      $objRelGrupoRedeUnidadeDTO = new RelGrupoRedeUnidadeDTO();
      $objRelGrupoRedeUnidadeDTO->retNumIdUnidade();
      $objRelGrupoRedeUnidadeDTO->retStrSiglaUnidade();
      $objRelGrupoRedeUnidadeDTO->setNumIdGrupoRede($objLoginDTO->getNumIdGrupoRede());

      $objRelGrupoRedeUnidadeRN = new RelGrupoRedeUnidadeRN();
      $arrObjRelGrupoRedeUnidadeDTO = $objRelGrupoRedeUnidadeRN->listar($objRelGrupoRedeUnidadeDTO);
      
      $arrUnidadesPadrao = array();
      for($i=0;$i<count($arrObjRelGrupoRedeUnidadeDTO);$i++){
        $arrUnidadesPadrao[$i] = array();
        $arrUnidadesPadrao[$i][InfraSip::$WS_LOGIN_UNIDADE_PADRAO_ID] = $arrObjRelGrupoRedeUnidadeDTO[$i]->getNumIdUnidade();
        $arrUnidadesPadrao[$i][InfraSip::$WS_LOGIN_UNIDADE_PADRAO_SIGLA] = $arrObjRelGrupoRedeUnidadeDTO[$i]->getStrSiglaUnidade();
      }
			$objInfraSessaoDTO->setArrUnidadesPadrao($arrUnidadesPadrao);
			
			//Carrega objeto de login com o objeto de sess�o
			$objLoginDTO->setObjInfraSessaoDTO($objInfraSessaoDTO);
			$objLoginDTO->setArrHierarquia(null);
			
      $objPermissaoRN = new PermissaoRN();
      $objPermissaoRN->carregar($objLoginDTO);
  		
      /*
      $mf = memory_get_usage();
      $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
	    InfraDebug::getInstance()->gravar('[LoginRN->logar] '.$numSeg.' s - '.($mf-$mi).' bytes');
	    LogSip::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug());
	    */
      
      return $objInfraSessaoDTO;

    }catch(Exception $e){
      throw new InfraException('Erro realizando login.',$e);
    }
  }
  
  protected function removerLoginControlado(LoginDTO $parObjLoginDTO) {
    try {

      $objLoginDTO = new LoginDTO();
      $objLoginDTO->retStrIdLogin();
      $objLoginDTO->retNumIdUsuario();
      $objLoginDTO->retNumIdContexto();
      $objLoginDTO->retNumIdSistema();
      $objLoginDTO->retStrSiglaOrgaoSistema();
      $objLoginDTO->retStrSiglaSistema();
      $objLoginDTO->retStrHashInterno();
      $objLoginDTO->retStrHashUsuario();
      $objLoginDTO->retStrHashAgente();
      $objLoginDTO->retNumIdOrgaoUsuario();
      $objLoginDTO->retStrSiglaUsuario();
      $objLoginDTO->retStrNomeUsuario();
      $objLoginDTO->retStrIdOrigemUsuario();
      $objLoginDTO->retNumIdOrgaoContexto();
      $objLoginDTO->retStrSiglaOrgaoContexto();
      $objLoginDTO->retStrDescricaoOrgaoContexto();
			$objLoginDTO->retStrSiglaOrgaoUsuarioEmulador();
			$objLoginDTO->retStrDescricaoOrgaoUsuarioEmulador();
			$objLoginDTO->retNumIdOrgaoUsuarioEmulador();
			$objLoginDTO->retNumIdUsuarioEmulador();
			$objLoginDTO->retStrSiglaUsuarioEmulador();
			$objLoginDTO->retStrNomeUsuarioEmulador();

      $objLoginDTO->setStrSiglaOrgaoSistema($parObjLoginDTO->getStrSiglaOrgaoSistema());
      $objLoginDTO->setStrSiglaSistema($parObjLoginDTO->getStrSiglaSistema());
      $objLoginDTO->setNumIdUsuario($parObjLoginDTO->getNumIdUsuario());

      $objLoginDTO->setDthLogin(InfraData::calcularData(12,InfraData::$UNIDADE_HORAS,InfraData::$SENTIDO_ATRAS,InfraData::getStrDataHoraAtual()),InfraDTO::$OPER_MAIOR_IGUAL);

      $objLoginDTO->setStrSinValidado('S');
      
      $arrObjLoginDTO = $this->listar($objLoginDTO);

      foreach($arrObjLoginDTO as $objLoginDTO){

        SessaoSip::getInstance()->setStrHashInterno($objLoginDTO->getStrHashInterno());
        SessaoSip::getInstance()->setStrHashUsuario($objLoginDTO->getStrHashUsuario());
        
        if (SessaoSip::getInstance()->verificarLink($parObjLoginDTO->getStrLink())){

          AuditoriaSip::getInstance()->auditar('login_remover', __METHOD__, $objLoginDTO);

          $this->excluir(array($objLoginDTO));

          break;
        }
      }

    }catch(Exception $e){
      throw new InfraException('Erro removendo login.',$e);
    }
  }  

}
?>