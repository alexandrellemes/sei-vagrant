<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
 * 
 * 12/11/2007 - criado por MGA
 *
 */

  try {

    require_once dirname(__FILE__).'/SEI.php';
		
		session_start();
		
		InfraDebug::getInstance()->setBolLigado(false);
		InfraDebug::getInstance()->setBolDebugInfra(false);
		InfraDebug::getInstance()->limpar();

		SeiINT::validarHttps();
		
		SessaoSEI::getInstance();

		$objUsuarioDTO = new UsuarioDTO();
		$objUsuarioDTO->setBolExclusaoLogica(false);
		$objUsuarioDTO->retStrSinAtivo();
		$objUsuarioDTO->retStrSinAcessibilidade();
		$objUsuarioDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());

		$objUsuarioRN = new UsuarioRN();
		$objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);

		if ($objUsuarioDTO == null) {
      SessaoSEI::getInstance()->sair(null, 'Usu?rio n?o encontrado no sistema.');
		}

		if ($objUsuarioDTO->getStrSinAtivo() == 'N') {
		  SessaoSEI::getInstance()->sair(null, 'Usu?rio desativado no sistema.');
		}

		SessaoSEI::getInstance()->setAtributo('acessibilidade',$objUsuarioDTO->getStrSinAcessibilidade());

		try{
		  if (!ConfiguracaoSEI::getInstance()->isSetValor('Usuario','Robo') || !in_array(SessaoSEI::getInstance()->getStrSiglaUsuario(),ConfiguracaoSEI::getInstance()->getValor('Usuario','Robo'))){
		    NavegadorSEI::getInstance()->registrar();
		  }
		}catch(Exception $e){
		  LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
		}

		if (isset($_GET['infra_url'])) {

			$strInfraUrl = base64_decode($_GET['infra_url']);

			if (SessaoSEI::getInstance()->tratarLinkSemAssinatura($strInfraUrl)){
				header('Location: ' . SessaoSEI::getInstance()->assinarLink($strInfraUrl));
				die;
			}
		}

		//chegando do login
		if (isset($_GET['infra_sip']) && SessaoSEI::getInstance()->verificarPermissao('procedimento_controlar')) {
			$strLinkInicializacao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem=principal&acao_retorno=principal&inicializando=1');
		} else {
			$strLinkInicializacao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=principal');
		}

	  header('Location: '.$strLinkInicializacao);
		die;

  }catch(Exception $e){
  	PaginaSEI::getInstance()->processarExcecao($e);
  }
?>