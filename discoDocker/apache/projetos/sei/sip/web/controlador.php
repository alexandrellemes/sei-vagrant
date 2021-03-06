<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
 * 
 * 16/06/2006 - criado por MGA
 *
 */
 
  
  try {
    
    require_once dirname(__FILE__).'/Sip.php';
		
		session_start();
		
		//////////////////////////////////////////////////////////////////////////////
		//InfraDebug::getInstance()->setBolLigado(false);
		//InfraDebug::getInstance()->setBolDebugInfra(true);
		//InfraDebug::getInstance()->limpar();
		//////////////////////////////////////////////////////////////////////////////
		
		SessaoSip::getInstance()->validarLink();
		
	  switch($_GET['acao']) {
			
      //INDEX //////////////////////////////////////////////////////////////
	  	case 'principal':
		  	require_once 'index.php';
		  	break;
		  	
      case 'sair':
	      SessaoSip::getInstance()->sair();
		  	break;  	
			
      //PERMISS?O //////////////////////////////////////////////////////////////
	  	case 'permissao_cadastrar':
	  	case 'permissao_alterar':
	  	case 'permissao_consultar':
			  require_once 'permissao_cadastro.php';
			  break;
	  	
	    case 'permissao_listar_administradas': 
	    case 'permissao_excluir': 
			  require_once 'permissao_lista_administradas.php';
			  break;

	    case 'permissao_listar_pessoais': 
			  require_once 'permissao_lista_pessoais.php';
			  break;
			  
	    case 'permissao_copiar': 
			  require_once 'permissao_copiar.php';
			  break;
			  
			  
      //?RG?O //////////////////////////////////////////////////////////////
	  	case 'orgao_cadastrar':
			case 'orgao_alterar':
			case 'orgao_consultar':	
			  require_once 'orgao_cadastro.php';
			  break;
	  	
	    case 'orgao_listar': 
	    case 'orgao_excluir':	
	    case 'orgao_desativar':	
	    case 'orgao_reativar':	
			  require_once 'orgao_lista.php';
			  break;
				
      //CONTEXTO //////////////////////////////////////////////////////////////
	  	case 'contexto_cadastrar':
	  	case 'contexto_alterar':
	  	case 'contexto_consultar':
			  require_once 'contexto_cadastro.php';
			  break;
	  	
	    case 'contexto_listar': 
	    case 'contexto_desativar':
	    case 'contexto_reativar':    
			  require_once 'contexto_lista.php';
			  break;
				
      //SISTEMA //////////////////////////////////////////////////////////////
	  	case 'sistema_cadastrar':
	  	case 'sistema_alterar':
	  	case 'sistema_consultar':
	  	case 'sistema_upload':  
			  require_once 'sistema_cadastro.php';
			  break;
	  	
	  	case 'sistema_clonar':  
			  require_once 'sistema_clonar.php';
			  break;

	  	case 'sistema_importar':  
			  require_once 'sistema_importar.php';
			  break;
			  
	    case 'sistema_listar':
      case 'sistema_desativar':
      case 'sistema_reativar':
      case 'sistema_excluir':
			  require_once 'sistema_lista.php';
			  break;

      //USU?RIO //////////////////////////////////////////////////////////////
	  	case 'usuario_cadastrar':
	  	case 'usuario_alterar':
	  	case 'usuario_consultar':
			  require_once 'usuario_cadastro.php';
			  break;
	  	
	    case 'usuario_listar': 
	    case 'usuario_excluir': 
	    case 'usuario_desativar': 
	    case 'usuario_reativar':
			  require_once 'usuario_lista.php';
			  break;
				
      //UNIDADE //////////////////////////////////////////////////////////////
	  	case 'unidade_cadastrar':
	  	case 'unidade_alterar':
	  	case 'unidade_consultar':
			  require_once 'unidade_cadastro.php';
			  break;
	  	
	    case 'unidade_listar': 
	    case 'unidade_desativar':
	    case 'unidade_reativar':
	    case 'unidade_excluir':
			  require_once 'unidade_lista.php';
			  break;

      //RECURSO //////////////////////////////////////////////////////////////
	  	case 'recurso_cadastrar':
	  	case 'recurso_alterar':
	  	case 'recurso_consultar':
			  require_once 'recurso_cadastro.php';
			  break;
	  	
	    case 'recurso_listar': 
	    case 'recurso_desativar': 
	    case 'recurso_reativar': 
	    case 'recurso_excluir': 
			  require_once 'recurso_lista.php';
			  break;
			  
	    case 'recurso_gerar': 
			  require_once 'recurso_gerar.php';
			  break;
			  
	    case 'recurso_selecionar_auditoria':	
			  require_once 'recurso_selecao.php';
			  break;
			  
      //PERFIL //////////////////////////////////////////////////////////////
	  	case 'perfil_cadastrar':
	  	case 'perfil_alterar':
	  	case 'perfil_consultar':
			  require_once 'perfil_cadastro.php';
			  break;
	  	
	    case 'perfil_listar':
	    case 'perfil_excluir':  
	    case 'perfil_desativar':    
	    case 'perfil_reativar':  
			  require_once 'perfil_lista.php';
			  break;
			  
	    case 'perfil_montar': 
			  require_once 'perfil_montar.php';
			  break;

	    case 'perfil_clonar': 
			  require_once 'perfil_clonar.php';
			  break;

		  case 'perfil_listar_coordenados':
		    require_once 'perfil_lista_coordenados.php';
		    break;

      case 'perfil_comparar':
        require_once 'perfil_comparar.php';
        break;

      //ADMINISTRADOR SISTEMA //////////////////////////////////////////////////////////////
	  	case 'administrador_sistema_cadastrar':
		  	require_once 'administrador_sistema_cadastro.php';
		  	break;
	  	
	    case 'administrador_sistema_listar': 
			  require_once 'administrador_sistema_lista.php';
			  break;
			  
      //COORDENADOR PERFIL //////////////////////////////////////////////////////////////
	  	case 'coordenador_perfil_cadastrar':
		  	require_once 'coordenador_perfil_cadastro.php';
		  	break;
	  	
	    case 'coordenador_perfil_listar': 
			  require_once 'coordenador_perfil_lista.php';
			  break;
			  
	    case 'coordenador_perfil_listar_simples':
	      require_once 'coordenador_perfil_lista_simples.php';
	      break;
	       
	      
      //COORDENADOR UNIDADE //////////////////////////////////////////////////////////////
	  	case 'coordenador_unidade_cadastrar':
		  	require_once 'coordenador_unidade_cadastro.php';
		  	break;
	  	
	    case 'coordenador_unidade_listar': 
			  require_once 'coordenador_unidade_lista.php';
			  break;
				
      //TIPO PERMISS?O //////////////////////////////////////////////////////////////
	  	case 'tipo_permissao_cadastrar':
	  	case 'tipo_permissao_alterar':
	  	case 'tipo_permissao_consultar':
			  require_once 'tipo_permissao_cadastro.php';
			  break;
	  	
	    case 'tipo_permissao_listar': 
			  require_once 'tipo_permissao_lista.php';
			  break;
			  
			  
      //HIERARQUIA //////////////////////////////////////////////////////////////
	  	case 'hierarquia_cadastrar':
	  	case 'hierarquia_alterar':
	  	case 'hierarquia_consultar':
			  require_once 'hierarquia_cadastro.php';
			  break;
	  	
	    case 'hierarquia_listar':
      case 'hierarquia_excluir':
      case 'hierarquia_desativar':
      case 'hierarquia_reativar':
      require_once 'hierarquia_lista.php';
			  break;

	  	
	    case 'hierarquia_clonar': 
			  require_once 'hierarquia_clonar.php';
			  break;
			  
			//ESTRUTURA HIERARQUICA	
	    case 'rel_hierarquia_unidade_cadastrar': 
	    case 'rel_hierarquia_unidade_alterar': 
			  require_once 'rel_hierarquia_unidade_cadastro.php';
			  break;
			  
	    case 'rel_hierarquia_unidade_listar': 
	    case 'rel_hierarquia_unidade_excluir': 
	    case 'rel_hierarquia_unidade_desativar': 
	    case 'rel_hierarquia_unidade_reativar': 
			  require_once 'rel_hierarquia_unidade_lista.php';
			  break;


      //MENU //////////////////////////////////////////////////////////////
	  	case 'menu_cadastrar':
	  	case 'menu_alterar':
	  	case 'menu_consultar':
			  require_once 'menu_cadastro.php';
			  break;
	  	
	    case 'menu_listar': 
			  require_once 'menu_lista.php';
			  break;

			//ITEM MENU	
	    case 'item_menu_cadastrar': 
	    case 'item_menu_alterar': 
			  require_once 'item_menu_cadastro.php';
			  break;
			  
	    case 'item_menu_listar': 
	    case 'item_menu_excluir': 
	    case 'item_menu_desativar': 
			  require_once 'item_menu_lista.php';
			  break;

		  case 'item_menu_listar_perfil':
		    require_once 'item_menu_lista_perfil.php';
		    break;
			    	
      //GRUPO DE REDE //////////////////////////////////////////////////////////////
	  	case 'grupo_rede_cadastrar':
	  	case 'grupo_rede_alterar':
	  	case 'grupo_rede_consultar':
			  require_once 'grupo_rede_cadastro.php';
			  break;
	  	
	    case 'grupo_rede_listar': 
	    case 'grupo_rede_excluir': 
	    case 'grupo_rede_selecionar': 
			  require_once 'grupo_rede_lista.php';
			  break;

      //GRUPO DE REDE //////////////////////////////////////////////////////////////
	  	case 'rel_grupo_rede_unidade_cadastrar':
	  	case 'rel_grupo_rede_unidade_consultar':
			  require_once 'rel_grupo_rede_unidade_cadastro.php';
			  break;
	  	
	    case 'rel_grupo_rede_unidade_listar': 
	    case 'rel_grupo_rede_unidade_excluir': 
			  require_once 'rel_grupo_rede_unidade_lista.php';
			  break;

			//AUDITORIA
			case 'regra_auditoria_cadastrar':
	  	case 'regra_auditoria_alterar':
	  	case 'regra_auditoria_consultar':
			  require_once 'regra_auditoria_cadastro.php';
			  break;
	  	
	    case 'regra_auditoria_listar': 
	    case 'regra_auditoria_excluir':
	    case 'regra_auditoria_desativar': 
	    case 'regra_auditoria_reativar': 
			  require_once 'regra_auditoria_lista.php';
			  break;

		  case 'servidor_autenticacao_cadastrar':
		  case 'servidor_autenticacao_alterar':
		  case 'servidor_autenticacao_consultar':
		    require_once 'servidor_autenticacao_cadastro.php';
		    break;
		  
		  case 'servidor_autenticacao_listar':
		  case 'servidor_autenticacao_excluir':
	    case 'servidor_autenticacao_selecionar':
		    require_once 'servidor_autenticacao_lista.php';
		    break;
			    	
			  
      //MENUS DOS SISTEMAS //////////////////////////////////////////////////////////////
	  	case 'rel_sistema_menu_cadastrar':
		  	require_once 'rel_sistema_menu_cadastro.php';
		  	break;
	  	
	    case 'rel_sistema_menu_listar': 
			  require_once 'rel_sistema_menu_lista.php';
			  break;
				
	    case 'permissao_atribuir_em_bloco':
			  require_once 'permissao_atribuicao_bloco.php';
			  break;
			  

		default:

			foreach($SIP_MODULOS as $objModulo){
				if ($objModulo->processarControlador($_GET['acao'])!=null){
					return;
				}
			}

      if ($_GET['acao']=='infra_auditoria_listar' && ConfiguracaoSip::getInstance()->isSetValor('BancoAuditoriaSip')){
        BancoAuditoria::setObjInfraIBanco(BancoAuditoriaSip::getInstance());
      }

		  if (!InfraControlador::processar($_GET['acao'], PaginaSip::getInstance(), SessaoSip::getInstance(), BancoSip::getInstance(),LogSip::getInstance(), CacheSip::getInstance())){
			  throw new InfraException('A??o \''.$_GET['acao'].'\' n?o reconhecida pelo controlador.');
		  }
		}
		
  }catch(Exception $e){
  	PaginaSip::getInstance()->processarExcecao($e);
  } 
	
?>