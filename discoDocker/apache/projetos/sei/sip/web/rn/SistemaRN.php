<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 27/11/2006 - criado por mga
*
*
*/

require_once dirname(__FILE__).'/../Sip.php';

class SistemaRN extends InfraRN {

  public static $TBD_SQLSERVER = 'S';
  public static $TBD_MYSQL = 'M';
  public static $TBD_ORACLE = 'O';

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSip::getInstance();
  }
	
  public function importar(ImportarSistemaDTO $objImportarSistemaDTO) {
    try{
 
      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('sistema_importar',__METHOD__,$objImportarSistemaDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if (InfraString::isBolVazia($objImportarSistemaDTO->getStrSiglaOrgaoSistemaOrigem())){
        $objInfraException->adicionarValidacao('�rg�o do Sistema Origem n�o informado.');
      }

      if (InfraString::isBolVazia($objImportarSistemaDTO->getStrSiglaOrigem())){
        $objInfraException->adicionarValidacao('Sigla do Sistema Origem n�o informada.');
      }

      if (InfraString::isBolVazia($objImportarSistemaDTO->getNumIdOrgaoSistemaDestino())){
        $objInfraException->adicionarValidacao('�rg�o do Sistema Destino n�o informado.');
      }

      if (InfraString::isBolVazia($objImportarSistemaDTO->getNumIdHierarquiaDestino())){
        $objInfraException->adicionarValidacao('Hierarquia de Destino n�o informada.');
      }
			
      if (InfraString::isBolVazia($objImportarSistemaDTO->getStrSiglaDestino())){
        $objInfraException->adicionarValidacao('Sigla do Sistema Destino n�o informada.');
      }

      if (InfraString::isBolVazia($objImportarSistemaDTO->getStrBancoServidor())){
        $objInfraException->adicionarValidacao('Servidor do Banco de Dados de origem n�o informado.');
      }

      if (InfraString::isBolVazia($objImportarSistemaDTO->getStrBancoPorta())){
        $objInfraException->adicionarValidacao('Porta do Banco de Dados de origem n�o informada.');
      }

      if (InfraString::isBolVazia($objImportarSistemaDTO->getStrBancoNome())){
        $objInfraException->adicionarValidacao('Nome da Base de Dados de origem n�o informado.');
      }

      if (InfraString::isBolVazia($objImportarSistemaDTO->getStrBancoUsuario())){
        $objInfraException->adicionarValidacao('Usu�rio do Banco de Dados de origem n�o informado.');
      }
      
      if (InfraString::isBolVazia($objImportarSistemaDTO->getStrBancoSenha())){
        $objInfraException->adicionarValidacao('Senha do Banco de Dados de origem n�o informada.');
      }

      if (InfraString::isBolVazia($objImportarSistemaDTO->getStrStaTipoBanco())){
        $objInfraException->adicionarValidacao('Tipo do Banco de Dados de origem n�o informado.');
      }

      if (!in_array($objImportarSistemaDTO->getStrStaTipoBanco(),array(self::$TBD_MYSQL,self::$TBD_SQLSERVER,self::$TBD_ORACLE))){
        $objInfraException->adicionarValidacao('Tipo do Banco de Dados de origem inv�lido.');
      }


      $dto = new SistemaDTO();
      $dto->setNumIdOrgao($objImportarSistemaDTO->getNumIdOrgaoSistemaDestino());
      $dto->setStrSigla($objImportarSistemaDTO->getStrSiglaDestino());
      if ($this->contar($dto)>0){
        $objInfraException->adicionarValidacao('J� existe um sistema com este �rg�o e sigla de destino.');        
      }
      
      
      $objInfraException->lancarValidacoes();

      switch($objImportarSistemaDTO->getStrStaTipoBanco()){
        case self::$TBD_SQLSERVER:
          BancoSip::setBanco(InfraBancoSqlServer::newInstance($objImportarSistemaDTO->getStrBancoServidor(),
              $objImportarSistemaDTO->getStrBancoPorta(),
              $objImportarSistemaDTO->getStrBancoNome(),
              $objImportarSistemaDTO->getStrBancoUsuario(),
              $objImportarSistemaDTO->getStrBancoSenha()));
          break;

        case self::$TBD_MYSQL:
          BancoSip::setBanco(InfraBancoMySql::newInstance($objImportarSistemaDTO->getStrBancoServidor(),
              $objImportarSistemaDTO->getStrBancoPorta(),
              $objImportarSistemaDTO->getStrBancoNome(),
              $objImportarSistemaDTO->getStrBancoUsuario(),
              $objImportarSistemaDTO->getStrBancoSenha()));
          break;

        case self::$TBD_ORACLE:
          BancoSip::setBanco(InfraBancoOracle::newInstance($objImportarSistemaDTO->getStrBancoServidor(),
              $objImportarSistemaDTO->getStrBancoPorta(),
              $objImportarSistemaDTO->getStrBancoNome(),
              $objImportarSistemaDTO->getStrBancoUsuario(),
              $objImportarSistemaDTO->getStrBancoSenha()));
          break;

      }

			$dto = new SistemaDTO();
			$dto->retTodos();
			//$dto->setNumIdOrgao($objImportarSistemaDTO->getNumIdOrgaoSistemaOrigem());
      $dto->setStrSiglaOrgao($objImportarSistemaDTO->getStrSiglaOrgaoSistemaOrigem());
			$dto->setStrSigla($objImportarSistemaDTO->getStrSiglaOrigem());
      $objSistemaDTO = $this->consultar($dto);
			if ($objSistemaDTO==null){
			  $objInfraException->lancarValidacao('Sistema de Origem n�o encontrado.');
			}
			
      //Consulta sistema origem
      $objDadosSistemaDTO = new DadosSistemaDTO();
      $objDadosSistemaDTO->setObjSistemaDTO($objSistemaDTO);
			$this->obterDadosCopiaSistema($objDadosSistemaDTO);
			
			//Finaliza trabalhos com a base de origem
      BancoSip::setBanco(null);
			
			
			//grava dados para Sistema Destino
      $objSistemaDTO = $objDadosSistemaDTO->getObjSistemaDTO();
			$objSistemaDTO->setNumIdOrgao($objImportarSistemaDTO->getNumIdOrgaoSistemaDestino());
			$objSistemaDTO->setNumIdHierarquia($objImportarSistemaDTO->getNumIdHierarquiaDestino());
      $objSistemaDTO->setStrSigla($objImportarSistemaDTO->getStrSiglaDestino());
      $objSistemaDTO->setStrWebService(null);
      //$objDadosSistemaDTO->setObjSistemaDTO($this->cadastrar($objSistemaDTO));
			
      $this->gravarDadosCopiaSistema($objDadosSistemaDTO);
      
			//Auditoria

      return $objDadosSistemaDTO->getObjSistemaDTO();

    }catch(Exception $e){
      
      BancoSip::setBanco(null);
      
      throw new InfraException('Erro clonando Sistema.',$e);
    }
  }

  public function clonar(ClonarSistemaDTO $objClonarSistemaDTO) {
    try{

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('sistema_clonar',__METHOD__,$objClonarSistemaDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if (InfraString::isBolVazia($objClonarSistemaDTO->getNumIdOrgaoSistemaOrigem())){
        $objInfraException->adicionarValidacao('�rg�o do Sistema Origem n�o informado.');
      }

      if (InfraString::isBolVazia($objClonarSistemaDTO->getNumIdSistemaOrigem())){
        $objInfraException->adicionarValidacao('Sistema Origem n�o informado.');
      }

      if (InfraString::isBolVazia($objClonarSistemaDTO->getNumIdOrgaoSistemaDestino())){
        $objInfraException->adicionarValidacao('�rg�o do Sistema Destino n�o informado.');
      }
			
      if (InfraString::isBolVazia($objClonarSistemaDTO->getStrSiglaDestino())){
        $objInfraException->adicionarValidacao('Sigla do Sistema Destino n�o informada.');
      }
      
			$dto = new SistemaDTO();
			$dto->retNumIdSistema();
			$dto->setNumIdOrgao($objClonarSistemaDTO->getNumIdOrgaoSistemaDestino());
			$dto->setStrSigla($objClonarSistemaDTO->getStrSiglaDestino());
			if (count($this->listar($dto))>0){
			  $objInfraException->adicionarValidacao('J� existe um sistema no �rg�o destino com esta sigla.');
			}

      $objInfraException->lancarValidacoes();

			
      //Consulta sistema origem
      $objSistemaDTO = new SistemaDTO();
      $objSistemaDTO->retTodos();
			$objSistemaDTO->setNumIdOrgao($objClonarSistemaDTO->getNumIdOrgaoSistemaOrigem());
			$objSistemaDTO->setNumIdSistema($objClonarSistemaDTO->getNumIdSistemaOrigem());

      $objDadosSistemaDTO = new DadosSistemaDTO();
      $objDadosSistemaDTO->setObjSistemaDTO($this->consultar($objSistemaDTO));
			
			//Le dados para o Sistema Origem
			$this->obterDadosCopiaSistema($objDadosSistemaDTO);
			
			//grava dados para Sistema Destino
      $objSistemaDTO = $objDadosSistemaDTO->getObjSistemaDTO();
			$objSistemaDTO->setNumIdOrgao($objClonarSistemaDTO->getNumIdOrgaoSistemaDestino());
      $objSistemaDTO->setStrSigla($objClonarSistemaDTO->getStrSiglaDestino());
      $objSistemaDTO->setStrWebService(null);
      //$objDadosSistemaDTO->setObjSistemaDTO($this->cadastrar($objSistemaDTO));
			
      $this->gravarDadosCopiaSistema($objDadosSistemaDTO);
      
			//Auditoria

      return $objDadosSistemaDTO->getObjSistemaDTO();

    }catch(Exception $e){
      throw new InfraException('Erro clonando Sistema.',$e);
    }
  }

	protected function obterDadosCopiaSistemaControlado(DadosSistemaDTO $objDadosSistemaDTO){

      //Recursos
      $objRecursoDTO = new RecursoDTO();
      $objRecursoDTO->setBolExclusaoLogica(false);
      $objRecursoDTO->retTodos();
      $objRecursoDTO->setNumIdSistema($objDadosSistemaDTO->getObjSistemaDTO()->getNumIdSistema());
      $objRecursoRN = new RecursoRN();
      $objDadosSistemaDTO->setArrObjRecursoDTO($objRecursoRN->listar($objRecursoDTO));

      //Perfis
      $objPerfilDTO = new PerfilDTO();
      $objPerfilDTO->setBolExclusaoLogica(false);
      $objPerfilDTO->retTodos();
      $objPerfilDTO->setNumIdSistema($objDadosSistemaDTO->getObjSistemaDTO()->getNumIdSistema());
      $objPerfilRN = new PerfilRN();
      $objDadosSistemaDTO->setArrObjPerfilDTO($objPerfilRN->listar($objPerfilDTO));
      
      //Recursos dos perfis
      $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
      $objRelPerfilRecursoDTO->retTodos();
      $objRelPerfilRecursoDTO->setNumIdSistema($objDadosSistemaDTO->getObjSistemaDTO()->getNumIdSistema());
      $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
      $objDadosSistemaDTO->setArrObjRelPerfilRecursoDTO($objRelPerfilRecursoRN->listar($objRelPerfilRecursoDTO));
      
      //Menus
      $objMenuDTO = new MenuDTO();
      $objMenuDTO->setBolExclusaoLogica(false);
      $objMenuDTO->retTodos();
      $objMenuDTO->setNumIdSistema($objDadosSistemaDTO->getObjSistemaDTO()->getNumIdSistema());
      $objMenuRN = new MenuRN();
			$arrObjMenuDTO = $objMenuRN->listar($objMenuDTO);
			for($i=0;$i<count($arrObjMenuDTO);$i++){
        //Clona itens de menu associados
  			$objItemMenuDTO = new ItemMenuDTO();
  			$objItemMenuDTO->setBolExclusaoLogica(false);
  			$objItemMenuDTO->retTodos();
  			$objItemMenuDTO->setNumIdMenu($arrObjMenuDTO[$i]->getNumIdMenu());
  			$objItemMenuDTO->setNumIdSistema($objDadosSistemaDTO->getObjSistemaDTO()->getNumIdSistema());
  			$objItemMenuRN = new ItemMenuRN();
  			$arrObjMenuDTO[$i]->setArrObjItemMenuDTO($objItemMenuRN->listarHierarquia($objItemMenuDTO));
			}			
			$objDadosSistemaDTO->setArrObjMenuDTO($arrObjMenuDTO);
  
      //Itens de menu dos perfis
      $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
      $objRelPerfilItemMenuDTO->retTodos();
      $objRelPerfilItemMenuDTO->setNumIdSistema($objDadosSistemaDTO->getObjSistemaDTO()->getNumIdSistema());
      $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
      $objDadosSistemaDTO->setArrObjRelPerfilItemMenuDTO($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));
		
	}
	
	protected function gravarDadosCopiaSistemaControlado(DadosSistemaDTO $objDadosSistemaDTO){
      
	    $objSistemaDTO = $this->cadastrar($objDadosSistemaDTO->getObjSistemaDTO());
	    
      //cadastra usu�rio atual como administrador
      $objAdministradorSistemaDTO = new AdministradorSistemaDTO();
      $objAdministradorSistemaDTO->setNumIdSistema($objSistemaDTO->getNumIdSistema());
      $objAdministradorSistemaDTO->setNumIdUsuario(SessaoSip::getInstance()->getNumIdUsuario());
      
      $objAdministradorSistemaRN = new AdministradorSistemaRN();
      $objAdministradorSistemaRN->cadastrar($objAdministradorSistemaDTO);
      
      //Clona recursos
      $objRecursoRN = new RecursoRN();
      $arrObjRecursoDTO =$objDadosSistemaDTO->getArrObjRecursoDTO();
      //Prepara array com mapeamento dos Ids antigos para os novos
      $arrRecursos = array();      
      foreach($arrObjRecursoDTO as $dto){
        $numIdOriginal = $dto->getNumIdRecurso();
        $dto->setNumIdSistema($objSistemaDTO->getNumIdSistema());
        $ret = $objRecursoRN->cadastrar($dto);
        $arrRecursos[$numIdOriginal] = $ret->getNumIdRecurso();
      }

      //Clona Perfis
      $objPerfilRN = new PerfilRN();
      $arrObjPerfilDTO = $objDadosSistemaDTO->getArrObjPerfilDTO();
      //Prepara array com mapeamento dos Ids antigos para os novos
      $arrPerfis = array();      
      foreach($arrObjPerfilDTO as $dto){
        $numIdOriginal = $dto->getNumIdPerfil();
        $dto->setNumIdSistema($objSistemaDTO->getNumIdSistema());
        $ret = $objPerfilRN->cadastrar($dto);
        $arrPerfis[$numIdOriginal] = $ret->getNumIdPerfil();
      }
      
      //Clona recursos dos perfis
      $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
      $arrObjRelPerfilRecursoDTO = $objDadosSistemaDTO->getArrObjRelPerfilRecursoDTO();
      foreach($arrObjRelPerfilRecursoDTO as $dto){
        $dto->setNumIdSistema($objSistemaDTO->getNumIdSistema());
        $dto->setNumIdRecurso($arrRecursos[$dto->getNumIdRecurso()]);
        $dto->setNumIdPerfil($arrPerfis[$dto->getNumIdPerfil()]);
        $objRelPerfilRecursoRN->cadastrar($dto);
      }
      
      
      //Clona Menus
      $objMenuRN = new MenuRN();
      $arrObjMenuDTO = $objDadosSistemaDTO->getArrObjMenuDTO();
      $arrObjItemMenuDTO = array();
      //Prepara array com mapeamento dos Ids antigos para os novos
      $arrMenus = array();      
      foreach($arrObjMenuDTO as $dto){
        $numIdMenuOriginal = $dto->getNumIdMenu();
        $dto->setNumIdSistema($objSistemaDTO->getNumIdSistema());
        $ret = $objMenuRN->cadastrar($dto);
        $arrMenus[$numIdMenuOriginal] = $ret->getNumIdMenu();
        
        $arrObjItemMenuDTO = array_merge($arrObjItemMenuDTO, $dto->getArrObjItemMenuDTO());
      }
      
      //Clona itens de menu associados
			$objItemMenuRN = new ItemMenuRN();

			//$arrObjItemMenuDTO = $dto->getArrObjItemMenuDTO();
			
      //Prepara array com mapeamento dos Ids antigos para os novos
      $arrItensMenus = array();      

      //Tem que adicionar partindo da raiz at� as folhas
			//Descobre qual o n�vel mais baixo
			$numNivel=0;
			foreach($arrObjItemMenuDTO as $dto){
			  if (strlen($dto->getStrRamificacao())>$numNivel){
			    $numNivel = strlen($dto->getStrRamificacao());
			  }
			}
			
			for($i=0;$i<=$numNivel;$i++){
				foreach($arrObjItemMenuDTO as $dto){
				  if (strlen($dto->getStrRamificacao())==$i){
				    //Adiciona Item
            $numIdItemMenuOriginal = $dto->getNumIdItemMenu();
            $dto->setNumIdSistema($objSistemaDTO->getNumIdSistema());
            $dto->setNumIdMenu($arrMenus[$dto->getNumIdMenu()]);
            if ($dto->getNumIdMenuPai()!=null && $dto->getNumIdItemMenuPai()!=null){
              $dto->setNumIdMenuPai($arrMenus[$dto->getNumIdMenuPai()]);
              $dto->setNumIdItemMenuPai($arrItensMenus[$dto->getNumIdItemMenuPai()]);
            }else{
              $dto->setNumIdMenuPai(null);
              $dto->setNumIdItemMenuPai(null);
            }
            $dto->setNumIdRecurso($arrRecursos[$dto->getNumIdRecurso()]);
            $ret = $objItemMenuRN->cadastrar($dto);
            $arrItensMenus[$numIdItemMenuOriginal] = $ret->getNumIdItemMenu();
				  }
				}
			}
			      
      //Clona itens de menu dos perfis
      $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
      $arrObjRelPerfilItemMenuDTO = $objDadosSistemaDTO->getArrObjRelPerfilItemMenuDTO();
      foreach($arrObjRelPerfilItemMenuDTO as $dto){
        $dto->setNumIdSistema($objSistemaDTO->getNumIdSistema());
        $dto->setNumIdPerfil($arrPerfis[$dto->getNumIdPerfil()]);
        $dto->setNumIdMenu($arrMenus[$dto->getNumIdMenu()]);
        $dto->setNumIdItemMenu($arrItensMenus[$dto->getNumIdItemMenu()]);
        $dto->setNumIdRecurso($arrRecursos[$dto->getNumIdRecurso()]);
        $objRelPerfilItemMenuRN->cadastrar($dto);
      }
      
      //Auditoria
	}
  
  protected function cadastrarControlado(SistemaDTO $objSistemaDTO) {
    try{

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('sistema_cadastrar',__METHOD__,$objSistemaDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdOrgao($objSistemaDTO,$objInfraException);
      $this->validarNumIdHierarquia($objSistemaDTO,$objInfraException);
			$this->validarStrSigla($objSistemaDTO,$objInfraException);
			$this->validarStrDescricao($objSistemaDTO,$objInfraException);
			$this->validarStrPaginaInicial($objSistemaDTO,$objInfraException);
			$this->validarStrWebService($objSistemaDTO,$objInfraException);
			$this->validarStrSinAtivo($objSistemaDTO,$objInfraException);
			
			if ($objSistemaDTO->isSetStrNomeArquivo()){
			  $this->validarStrNomeArquivo($objSistemaDTO, $objInfraException);
			}
			
      $objInfraException->lancarValidacoes();
      
      if ($objSistemaDTO->isSetStrNomeArquivo() && !InfraString::isBolVazia($objSistemaDTO->getStrNomeArquivo())) {
        $objSistemaDTO->setStrLogo(base64_encode(file_get_contents(DIR_SIP_TEMP.'/'.$objSistemaDTO->getStrNomeArquivo())));
      }
      
      $objSistemaBD = new SistemaBD($this->getObjInfraIBanco());
      $ret = $objSistemaBD->cadastrar($objSistemaDTO);

      //replica todos orgaos e contextos do sistema
      $objOrgaoDTO = new OrgaoDTO();
      $objOrgaoDTO->retNumIdOrgao();
      
      $objOrgaoRN = new OrgaoRN();
      $arrObjOrgaoDTO = $objOrgaoRN->listar($objOrgaoDTO);
      
      
      foreach($arrObjOrgaoDTO as $objOrgaoDTO){
        $objReplicacaoOrgaoDTO = new ReplicacaoOrgaoDTO();
        $objReplicacaoOrgaoDTO->setStrStaOperacao('C');
        $objReplicacaoOrgaoDTO->setNumIdOrgao($objOrgaoDTO->getNumIdOrgao());
        $objReplicacaoOrgaoDTO->setNumIdSistema($ret->getNumIdSistema());
        $this->replicarOrgao($objReplicacaoOrgaoDTO);
        
        $objContextoDTO = new ContextoDTO();
        $objContextoDTO->retNumIdContexto();
        $objContextoDTO->setNumIdOrgao($objOrgaoDTO->getNumIdOrgao());
        $objContextoRN = new ContextoRN();
        $arrObjContextoDTO = $objContextoRN->listar($objContextoDTO);
        
        
        foreach($arrObjContextoDTO as $objContextoDTO){
          $objReplicacaoContextoDTO = new ReplicacaoContextoDTO();
          $objReplicacaoContextoDTO->setStrStaOperacao('C');
          $objReplicacaoContextoDTO->setNumIdContexto($objContextoDTO->getNumIdContexto());
          $objReplicacaoContextoDTO->setNumIdSistema($ret->getNumIdSistema());
          $this->replicarContexto($objReplicacaoContextoDTO);
        }
      }

      if ($objSistemaDTO->isSetStrNomeArquivo() && !InfraString::isBolVazia($objSistemaDTO->getStrNomeArquivo())){
        unlink(DIR_SIP_TEMP . '/' . $objSistemaDTO->getStrNomeArquivo());
      }

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Sistema.',$e);
    }
  }

  protected function alterarControlado(SistemaDTO $objSistemaDTO){
    try {

      //Valida Permissao
  	   SessaoSip::getInstance()->validarAuditarPermissao('sistema_alterar',__METHOD__,$objSistemaDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();


      if ($objSistemaDTO->isSetNumIdOrgao()){
        $this->validarNumIdOrgao($objSistemaDTO,$objInfraException);
      }
      
      if ($objSistemaDTO->isSetNumIdHierarquia()){
        $this->validarNumIdHierarquia($objSistemaDTO,$objInfraException);
      }
      
      if ($objSistemaDTO->isSetStrSigla()){
			  $this->validarStrSigla($objSistemaDTO,$objInfraException);
      }
      
      if ($objSistemaDTO->isSetStrDescricao()){
			  $this->validarStrDescricao($objSistemaDTO,$objInfraException);
      }
      
      if ($objSistemaDTO->isSetStrPaginaInicial()){
			  $this->validarStrPaginaInicial($objSistemaDTO,$objInfraException);
      }
      
      if ($objSistemaDTO->isSetStrWebService()){
			  $this->validarStrWebService($objSistemaDTO,$objInfraException);
      }

      if ($objSistemaDTO->isSetStrSinAtivo()){
			  $this->validarStrSinAtivo($objSistemaDTO,$objInfraException);
      }

      if ($objSistemaDTO->isSetStrNomeArquivo()) {
        $this->validarStrNomeArquivo($objSistemaDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      
      if ($objSistemaDTO->isSetStrNomeArquivo() && !InfraString::isBolVazia($objSistemaDTO->getStrNomeArquivo())) {
        if ($objSistemaDTO->getStrNomeArquivo()=="*REMOVER*") {
          $objSistemaDTO->setStrLogo(null);
        } else {
          $objSistemaDTO->setStrLogo(base64_encode(file_get_contents(DIR_SIP_TEMP.'/'.$objSistemaDTO->getStrNomeArquivo())));
        }
      }
      
      $objSistemaBD = new SistemaBD($this->getObjInfraIBanco());
      $objSistemaBD->alterar($objSistemaDTO);

      
      //replica todos orgaos e contextos do sistema
      $objOrgaoDTO = new OrgaoDTO();
      $objOrgaoDTO->retNumIdOrgao();
      
      $objOrgaoRN = new OrgaoRN();
      $arrObjOrgaoDTO = $objOrgaoRN->listar($objOrgaoDTO);
      
      
      foreach($arrObjOrgaoDTO as $objOrgaoDTO){
        $objReplicacaoOrgaoDTO = new ReplicacaoOrgaoDTO();
        $objReplicacaoOrgaoDTO->setStrStaOperacao('C');
        $objReplicacaoOrgaoDTO->setNumIdOrgao($objOrgaoDTO->getNumIdOrgao());
        $objReplicacaoOrgaoDTO->setNumIdSistema($objSistemaDTO->getNumIdSistema());
        $this->replicarOrgao($objReplicacaoOrgaoDTO);
        
        $objContextoDTO = new ContextoDTO();
        $objContextoDTO->retNumIdContexto();
        $objContextoDTO->setNumIdOrgao($objOrgaoDTO->getNumIdOrgao());
        $objContextoRN = new ContextoRN();
        $arrObjContextoDTO = $objContextoRN->listar($objContextoDTO);
        
        
        foreach($arrObjContextoDTO as $objContextoDTO){
          $objReplicacaoContextoDTO = new ReplicacaoContextoDTO();
          $objReplicacaoContextoDTO->setStrStaOperacao('C');
          $objReplicacaoContextoDTO->setNumIdContexto($objContextoDTO->getNumIdContexto());
          $objReplicacaoContextoDTO->setNumIdSistema($objSistemaDTO->getNumIdSistema());
          $this->replicarContexto($objReplicacaoContextoDTO);
        }
      }

      if ($objSistemaDTO->isSetStrNomeArquivo() && !InfraString::isBolVazia($objSistemaDTO->getStrNomeArquivo()) && $objSistemaDTO->getStrNomeArquivo()!="*REMOVER*") {
        unlink(DIR_SIP_TEMP.'/'.$objSistemaDTO->getStrNomeArquivo());
      }
      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Sistema.',$e);
    }
  }

  protected function excluirControlado($arrObjSistemaDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('sistema_excluir',__METHOD__,$arrObjSistemaDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

			
			for($i=0;$i<count($arrObjSistemaDTO);$i++){
				//Verifica se existem permissoes no sistema
				$objPermissaoDTO = new PermissaoDTO();
				$objPermissaoDTO->retNumIdSistema();
				$objPermissaoDTO->setNumIdSistema($arrObjSistemaDTO[$i]->getNumIdSistema());
				$objPermissaoRN = new PermissaoRN();
				if (count($objPermissaoRN->listar($objPermissaoDTO))>0){
					$objInfraException->adicionarValidacao('Existem permiss�es associadas.');
				}
				
        $objInfraException->lancarValidacoes();
			}


      $objSistemaBD = new SistemaBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjSistemaDTO);$i++){

				//Exclui perfis associados
				$objPerfilDTO = new PerfilDTO();
				$objPerfilDTO->retNumIdPerfil();
				$objPerfilDTO->retNumIdSistema();
				$objPerfilDTO->setNumIdSistema($arrObjSistemaDTO[$i]->getNumIdSistema());
				$objPerfilDTO->setBolExclusaoLogica(false);
				$objPerfilRN = new PerfilRN();
				$objPerfilRN->excluir($objPerfilRN->listar($objPerfilDTO));
        
				
				//Exclui administradores de sistemas
				$objAdministradorSistemaDTO = new AdministradorSistemaDTO();
				$objAdministradorSistemaDTO->retNumIdUsuario();
				$objAdministradorSistemaDTO->retNumIdSistema();
				$objAdministradorSistemaDTO->setNumIdSistema($arrObjSistemaDTO[$i]->getNumIdSistema());
				$objAdministradorSistemaRN = new AdministradorSistemaRN();
				$objAdministradorSistemaRN->excluir($objAdministradorSistemaRN->listar($objAdministradorSistemaDTO));
				
        
				//Exclui menus asssociados sistemas
				$objMenuDTO = new MenuDTO();
				$objMenuDTO->retNumIdMenu();
				$objMenuDTO->setNumIdSistema($arrObjSistemaDTO[$i]->getNumIdSistema());
				$objMenuDTO->setBolExclusaoLogica(false);
				$objMenuRN = new MenuRN();
				$objMenuRN->excluir($objMenuRN->listar($objMenuDTO));
				
				
				//Exclui recursos associados
				$objRecursoDTO = new RecursoDTO();
				$objRecursoDTO->retNumIdRecurso();
				$objRecursoDTO->retNumIdSistema();
				$objRecursoDTO->setNumIdSistema($arrObjSistemaDTO[$i]->getNumIdSistema());
				$objRecursoDTO->setBolExclusaoLogica(false);
				$objRecursoRN = new RecursoRN();
				$objRecursoRN->excluir($objRecursoRN->listar($objRecursoDTO));

        //Exclui logins associados
				$objLoginDTO = new LoginDTO();
				$objLoginDTO->retStrIdLogin();
				$objLoginDTO->retNumIdSistema();
				$objLoginDTO->retNumIdContexto();
				$objLoginDTO->retNumIdUsuario();
				
				$objLoginDTO->setNumIdSistema($arrObjSistemaDTO[$i]->getNumIdSistema());
				$objLoginRN = new LoginRN();
				$objLoginRN->excluir($objLoginRN->listar($objLoginDTO));
				
        $objSistemaBD->excluir($arrObjSistemaDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Sistema.',$e);
    }
  }

  protected function desativarControlado($arrObjSistemaDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('sistema_desativar',__METHOD__,$arrObjSistemaDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objSistemaBD = new SistemaBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjSistemaDTO);$i++){
        $objSistemaBD->desativar($arrObjSistemaDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Sistema.',$e);
    }
  }

  protected function reativarControlado($arrObjSistemaDTO){
    try {

      //Valida Permissao
      SessaoSip::getInstance()->validarAuditarPermissao('sistema_reativar',__METHOD__,$arrObjSistemaDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objSistemaBD = new SistemaBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjSistemaDTO);$i++){
        $objSistemaBD->reativar($arrObjSistemaDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Sistema.',$e);
    }
  }

  protected function consultarConectado(SistemaDTO $objSistemaDTO){
    try {
      /////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('sistema_consultar',__METHOD__,$objSistemaDTO);
      /////////////////////////////////////////////////////////////////
			
      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objSistemaBD = new SistemaBD($this->getObjInfraIBanco());
      $ret = $objSistemaBD->consultar($objSistemaDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Sistema.',$e);
    }
  }

  protected function listarConectado(SistemaDTO $objSistemaDTO) {
    try {
      ////////////////////////////////////////////////////////////////////// 
      //SessaoSip::getInstance()->validarAuditarPermissao('sistema_listar',__METHOD__,$objSistemaDTO);
			//////////////////////////////////////////////////////////////////////


      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objSistemaBD = new SistemaBD($this->getObjInfraIBanco());
      $ret = $objSistemaBD->listar($objSistemaDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Sistemas.',$e);
    }
  }

  protected function contarConectado(SistemaDTO $objSistemaDTO) {
    try {
      ////////////////////////////////////////////////////////////////////// 
      //SessaoSip::getInstance()->validarAuditarPermissao('sistema_contar',__METHOD__,$objSistemaDTO);
			//////////////////////////////////////////////////////////////////////


      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objSistemaBD = new SistemaBD($this->getObjInfraIBanco());
      $ret = $objSistemaBD->contar($objSistemaDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro contando Sistemas.',$e);
    }
  }
  
	/**
	Lista todos os sistemas onde o usuario � administrador (se o usuario 
  administra o SIP entao lista todos os sistemas do �rg�o do SIP administrado)
	*/
  protected function listarSipConectado(SistemaDTO $objSistemaDTO) {
    try {

			/////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('sistema_listar',__METHOD__,$objSistemaDTO);
			/////////////////////////////////////////////////////////////////


      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

			//Solicita retorno do ID do �rg�o
			$objSistemaDTO->retNumIdOrgao();
      $arrObjSistemaDTO = $this->listar($objSistemaDTO);

			//Obtem sistemas administrados pelo usuario
			$objAcessoDTO = new AcessoDTO();
			$objAcessoDTO->setNumTipo(AcessoDTO::$ADMINISTRADOR);
			$objAcessoRN = new AcessoRN();
			$arrObjAcessoDTO = $objAcessoRN->obterAcessos($objAcessoDTO);
			
			$arrSistemasAdicionados = array();
			
			$ret = array();
			
			//Se tem permiss�o no SIP ent�o carrega todos os sistemas do �rg�o
			foreach($arrObjAcessoDTO as $acesso){
			  if (strtoupper($acesso->getStrSiglaSistema())==SessaoSip::getInstance()->getStrSiglaSistema()){
					foreach($arrObjSistemaDTO as $sistema){
  						if ($sistema->getNumIdOrgao()==$acesso->getNumIdOrgaoSistema()){
  							if(!in_array($sistema->getNumIdSistema(),$arrSistemasAdicionados)){
  								$arrSistemasAdicionados[] = $sistema->getNumIdSistema();
  								$ret[] = $sistema;
  							}
  						}
					 }
			   }
			 }
			 
			//Adiciona sistemas administrados restantes
		  foreach($arrObjAcessoDTO as $acesso){
				foreach($arrObjSistemaDTO as $sistema){
					if ($acesso->getNumIdSistema()==$sistema->getNumIdSistema()){
						if(!in_array($sistema->getNumIdSistema(),$arrSistemasAdicionados)){
							$arrSistemasAdicionados[] = $sistema->getNumIdSistema();
							$ret[] = $sistema;
						}
					}
				}
			}
			
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Sistemas SIP.',$e);
    }
  }

  protected function listarAdministradosConectado(SistemaDTO $objSistemaDTO) {
    try {

			/////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('sistema_listar',__METHOD__,$objSistemaDTO);
			/////////////////////////////////////////////////////////////////


      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $arrObjSistemaDTO = $this->listar($objSistemaDTO);

			//Obtem sistemas acessados pelo usuario
			$objAcessoDTO = new AcessoDTO();
			$objAcessoDTO->setNumTipo(AcessoDTO::$ADMINISTRADOR);
			$objAcessoRN = new AcessoRN();
			$arrObjAcessoDTO = $objAcessoRN->obterAcessos($objAcessoDTO);
			
			$ret = InfraArray::joinArrInfraDTO($arrObjSistemaDTO, 'IdSistema', $arrObjAcessoDTO, 'IdSistema');
			
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Sistemas administrados.',$e);
    }
  }

  protected function listarCoordenadosConectado(SistemaDTO $objSistemaDTO) {
    try {
  
      /////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('sistema_listar',__METHOD__,$objSistemaDTO);
      /////////////////////////////////////////////////////////////////
  
      //Regras de Negocio
      //$objInfraException = new InfraException();
  
      //$objInfraException->lancarValidacoes();
      
      $ret = array();

      //Obtem sistemas acessados pelo usuario
      $objAcessoDTO = new AcessoDTO();
      $objAcessoDTO->setNumTipo(AcessoDTO::$COORDENADOR_PERFIL);
      $objAcessoRN = new AcessoRN();
      $arrObjAcessoDTO = $objAcessoRN->obterAcessos($objAcessoDTO);
      
      $arrObjAcessoDTO = InfraArray::distinctArrInfraDTO($arrObjAcessoDTO,'IdSistema');
      
      if (count($arrObjAcessoDTO)){
        
        $objSistemaDTO->retNumIdSistema();
        $objSistemaDTO->setNumIdSistema(InfraArray::converterArrInfraDTO($arrObjAcessoDTO,'IdSistema'),InfraDTO::$OPER_IN);
        
        $ret = $this->listar($objSistemaDTO);
      }
      	
      //Auditoria
  
      return $ret;
  
    }catch(Exception $e){
      throw new InfraException('Erro listando Sistemas coordenados.',$e);
    }
  }
  
  protected function listarAutorizadosConectado(SistemaDTO $objSistemaDTO) {
    try {

			/////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('sistema_listar',__METHOD__,$objSistemaDTO);
			/////////////////////////////////////////////////////////////////


      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $arrObjSistemaDTO = $this->listar($objSistemaDTO);

			//Obtem sistemas acessados pelo usuario
			$objAcessoDTO = new AcessoDTO();
			$objAcessoDTO->setNumTipo(AcessoDTO::$ADMINISTRADOR | AcessoDTO::$COORDENADOR_PERFIL | AcessoDTO::$COORDENADOR_UNIDADE);
			$objAcessoRN = new AcessoRN();
			$arrObjAcessoDTO = $objAcessoRN->obterAcessos($objAcessoDTO);
			
			$arrObjAcessoDTO = InfraArray::distinctArrInfraDTO($arrObjAcessoDTO,'IdSistema');
			
			$ret = InfraArray::joinArrInfraDTO($arrObjSistemaDTO, 'IdSistema', $arrObjAcessoDTO, 'IdSistema');
			
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Sistemas autorizados.',$e);
    }
  }

  protected function listarPessoaisConectado(SistemaDTO $objSistemaDTO) {
    try {

			/////////////////////////////////////////////////////////////////
      //SessaoSip::getInstance()->validarAuditarPermissao('sistema_listar',__METHOD__,$objSistemaDTO);
			/////////////////////////////////////////////////////////////////


      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $arrObjSistemaDTO = $this->listar($objSistemaDTO);

			//Obtem sistemas acessados pelo usuario
			$objAcessoDTO = new AcessoDTO();
			$objAcessoDTO->setNumTipo(AcessoDTO::$PERMISSAO);
			$objAcessoRN = new AcessoRN();
			$arrObjAcessoDTO = $objAcessoRN->obterAcessos($objAcessoDTO);
			
			$arrObjAcessoDTO = InfraArray::distinctArrInfraDTO($arrObjAcessoDTO,'IdSistema');
			
			$ret = InfraArray::joinArrInfraDTO($arrObjSistemaDTO, 'IdSistema', $arrObjAcessoDTO, 'IdSistema');
			
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Sistemas com permiss�o.',$e);
    }
  }
  
  protected function listarUnidadesConectado(SistemaDTO $parObjSistemaDTO){
    try{
          
      //Busca hierarquia do sistema
      $objSistemaDTO = new SistemaDTO();
      $objSistemaDTO->retNumIdSistema();
      $objSistemaDTO->retNumIdHierarquia();
      $objSistemaDTO->setNumIdSistema($parObjSistemaDTO->getNumIdSistema());
      
      $objSistemaDTO = $this->consultar($objSistemaDTO);
      
      if ($objSistemaDTO==null){
        throw new InfraException('Sistema n�o encontrado.');
      }
    	  
      $objRelHierarquiaUnidadeDTO = new RelHierarquiaUnidadeDTO();
    	$objRelHierarquiaUnidadeDTO->retArrUnidadesInferiores();
    	$objRelHierarquiaUnidadeDTO->retArrUnidadesSuperiores();
      $objRelHierarquiaUnidadeDTO->setNumIdHierarquia($objSistemaDTO->getNumIdHierarquia());

      if ($parObjSistemaDTO->isSetNumIdUnidade()){
        $objRelHierarquiaUnidadeDTO->setNumIdUnidade($parObjSistemaDTO->getNumIdUnidade());
      }

      $objRelHierarquiaUnidadeRN = new RelHierarquiaUnidadeRN();
      $arrHierarquia = $objRelHierarquiaUnidadeRN->listarHierarquia($objRelHierarquiaUnidadeDTO);
      
      $ret = array();
      
      foreach($arrHierarquia as $objRelHierarquiaUnidadeDTO){
        
        //ATEN��O: os elementos devem ser adicionados no array seguindo a ordem dos �ndices (posi��o 0, 1, 2, ...)
        //Ao enviar via web-services o PHP ignora o valor do �ndice passado na constante e assume a ordem em que foram adicionados.
        
      	$numIdUnidade = $objRelHierarquiaUnidadeDTO->getNumIdUnidade();
      	
      	$ret[$numIdUnidade] = array();
      	$ret[$numIdUnidade][InfraSip::$WS_UNIDADE_ID] = $numIdUnidade;
      	$ret[$numIdUnidade][InfraSip::$WS_UNIDADE_ORGAO_ID] = $objRelHierarquiaUnidadeDTO->getNumIdOrgaoUnidade();
      	$ret[$numIdUnidade][InfraSip::$WS_UNIDADE_SIGLA] = $objRelHierarquiaUnidadeDTO->getStrSiglaUnidade();
      	$ret[$numIdUnidade][InfraSip::$WS_UNIDADE_DESCRICAO] = $objRelHierarquiaUnidadeDTO->getStrDescricaoUnidade();
      	$ret[$numIdUnidade][InfraSip::$WS_UNIDADE_SIN_ATIVO] = $objRelHierarquiaUnidadeDTO->getStrSinAtivo();
      	$ret[$numIdUnidade][InfraSip::$WS_UNIDADE_SUBUNIDADES] = InfraArray::converterArrInfraDTO($objRelHierarquiaUnidadeDTO->getArrUnidadesInferiores(),'IdUnidade');
      	$ret[$numIdUnidade][InfraSip::$WS_UNIDADE_UNIDADES_SUPERIORES] = InfraArray::converterArrInfraDTO($objRelHierarquiaUnidadeDTO->getArrUnidadesSuperiores(),'IdUnidade');
        $ret[$numIdUnidade][InfraSip::$WS_UNIDADE_ID_ORIGEM] = $objRelHierarquiaUnidadeDTO->getStrIdOrigemUnidade();
      }
    	  
      return $ret;
      
    }catch(Exception $e){
      throw new InfraException('Erro carregando unidades do sistema.',$e);
    }
    
  }

  private function validarStrNomeArquivo(SistemaDTO $objSistemaDTO, InfraException $objInfraException){
    if (!InfraString::isBolVazia($objSistemaDTO->getStrNomeArquivo()) && $objSistemaDTO->getStrNomeArquivo()!="*REMOVER*"){
      if (!file_exists(DIR_SIP_TEMP.'/'.$objSistemaDTO->getStrNomeArquivo())) {
        $objInfraException->adicionarValidacao('N�o foi poss�vel abrir arquivo da imagem.');
      }
    }
  }
  
  private function validarNumIdOrgao(SistemaDTO $objSistemaDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objSistemaDTO->getNumIdOrgao())){
      $objInfraException->adicionarValidacao('�rg�o n�o informado.');
    }
  }
  
  private function validarNumIdHierarquia(SistemaDTO $objSistemaDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objSistemaDTO->getNumIdHierarquia())){
      $objInfraException->adicionarValidacao('Hierarquia n�o informada.');
    }
  }
	
  private function validarStrSigla(SistemaDTO $objSistemaDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objSistemaDTO->getStrSigla())){
      $objInfraException->adicionarValidacao('Sigla n�o informada.');
    }

    $objSistemaDTO->setStrSigla(trim($objSistemaDTO->getStrSigla()));

    if (strlen($objSistemaDTO->getStrSigla())>15){
      $objInfraException->adicionarValidacao('Sigla possui tamanho superior a 15 caracteres.');
    }

    $strSigla = $objSistemaDTO->getStrSigla();

    if (preg_match("/[^0-9a-zA-Z\-_]/", $strSigla)){
      $objInfraException->adicionarValidacao('Sigla possui caracter inv�lido.');
    }

    $dto = new SistemaDTO();
    $dto->setBolExclusaoLogica(false);
    $dto->retStrSinAtivo();
    $dto->setNumIdSistema($objSistemaDTO->getNumIdSistema(),InfraDTO::$OPER_DIFERENTE);
    $dto->setNumIdOrgao($objSistemaDTO->getNumIdOrgao());
    $dto->setStrSigla($objSistemaDTO->getStrSigla());
    $dto = $this->consultar($dto);
    if ($dto!=null){
      if ($dto->getStrSinAtivo()=='N'){
        $objInfraException->adicionarValidacao('Existe outro sistema inativo com a mesma sigla neste �rg�o.');
      }else{
        $objInfraException->adicionarValidacao('Existe outro sistema com a mesma sigla neste �rg�o.');
      }
    }
  }
	
  private function validarStrDescricao(SistemaDTO $objSistemaDTO, InfraException $objInfraException){
  	$objSistemaDTO->setStrDescricao(trim($objSistemaDTO->getStrDescricao()));
  	
    
    if (strlen($objSistemaDTO->getStrDescricao())>200){
      $objInfraException->adicionarValidacao('Descri��o possui tamanho superior a 200 caracteres.');
    }
    
  }

  private function validarStrPaginaInicial(SistemaDTO $objSistemaDTO, InfraException $objInfraException){
  	$objSistemaDTO->setStrPaginaInicial(trim($objSistemaDTO->getStrPaginaInicial()));
  	
    
    if (strlen($objSistemaDTO->getStrPaginaInicial())>255){
      $objInfraException->adicionarValidacao('Localiza��o da P�gina Inicial possui tamanho superior a 255 caracteres.');
    }
    
  }

  private function validarStrWebService(SistemaDTO $objSistemaDTO, InfraException $objInfraException){
  	$objSistemaDTO->setStrWebService(trim($objSistemaDTO->getStrWebService()));
  	
    
    if (strlen($objSistemaDTO->getStrWebService())>255){
      $objInfraException->adicionarValidacao('Localiza��o do Web Service possui tamanho superior a 255 caracteres.');
    }
    
  }
  
  private function validarStrSinAtivo(SistemaDTO $objSistemaDTO, InfraException $objInfraException){
    if ($objSistemaDTO->getStrSinAtivo()===null || ($objSistemaDTO->getStrSinAtivo()!=='S' && $objSistemaDTO->getStrSinAtivo()!=='N')){
      $objInfraException->adicionarValidacao('Sinalizador de Exclus�o L�gica inv�lido.');
    }
  }
	
  protected function listarHierarquiaConectado(SistemaDTO $objSistemaDTO){
		//Busca hierarquia do sistema
    $dto = new SistemaDTO();
    $dto->retNumIdHierarquia();
    $dto->setNumIdSistema($objSistemaDTO->getNumIdSistema());
    $dto = $this->consultar($dto);
    if ($objSistemaDTO==null){
      throw new InfraException('Sistema n�o encontrado.');
    }
		
    $objRelHierarquiaUnidadeDTO = new RelHierarquiaUnidadeDTO();
  	$objRelHierarquiaUnidadeDTO->retArrUnidadesInferiores();
    $objRelHierarquiaUnidadeDTO->setNumIdHierarquia($dto->getNumIdHierarquia());

    if ($objSistemaDTO->isSetNumIdUnidade()){
      $objRelHierarquiaUnidadeDTO->setNumIdUnidade($objSistemaDTO->getNumIdUnidade());
    }

    $objRelHierarquiaUnidadeRN = new RelHierarquiaUnidadeRN();
    $arrHierarquia = $objRelHierarquiaUnidadeRN->listarHierarquia($objRelHierarquiaUnidadeDTO);
    
    return $arrHierarquia;
  }  
  
  public function replicarRegraAuditoria(ReplicacaoRegraAuditoriaDTO $objReplicacaoRegraAuditoriaDTO) {
  
    try{
  
      $objInfraException = new InfraException();
       
      $objInfraParametro = new InfraParametro(BancoSip::getInstance());
       
      $objRegraAuditoriaDTO = new RegraAuditoriaDTO();
      $objRegraAuditoriaDTO->setBolExclusaoLogica(false);
      $objRegraAuditoriaDTO->retNumIdRegraAuditoria();
      $objRegraAuditoriaDTO->retStrDescricao();
      $objRegraAuditoriaDTO->retNumIdSistema();
      $objRegraAuditoriaDTO->retStrSinAtivo();
      $objRegraAuditoriaDTO->setNumIdRegraAuditoria($objReplicacaoRegraAuditoriaDTO->getNumIdRegraAuditoria());
       
      $objRegraAuditoriaRN = new RegraAuditoriaRN();
      $objRegraAuditoriaDTO = $objRegraAuditoriaRN->consultar($objRegraAuditoriaDTO);
  
      $objSistemaDTO = new SistemaDTO();
      $objSistemaDTO->retNumIdSistema();
      $objSistemaDTO->retStrSigla();
      $objSistemaDTO->retStrWebService();
      $objSistemaDTO->setNumIdSistema($objRegraAuditoriaDTO->getNumIdSistema());
  
      $objSistemaDTO = $this->consultar($objSistemaDTO);
  
      if ($objRegraAuditoriaDTO->getNumIdSistema() != $objInfraParametro->getValor('ID_SISTEMA_SIP') && InfraString::isBolVazia($objSistemaDTO->getStrWebService())){
        return;
      }
  
      $objRelRegraAuditoriaRecursoDTO = new RelRegraAuditoriaRecursoDTO();
      $objRelRegraAuditoriaRecursoDTO->retStrNomeRecurso();
      $objRelRegraAuditoriaRecursoDTO->setNumIdRegraAuditoria($objRegraAuditoriaDTO->getNumIdRegraAuditoria());
  
      $objRelRegraAuditoriaRecursoRN = new RelRegraAuditoriaRecursoRN();
      $arrObjRelRegraAuditoriaRecursoDTO = $objRelRegraAuditoriaRecursoRN->listar($objRelRegraAuditoriaRecursoDTO);
  
  
  
      if ($objRegraAuditoriaDTO->getNumIdSistema() == $objInfraParametro->getValor('ID_SISTEMA_SIP')){
  
        AuditoriaSip::getInstance()->replicarRegra($objReplicacaoRegraAuditoriaDTO->getStrStaOperacao(),
        $objRegraAuditoriaDTO->getNumIdRegraAuditoria(),
        $objRegraAuditoriaDTO->getStrDescricao(),
        $objRegraAuditoriaDTO->getStrSinAtivo(),
        InfraArray::converterArrInfraDTO($arrObjRelRegraAuditoriaRecursoDTO,'NomeRecurso'));
  
      }else{
  
        try {
  
          if(!@file_get_contents($objSistemaDTO->getStrWebService())){
          		throw new InfraException('Web Service n�o encontrado.');
          }
  
  
          $objWS = new SoapClient($objSistemaDTO->getStrWebService(), array('encoding'=>'ISO-8859-1'));
  
          $objWS->replicarRegraAuditoria($objReplicacaoRegraAuditoriaDTO->getStrStaOperacao(),
              $objRegraAuditoriaDTO->getNumIdRegraAuditoria(),
              $objRegraAuditoriaDTO->getStrDescricao(),
              $objRegraAuditoriaDTO->getStrSinAtivo(),
              InfraArray::converterArrInfraDTO($arrObjRelRegraAuditoriaRecursoDTO,'NomeRecurso'));
  
        } catch(Exception $e){
          throw new InfraException('Falha na chamada ao Web Service do sistema '.$objSistemaDTO->getStrSigla().'.',$e);
        }
      }
  
    }catch(Exception $e){
      throw new InfraException('Erro replicando regra de auditoria.',$e);
    }
  }
  
  public function replicarUsuario(ReplicacaoUsuarioDTO $objReplicacaoUsuarioDTO) {
    
    try{

      $objReplicacaoServicoDTO = new ReplicacaoServicoDTO();
      $objReplicacaoServicoDTO->setNumIdSistema($objReplicacaoUsuarioDTO->getNumIdSistema());
      $objReplicacaoServicoDTO->setStrNomeOperacao('replicarUsuario');
      $objReplicacaoServicoDTO = Replicacao::getInstance()->obterServico($objReplicacaoServicoDTO);
      
      if ($objReplicacaoServicoDTO != null){
        
        $objUsuarioDTO = new UsuarioDTO();
        $objUsuarioDTO->setBolExclusaoLogica(false);
        $objUsuarioDTO->retNumIdUsuario();
        $objUsuarioDTO->retNumIdOrgao();
        $objUsuarioDTO->retStrIdOrigem();
        $objUsuarioDTO->retStrSigla();
        $objUsuarioDTO->retStrNome();
        $objUsuarioDTO->retStrSinAtivo();

        if (is_array($objReplicacaoUsuarioDTO->getNumIdUsuario())){
          $arrIdUsuario = $objReplicacaoUsuarioDTO->getNumIdUsuario();
        }else{
          $arrIdUsuario = array($objReplicacaoUsuarioDTO->getNumIdUsuario());
        }

        $objUsuarioDTO->setNumIdUsuario($arrIdUsuario, InfraDTO::$OPER_IN);
        
        $objUsuarioRN = new UsuarioRN();
        $arrObjUsuarioDTO = $objUsuarioRN->listar($objUsuarioDTO);

        if (count($arrObjUsuarioDTO)) {

          $arr = array();
          foreach ($arrObjUsuarioDTO as $objUsuarioDTO) {
            $arr[] = array(
                'StaOperacao' => $objReplicacaoUsuarioDTO->getStrStaOperacao(),
                'IdUsuario' => $objUsuarioDTO->getNumIdUsuario(),
                'IdOrgao' => $objUsuarioDTO->getNumIdOrgao(),
                'IdOrigem' => $objUsuarioDTO->getStrIdOrigem(),
                'Sigla' => $objUsuarioDTO->getStrSigla(),
                'Nome' => $objUsuarioDTO->getStrNome(),
                'SinAtivo' => $objUsuarioDTO->getStrSinAtivo());
          }

          try {

            $objReplicacaoServicoDTO->getObjWebService()->replicarUsuario($arr);

          } catch (Exception $e) {
            throw new InfraException('Falha na chamada ao Web Service do sistema ' . $objReplicacaoServicoDTO->getStrSiglaSistema() . '.', $e);
          }
        }
      }
    }catch(Exception $e){
      throw new InfraException('Erro replicando usu�rio.',$e);
    }
  }

  public function replicarUnidade(ReplicacaoUnidadeDTO $objReplicacaoUnidadeDTO) {
    
    $strMsg = '';
    
    try{

      $objSistemaDTO = new SistemaDTO();
      $objSistemaDTO->retNumIdSistema();
      $objSistemaDTO->setNumIdHierarquia($objReplicacaoUnidadeDTO->getNumIdHierarquia());
      
      if ($objReplicacaoUnidadeDTO->isSetNumIdSistema()){
        $objSistemaDTO->setNumIdSistema($objReplicacaoUnidadeDTO->getNumIdSistema());
      }
      
      $arrObjSistemaDTO = $this->listar($objSistemaDTO);
      
      foreach($arrObjSistemaDTO as $objSistemaDTO){

        $objReplicacaoServicoDTO = new ReplicacaoServicoDTO();
        $objReplicacaoServicoDTO->setNumIdSistema($objSistemaDTO->getNumIdSistema());
        $objReplicacaoServicoDTO->setStrNomeOperacao('replicarUnidade');
        $objReplicacaoServicoDTO = Replicacao::getInstance()->obterServico($objReplicacaoServicoDTO);

        $objRelHierarquiaUnidadeRN = new RelHierarquiaUnidadeRN();

        if ($objReplicacaoServicoDTO != null){

          $objRelHierarquiaUnidadeDTO = new RelHierarquiaUnidadeDTO();
          $objRelHierarquiaUnidadeDTO->setBolExclusaoLogica(false);
          $objRelHierarquiaUnidadeDTO->retNumIdUnidade();
          $objRelHierarquiaUnidadeDTO->retStrIdOrigemUnidade();
          $objRelHierarquiaUnidadeDTO->retNumIdOrgaoUnidade();
          $objRelHierarquiaUnidadeDTO->retStrSiglaUnidade();
          $objRelHierarquiaUnidadeDTO->retStrDescricaoUnidade();
          $objRelHierarquiaUnidadeDTO->retStrSinAtivo();

          $objRelHierarquiaUnidadeDTO->setNumIdHierarquia($objReplicacaoUnidadeDTO->getNumIdHierarquia());

          if (is_array($objReplicacaoUnidadeDTO->getNumIdUnidade())){
            $arrIdUnidade = $objReplicacaoUnidadeDTO->getNumIdUnidade();
          }else{
            $arrIdUnidade = array($objReplicacaoUnidadeDTO->getNumIdUnidade());
          }

          $objRelHierarquiaUnidadeDTO->setNumIdUnidade($arrIdUnidade, InfraDTO::$OPER_IN);

          $arrObjRelHierarquiaUnidadeDTO = $objRelHierarquiaUnidadeRN->listar($objRelHierarquiaUnidadeDTO);

          if (count($arrObjRelHierarquiaUnidadeDTO)) {

            $arr = array();
            foreach ($arrObjRelHierarquiaUnidadeDTO as $objRelHierarquiaUnidadeDTO) {
              $arr[] = array(
                  'StaOperacao' => $objReplicacaoUnidadeDTO->getStrStaOperacao(),
                  'IdUnidade' => $objRelHierarquiaUnidadeDTO->getNumIdUnidade(),
                  'IdOrigem' => $objRelHierarquiaUnidadeDTO->getStrIdOrigemUnidade(),
                  'IdOrgao' => $objRelHierarquiaUnidadeDTO->getNumIdOrgaoUnidade(),
                  'Sigla' => $objRelHierarquiaUnidadeDTO->getStrSiglaUnidade(),
                  'Descricao' => $objRelHierarquiaUnidadeDTO->getStrDescricaoUnidade(),
                  'SinAtivo' => $objRelHierarquiaUnidadeDTO->getStrSinAtivo());
            }

            try {

              $objReplicacaoServicoDTO->getObjWebService()->replicarUnidade($arr);

            } catch (Exception $e) {
              throw new InfraException('Falha na chamada ao Web Service do sistema ' . $objReplicacaoServicoDTO->getStrSiglaSistema() . '.', $e);
            }
          }
        }
      }

    }catch(Exception $e){
      throw new InfraException('Erro replicando unidade.',$e);
    }
  }
  
  public function replicarOrgao(ReplicacaoOrgaoDTO $objReplicacaoOrgaoDTO) {
    
    $strMsg = '';
    
    try{

      $objSistemaDTO = new SistemaDTO();
      $objSistemaDTO->retNumIdSistema();
      
      if ($objReplicacaoOrgaoDTO->isSetNumIdSistema()){
        $objSistemaDTO->setNumIdSistema($objReplicacaoOrgaoDTO->getNumIdSistema());
      }
      
      $arrObjSistemaDTO = $this->listar($objSistemaDTO);
      
      foreach($arrObjSistemaDTO as $objSistemaDTO){

        $objReplicacaoServicoDTO = new ReplicacaoServicoDTO();
        $objReplicacaoServicoDTO->setNumIdSistema($objSistemaDTO->getNumIdSistema());
        $objReplicacaoServicoDTO->setStrNomeOperacao('replicarOrgao');
        $objReplicacaoServicoDTO = Replicacao::getInstance()->obterServico($objReplicacaoServicoDTO);
        
        if ($objReplicacaoServicoDTO != null){
      
          $objOrgaoDTO = new OrgaoDTO();
          $objOrgaoDTO->setBolExclusaoLogica(false);
          $objOrgaoDTO->retNumIdOrgao();
          $objOrgaoDTO->retStrSigla();
          $objOrgaoDTO->retStrDescricao();
          $objOrgaoDTO->retStrSinAtivo();


          if (is_array($objReplicacaoOrgaoDTO->getNumIdOrgao())){
            $arrIdOrgao = $objReplicacaoOrgaoDTO->getNumIdOrgao();
          }else{
            $arrIdOrgao = array($objReplicacaoOrgaoDTO->getNumIdOrgao());
          }

          $objOrgaoDTO->setNumIdOrgao($arrIdOrgao, InfraDTO::$OPER_IN);

          $objOrgaoRN = new OrgaoRN();
          $arrObjOrgaoDTO = $objOrgaoRN->listar($objOrgaoDTO);

          if (count($arrObjOrgaoDTO)) {

            $arr = array();
            foreach ($arrObjOrgaoDTO as $objOrgaoDTO) {
              $arr[] = array(
                  'StaOperacao' => $objReplicacaoOrgaoDTO->getStrStaOperacao(),
                  'IdOrgao' => $objOrgaoDTO->getNumIdOrgao(),
                  'Sigla' => $objOrgaoDTO->getStrSigla(),
                  'Descricao' => $objOrgaoDTO->getStrDescricao(),
                  'SinAtivo' => $objOrgaoDTO->getStrSinAtivo());
            }

            try {

              $objReplicacaoServicoDTO->getObjWebService()->replicarOrgao($arr);

            } catch (Exception $e) {
              throw new InfraException('Falha na chamada ao Web Service do sistema ' . $objReplicacaoServicoDTO->getStrSiglaSistema() . '.', $e);
            }
          }
        }
      }

    }catch(Exception $e){
      throw new InfraException('Erro replicando �rg�o.',$e);
    }
  }

  public function replicarContexto(ReplicacaoContextoDTO $objReplicacaoContextoDTO) {
    
    $strMsg = '';
    
    try{

      $objSistemaDTO = new SistemaDTO();
      $objSistemaDTO->retNumIdSistema();
      
      if ($objReplicacaoContextoDTO->isSetNumIdSistema()){
        $objSistemaDTO->setNumIdSistema($objReplicacaoContextoDTO->getNumIdSistema());
      }
      
      $arrObjSistemaDTO = $this->listar($objSistemaDTO);
      
      foreach($arrObjSistemaDTO as $objSistemaDTO){
      
        $objReplicacaoServicoDTO = new ReplicacaoServicoDTO();
        $objReplicacaoServicoDTO->setNumIdSistema($objSistemaDTO->getNumIdSistema());
        $objReplicacaoServicoDTO->setStrNomeOperacao('replicarContexto');
        $objReplicacaoServicoDTO = Replicacao::getInstance()->obterServico($objReplicacaoServicoDTO);
        
        if ($objReplicacaoServicoDTO != null){
      
          $objContextoDTO = new ContextoDTO();
          $objContextoDTO->setBolExclusaoLogica(false);
          $objContextoDTO->retNumIdContexto();
          $objContextoDTO->retNumIdOrgao();
          $objContextoDTO->retStrNome();
          $objContextoDTO->retStrDescricao();
          $objContextoDTO->retStrBaseDnLdap();
          $objContextoDTO->retStrSinAtivo();
          
          $objContextoDTO->setNumIdContexto($objReplicacaoContextoDTO->getNumIdContexto());
          
          $objContextoRN = new ContextoRN();
          $objContextoDTO = $objContextoRN->consultar($objContextoDTO);
      
          try{
            
            $objReplicacaoServicoDTO->getObjWebService()->replicarContexto($objReplicacaoContextoDTO->getStrStaOperacao(),
                                                                           $objContextoDTO->getNumIdContexto(),
                                                                           $objContextoDTO->getNumIdOrgao(),
                                                                           $objContextoDTO->getStrNome(),
                                                                           $objContextoDTO->getStrDescricao(),
                                                                           $objContextoDTO->getStrBaseDnLdap(),
                                                                           $objContextoDTO->getStrSinAtivo());
            
            $strMsg .= 'Contexto replicado no sistema '.$objReplicacaoServicoDTO->getStrSiglaSistema().'.'."\n\n";
                        
          } catch(Exception $e){
            throw new InfraException($strMsg.'Falha na chamada ao Web Service do sistema '.$objReplicacaoServicoDTO->getStrSiglaSistema().'.',$e);
          }
        }
      }

    }catch(Exception $e){
      throw new InfraException('Erro replicando contexto.',$e);
    }
  }

  public function replicarAssociacaoUsuarioUnidade(ReplicacaoAssociacaoUsuarioUnidadeDTO $objReplicacaoAssociacaoUsuarioUnidadeDTO) {
    
    try{

      $objReplicacaoServicoDTO = new ReplicacaoServicoDTO();
      $objReplicacaoServicoDTO->setNumIdSistema($objReplicacaoAssociacaoUsuarioUnidadeDTO->getNumIdSistema());
      $objReplicacaoServicoDTO->setStrNomeOperacao('replicarAssociacaoUsuarioUnidade');
      $objReplicacaoServicoDTO = Replicacao::getInstance()->obterServico($objReplicacaoServicoDTO);
      
      if ($objReplicacaoServicoDTO != null){
        try{
          $objReplicacaoServicoDTO->getObjWebService()->replicarAssociacaoUsuarioUnidade($objReplicacaoAssociacaoUsuarioUnidadeDTO->getStrStaOperacao(),
                                                                                         $objReplicacaoAssociacaoUsuarioUnidadeDTO->getNumIdUsuario(), 
                                                                                         $objReplicacaoAssociacaoUsuarioUnidadeDTO->getNumIdUnidade());
        }catch(Exception $e){
          throw new InfraException('Erro replicando associa��o entre usu�rio e unidade para o sistema '.$objReplicacaoServicoDTO->getStrSiglaSistema().'.',$e);
        }
      }

    }catch(Exception $e){
      throw new InfraException('Erro replicando associa��o entre usu�rio e unidade.',$e);
    }
  }

  public function replicarPermissao(ReplicacaoPermissaoDTO $objReplicacaoPermissaoDTO) {

    try{

      $objReplicacaoServicoDTO = new ReplicacaoServicoDTO();
      $objReplicacaoServicoDTO->setNumIdSistema($objReplicacaoPermissaoDTO->getNumIdSistema());
      $objReplicacaoServicoDTO->setStrNomeOperacao('replicarPermissao');
      $objReplicacaoServicoDTO = Replicacao::getInstance()->obterServico($objReplicacaoServicoDTO);

      if ($objReplicacaoServicoDTO != null){

        $objPermissaoDTO = new PermissaoDTO();
        $objPermissaoDTO->retNumIdSistema();
        $objPermissaoDTO->retNumIdUsuario();
        $objPermissaoDTO->retNumIdUnidade();
        $objPermissaoDTO->retNumIdPerfil();
        $objPermissaoDTO->retDtaDataInicio();
        $objPermissaoDTO->retDtaDataFim();
        $objPermissaoDTO->retStrSinSubunidades();

        $objPermissaoDTO->setNumIdSistema($objReplicacaoPermissaoDTO->getNumIdSistema());
        $objPermissaoDTO->setNumIdUsuario($objReplicacaoPermissaoDTO->getNumIdUsuario());
        $objPermissaoDTO->setNumIdUnidade($objReplicacaoPermissaoDTO->getNumIdUnidade());
        $objPermissaoDTO->setNumIdPerfil($objReplicacaoPermissaoDTO->getNumIdPerfil());

        $objPermissaoRN = new PermissaoRN();
        $objPermissaoDTO = $objPermissaoRN->consultar($objPermissaoDTO);

        $arrUnidadesReplicacao = array($objReplicacaoPermissaoDTO->getNumIdUnidade());

        if ($objPermissaoDTO->getStrSinSubunidades()=='S'){
          $objSistemaDTO = new SistemaDTO();
          $objSistemaDTO->setNumIdSistema($objReplicacaoPermissaoDTO->getNumIdSistema());
          $objSistemaDTO->setNumIdUnidade($objPermissaoDTO->getNumIdUnidade());
          $arrHierarquia = Replicacao::getInstance()->obterHierarquia($objSistemaDTO);
          $arrUnidadesReplicacao = array_merge($arrUnidadesReplicacao, InfraArray::converterArrInfraDTO($arrHierarquia[$objPermissaoDTO->getNumIdUnidade()]->getArrUnidadesInferiores(),'IdUnidade'));
        }

        try{
          foreach($arrUnidadesReplicacao as $numIdUnidadeReplicacao){
            $objReplicacaoServicoDTO->getObjWebService()->replicarPermissao($objReplicacaoPermissaoDTO->getStrStaOperacao(),
                                                                            $objPermissaoDTO->getNumIdSistema(),
                                                                            $objPermissaoDTO->getNumIdUsuario(),
                                                                            $numIdUnidadeReplicacao,
                                                                            $objPermissaoDTO->getNumIdPerfil(),
                                                                            $objPermissaoDTO->getDtaDataInicio(),
                                                                            $objPermissaoDTO->getDtaDataFim());
          }

        }catch(Exception $e){
          throw new InfraException('Falha na chamada ao Web Service do sistema '.$objReplicacaoServicoDTO->getStrSiglaSistema().'.',$e);
        }
      }

    }catch(Exception $e){
      throw new InfraException('Erro replicando permiss�o.',$e);
    }
  }
}
?>