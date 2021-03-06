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

	SessaoSEI::getInstance()->validarLink();

	infraTratarErroFatal(SessaoSEI::getInstance());


	//if (!(SessaoSEI::getInstance()->getStrSiglaOrgaoUsuario()=='XXXX' && SessaoSEI::getInstance()->getStrSiglaUsuario()=='xxxx')){
	//	SessaoSEI::getInstance()->sair(null,'Sistema em manuten??o.');
	//}


	switch($_GET['acao']) {

		//INDEX //////////////////////////////////////////////////////////////
		case 'principal':
			require_once 'index.php';
			break;

		case 'sistema_configurar':
			require_once dirname(__FILE__) . '/Configuracao.php';
			break;
		
		case 'procedimento_controlar':
			require_once 'procedimento_controlar.php';
			break;

		case 'procedimento_trabalhar':
			require_once 'procedimento_trabalhar.php';
			break;

    case 'procedimento_pesquisar':
      require_once 'procedimento_pesquisar.php';
      break;

		case 'procedimento_visualizar':
		case 'procedimento_paginar':
			require_once 'arvore_montar.php';
			break;

		case 'arvore_ordenar':
			require_once 'arvore_ordenar.php';
			break;

		case 'arvore_visualizar':
		case 'procedimento_reabrir':
		case 'procedimento_excluir':
		case 'documento_excluir':
    case 'procedimento_ciencia':
    case 'procedimento_anexado_ciencia':
    case 'documento_ciencia':
    case 'procedimento_credencial_renunciar';

    require_once 'arvore_visualizar.php';
			break;

		case 'procedimento_enviar_email':
		case 'documento_enviar_email':
		case 'responder_formulario':
		case 'email_upload_anexo':
		case 'email_encaminhar':
    case 'documento_email_circular':
			require_once 'email_processar.php';
			break;

		case 'procedimento_escolher_tipo':
		case 'procedimento_escolher_tipo_relacionado':
			require_once 'procedimento_escolher_tipo.php';
			break;

		case 'procedimento_upload_anexo':
		case 'procedimento_gerar':
		case 'procedimento_gerar_relacionado':
		case 'procedimento_alterar':
		case 'procedimento_consultar':
			require_once 'procedimento_cadastro.php';
			break;

		case 'procedimento_duplicar':
			require_once 'procedimento_duplicar.php';
			break;

		case 'procedimento_consultar_historico':
			require_once 'procedimento_historico.php';
			break;

		case 'procedimento_concluir':
			if ($_GET['acao_origem']=='procedimento_controlar'){
				require_once 'procedimento_controlar.php';
			}else if ($_GET['acao_origem']=='arvore_visualizar'){
				require_once 'arvore_visualizar.php';
			}
			break;


		case 'procedimento_relacionar':
		case 'procedimento_excluir_relacionamento':
			require_once 'procedimento_relacionados.php';
			break;

		case 'procedimento_sobrestado_listar':
			require_once 'procedimento_sobrestado_lista.php';
			break;

		case 'procedimento_remover_sobrestamento':
			if ($_GET['acao_origem']=='procedimento_sobrestado_listar'){
				require_once 'procedimento_sobrestado_lista.php';
			}else{
				require_once 'arvore_visualizar.php';
			}
			break;

		case 'procedimento_sobrestar':
			require_once 'procedimento_sobrestar.php';
			break;

		case 'procedimento_anexar':
		  require_once 'procedimento_anexacao.php';
		  break;

	  case 'procedimento_desanexar':
	    require_once 'procedimento_desanexacao.php';
	    break;

		//EDITOR
		case 'editor_montar':
		case 'editor_imagem_upload':
		//case 'editor_salvar': enviada diretamente para a p?gina editor_processar.php para tratatamento de troca de unidade com documento aberto
			require_once 'editor/editor_processar.php';
			break;

    //EDITOR
    case 'formulario_gerar':
    case 'formulario_alterar':
    case 'formulario_consultar':
    case 'tipo_formulario_visualizar':
      require_once 'formulario_processar.php';
      break;

		case 'tarja_assinatura_consultar':
		case 'tarja_assinatura_upload':
		case 'tarja_assinatura_alterar':
		  require_once 'tarja_assinatura_cadastro.php';
		  break;

		case 'tarja_assinatura_listar':
		  require_once 'tarja_assinatura_lista.php';
		  break;

	  case 'tarja_assinatura_montar':
	    require_once 'tarja_assinatura_montagem.php';
	    break;

			//PROTOCOLO
		case 'protocolo_pesquisar':
		case 'protocolo_pesquisa_rapida':
			require_once 'protocolo_pesquisa.php';
			break;

		case 'protocolo_ciencia_listar':
			require_once 'ciencia_andamento.php';
			break;

		case 'documento_imprimir_web':
			require_once 'documento_imprimir.php';
			break;

		case 'documento_mover':
		  require_once 'documento_mover.php';
		  break;

    case 'arquivamento_listar':
		case 'arquivamento_arquivar':
		case 'arquivamento_receber':
		case 'arquivamento_cancelar_recebimento':
		case 'arquivamento_pesquisar':
			require_once 'arquivamento_lista.php';
			break;

		case 'arquivamento_solicitar_desarquivamento':
			require_once 'solicitar_desarquivamento.php';
			break;


    case 'arquivamento_desarquivar':
    case 'arquivamento_desarquivamento_listar':
		case 'arquivamento_cancelar_solicitacao_desarquivamento':
			require_once 'desarquivamento_lista.php';
			break;

		case 'documento_cancelar':
			require_once 'documento_cancelar.php';
			break;

		case 'procedimento_atualizar_andamento':
			require_once 'procedimento_atualizar_andamento.php';
			break;

		case 'procedimento_enviar':
			require_once 'procedimento_enviar.php';
			break;

		case 'usuario_validar_acesso':
			require_once 'identificacao_acesso.php';
			break;

		case 'procedimento_atribuicao_cadastrar':
			require_once 'procedimento_atribuicao_cadastro.php';
			break;

		case 'procedimento_atribuicao_listar':
		case 'procedimento_atribuicao_alterar':
			require_once 'procedimento_atribuicao_lista.php';
			break;

		case 'tarefa_listar':
		case 'tarefa_configurar_historico':
		  require_once 'historico_configuracao.php';
		  break;

			//DOCUMENTO
		case 'documento_escolher_tipo':
			require_once 'documento_escolher_tipo.php';
			break;


		case 'documento_gerar':
		case 'documento_receber':
		case 'documento_alterar':
		case 'documento_alterar_recebido':
		case 'documento_consultar':
		case 'documento_consultar_recebido':
		case 'documento_upload_anexo':
		case 'publicacao_gerar_relacionada':
			require_once 'documento_cadastro.php';
			break;

		case 'documento_assinar':
			require_once 'documento_assinar.php';
			break;

		case 'documento_visualizar':
		case 'documento_visualizar_conteudo_assinatura':
		case 'base_conhecimento_visualizar':
			require_once 'documento_visualizar.php';
			break;

    case 'documento_gerar_multiplo':
      require_once 'documento_geracao_multiplo.php';
      break;

    case 'documento_gerar_circular':
      require_once 'documento_geracao_circular.php';
      break;

		case 'protocolo_visualizar':
		  require_once 'protocolo_visualizar.php';
		  break;

		case 'procedimento_download_anexo':
		case 'documento_download_anexo':
		case 'base_conhecimento_download_anexo':
    case 'projeto_download_anexo':
    case 'anexo_download':
			require_once 'anexo_download.php';
			break;

		case 'procedimento_credencial_gerenciar';
    case 'procedimento_credencial_conceder';
		case 'procedimento_credencial_cassar';
	  	require_once 'procedimento_credencial_gerenciar.php';
	  	break;

		case 'procedimento_credencial_listar';
			require_once 'procedimento_credencial_lista.php';
			break;
    
		case 'procedimento_credencial_transferir';
			require_once 'procedimento_credencial_transferir.php';
			break;

		case 'credencial_assinatura_gerenciar';
    case 'credencial_assinatura_conceder';
		case 'credencial_assinatura_cassar';
	  	require_once 'credencial_assinatura_gerenciar.php';
	  	break;

		case 'assinatura_externa_gerenciar';
    case 'assinatura_externa_liberar';
	  	require_once 'assinatura_externa_gerenciar.php';
	  	break;

	  case 'acesso_externo_cancelar':
		case 'assinatura_externa_cancelar';
	  	require_once 'acesso_externo_cancelar.php';
	  	break;

		case 'acesso_externo_protocolo_selecionar':
		case 'acesso_externo_protocolo_detalhe':
			require_once 'acesso_externo_protocolos.php';
			break;

		case 'procedimento_gerar_pdf':
			require_once 'procedimento_pdf.php';
			break;

    case 'procedimento_gerar_zip':
      require_once 'procedimento_zip.php';
      break;

			//BLOCO
		case 'bloco_excluir':
		case 'bloco_concluir':
		case 'bloco_desativar':
		case 'bloco_reativar':
		case 'bloco_disponibilizar':
		case 'bloco_cancelar_disponibilizacao':
		case 'bloco_reuniao_listar':
		case 'bloco_reuniao_listar_disponibilizados':
		case 'bloco_assinatura_listar':
		case 'bloco_assinatura_listar_disponibilizados':
		case 'bloco_interno_listar':
		case 'bloco_interno_listar_disponibilizados':
		case 'bloco_selecionar_processo':
		case 'bloco_selecionar_documento':
		case 'bloco_retornar':
		case 'bloco_reabrir':
			require_once 'bloco_lista.php';
			break;

		case 'bloco_assinatura_cadastrar':
		case 'bloco_assinatura_alterar':
		case 'bloco_interno_cadastrar':
		case 'bloco_interno_alterar':
		case 'bloco_reuniao_cadastrar':
		case 'bloco_reuniao_alterar':
		case 'bloco_alterar':
		case 'bloco_consultar':
			require_once 'bloco_cadastro.php';
			break;

		case 'bloco_escolher':
			require_once 'bloco_escolher.php';
			break;

			//REL BLOCO PROTOCOLO
		case 'rel_bloco_protocolo_excluir':
		case 'rel_bloco_protocolo_desativar':
		case 'rel_bloco_protocolo_reativar':
		case 'rel_bloco_protocolo_listar':
		case 'rel_bloco_protocolo_selecionar':
			require_once 'rel_bloco_protocolo_lista.php';
			break;

		case 'rel_bloco_protocolo_cadastrar':
		case 'rel_bloco_protocolo_alterar':
		case 'rel_bloco_protocolo_consultar':
			require_once 'rel_bloco_protocolo_cadastro.php';
			break;

    case 'bloco_navegar':
      require_once 'bloco_navegar.php';
      break;

    //SITUACAO DE PROCESSO
    case 'situacao_excluir':
    case 'situacao_desativar':
    case 'situacao_reativar':
    case 'situacao_listar':
    case 'situacao_selecionar':
      require_once 'situacao_lista.php';
      break;

    case 'situacao_cadastrar':
    case 'situacao_alterar':
    case 'situacao_consultar':
      require_once 'situacao_cadastro.php';
      break;

    case 'andamento_situacao_gerenciar':
      require_once 'situacao_gerenciar.php';
      break;

    case 'andamento_marcador_gerenciar':
      require_once 'marcador_gerenciar.php';
      break;

			//TIPO PROCEDIMENTO
		case 'tipo_procedimento_excluir':
		case 'tipo_procedimento_desativar':
		case 'tipo_procedimento_reativar':
		case 'tipo_procedimento_listar':
		case 'tipo_procedimento_selecionar':
			require_once 'tipo_procedimento_lista.php';
			break;

		case 'tipo_procedimento_cadastrar':
		case 'tipo_procedimento_alterar':
		case 'tipo_procedimento_consultar':
			require_once 'tipo_procedimento_cadastro.php';
			break;

			//NOVIDADES
		case 'novidade_cadastrar':
		case 'novidade_alterar':
		case 'novidade_consultar':
			require_once 'novidade_cadastro.php';
			break;

		case 'novidade_excluir':
		case 'novidade_listar':
		case 'novidade_liberar':
		case 'novidade_cancelar_liberacao':
			require_once 'novidade_lista.php';
			break;

		case 'novidade_mostrar':
			require_once 'novidade_mostrar.php';
			break;

    //Lembretes
    case 'lembrete_listar':
    case 'lembrete_excluir':
    case 'lembrete_desativar':
    case 'lembrete_selecionar':
    case 'lembrete_reativar':
      require_once 'lembrete_lista.php';
      break;

    case 'lembrete_visualizar':
      require_once 'lembrete_visualiza.php';
      break;

			//CARGO
		case 'cargo_excluir':
		case 'cargo_desativar':
		case 'cargo_reativar':
		case 'cargo_listar':
		case 'cargo_selecionar':
			require_once 'cargo_lista.php';
			break;

		case 'cargo_cadastrar':
		case 'cargo_alterar':
		case 'cargo_consultar':
			require_once 'cargo_cadastro.php';
			break;

			//VOCATIVO
		case 'vocativo_excluir':
		case 'vocativo_desativar':
		case 'vocativo_reativar':
		case 'vocativo_listar':
		case 'vocativo_selecionar':
			require_once 'vocativo_lista.php';
			break;

		case 'vocativo_cadastrar':
		case 'vocativo_alterar':
		case 'vocativo_consultar':
			require_once 'vocativo_cadastro.php';
			break;

			//TRATAMENTO
		case 'tratamento_excluir':
		case 'tratamento_desativar':
		case 'tratamento_reativar':
		case 'tratamento_listar':
		case 'tratamento_selecionar':
			require_once 'tratamento_lista.php';
			break;

		case 'tratamento_cadastrar':
		case 'tratamento_alterar':
		case 'tratamento_consultar':
			require_once 'tratamento_cadastro.php';
			break;

			//TIPO CONTEXTO CONTATO
		case 'tipo_contato_excluir':
		case 'tipo_contato_desativar':
		case 'tipo_contato_reativar':
		case 'tipo_contato_listar':
		case 'tipo_contato_selecionar':
			require_once 'tipo_contato_lista.php';
			break;

		case 'tipo_contato_cadastrar':
		case 'tipo_contato_alterar':
		case 'tipo_contato_consultar':
			require_once 'tipo_contato_cadastro.php';
			break;

			//UF
		case 'uf_excluir':
		case 'uf_desativar':
		case 'uf_reativar':
		case 'uf_listar':
		case 'uf_selecionar':
			require_once 'uf_lista.php';
			break;

		case 'uf_cadastrar':
		case 'uf_alterar':
		case 'uf_consultar':
			require_once 'uf_cadastro.php';
			break;

		//Pa?s
		case 'pais_excluir':
		case 'pais_desativar':
		case 'pais_reativar':
		case 'pais_listar':
		case 'pais_selecionar':
			require_once 'pais_lista.php';
			break;

		case 'pais_cadastrar':
		case 'pais_alterar':
		case 'pais_consultar':
			require_once 'pais_cadastro.php';
			break;

			//CIDADE
		case 'cidade_excluir':
		case 'cidade_desativar':
		case 'cidade_reativar':
		case 'cidade_listar':
		case 'cidade_selecionar':
			require_once 'cidade_lista.php';
			break;

		case 'cidade_cadastrar':
		case 'cidade_alterar':
		case 'cidade_consultar':
			require_once 'cidade_cadastro.php';
			break;

			//CONTATO
		case 'contato_excluir':
		case 'contato_desativar':
		case 'contato_reativar':
		case 'contato_listar':
		case 'contato_selecionar':
			require_once 'contato_lista.php';
			break;

		case 'contato_cadastrar':
		case 'contato_alterar':
		case 'contato_consultar':
		case 'contato_alterar_temporario':
			require_once 'contato_cadastro.php';
			break;

		case 'contato_imprimir_etiquetas':
    case 'contato_pdf_etiquetas':
			require_once 'contato_etiquetas.php';
			break;

		case 'contato_relatorio_temporarios':
		case 'contato_desativar_temporario':
		case 'contato_excluir_temporario':
		case 'contato_substituir_temporario':
			require_once 'contato_relatorio_temporarios.php';
			break;

		case 'procedimento_relatorio_sigilosos':
			require_once 'procedimento_relatorio_sigilosos.php';
			break;

		case 'procedimento_acervo_sigilosos':
		case 'procedimento_credencial_cancelar':
			require_once 'procedimento_acervo_sigilosos.php';
			break;

		case 'procedimento_credencial_ativar':
			require_once 'procedimento_credencial_ativar.php';
			break;

			//UNIDADE
		case 'unidade_selecionar_todas':
		case 'unidade_selecionar_outras':
		case 'unidade_selecionar_envio_processo':
		case 'unidade_selecionar_orgao':
		require_once 'unidade_selecao.php';
			break;

		case 'unidade_selecionar_reabertura_processo':
		  require_once 'unidade_reabertura.php';
		  break;

		case 'unidade_excluir':
		case 'unidade_desativar':
		case 'unidade_reativar':
		case 'unidade_listar':
		case 'unidade_cache':
			require_once 'unidade_lista.php';
			break;

		case 'unidade_alterar':
		case 'unidade_consultar':
			require_once 'unidade_cadastro.php';
			break;

		case 'unidade_migrar':
			require_once 'unidade_migracao.php';
			break;

		case 'usuario_excluir':
		case 'usuario_desativar':
		case 'usuario_reativar':
		case 'usuario_listar':
		case 'usuario_selecionar':
			require_once 'usuario_lista.php';
			break;

		case 'usuario_alterar':
		case 'usuario_consultar':
			require_once 'usuario_cadastro.php';
			break;

		case 'usuario_externo_excluir':
		case 'usuario_externo_desativar':
		case 'usuario_externo_reativar':
		case 'usuario_externo_listar':
		case 'usuario_externo_selecionar':
			require_once 'usuario_externo_lista.php';
			break;

		case 'usuario_externo_alterar':
		case 'usuario_externo_consultar':
			require_once 'usuario_externo_cadastro.php';
			break;

		case 'tipo_formulario_excluir':
		case 'tipo_formulario_desativar':
		case 'tipo_formulario_reativar':
		case 'tipo_formulario_listar':
		case 'tipo_formulario_selecionar':
			require_once 'tipo_formulario_lista.php';
			break;

		case 'tipo_formulario_cadastrar':
		case 'tipo_formulario_alterar':
		case 'tipo_formulario_consultar':
			require_once 'tipo_formulario_cadastro.php';
			break;

		case 'tipo_formulario_clonar':
			require_once 'tipo_formulario_clonar.php';
			break;

		case 'atributo_excluir':
		case 'atributo_desativar':
		case 'atributo_reativar':
		case 'atributo_listar':
		case 'atributo_selecionar':
			require_once 'atributo_lista.php';
			break;

		case 'atributo_cadastrar':
		case 'atributo_alterar':
		case 'atributo_consultar':
			require_once 'atributo_cadastro.php';
			break;

			//TIPO ARQUIVO
		case 'tipo_localizador_excluir':
		case 'tipo_localizador_desativar':
		case 'tipo_localizador_reativar':
		case 'tipo_localizador_listar':
		case 'tipo_localizador_selecionar':
			require_once 'tipo_localizador_lista.php';
			break;

		case 'tipo_localizador_cadastrar':
		case 'tipo_localizador_alterar':
		case 'tipo_localizador_consultar':
			require_once 'tipo_localizador_cadastro.php';
			break;


			//TIPO SUPORTE
		case 'tipo_suporte_excluir':
		case 'tipo_suporte_desativar':
		case 'tipo_suporte_reativar':
		case 'tipo_suporte_listar':
		case 'tipo_suporte_selecionar':
			require_once 'tipo_suporte_lista.php';
			break;

		case 'tipo_suporte_cadastrar':
		case 'tipo_suporte_alterar':
		case 'tipo_suporte_consultar':
			require_once 'tipo_suporte_cadastro.php';
			break;



			//LOCAL ARQUIVO
		case 'lugar_localizador_excluir':
		case 'lugar_localizador_desativar':
		case 'lugar_localizador_reativar':
		case 'lugar_localizador_listar':
		case 'lugar_localizador_selecionar':
			require_once 'lugar_localizador_lista.php';
			break;

		case 'lugar_localizador_cadastrar':
		case 'lugar_localizador_alterar':
		case 'lugar_localizador_consultar':
			require_once 'lugar_localizador_cadastro.php';
			break;



			//ARQUIVO
		case 'localizador_excluir':
		case 'localizador_desativar':
		case 'localizador_reativar':
		case 'localizador_listar':
		case 'localizador_selecionar':
			require_once 'localizador_lista.php';
			break;

		case 'localizador_cadastrar':
		case 'localizador_alterar':
		case 'localizador_consultar':
			require_once 'localizador_cadastro.php';
			break;

			//S?RIES
		case 'grupo_serie_excluir':
		case 'grupo_serie_desativar':
		case 'grupo_serie_reativar':
		case 'grupo_serie_listar':
		case 'grupo_serie_selecionar':
			require_once 'grupo_serie_lista.php';
			break;

		case 'grupo_serie_cadastrar':
		case 'grupo_serie_alterar':
		case 'grupo_serie_consultar':
			require_once 'grupo_serie_cadastro.php';
			break;

			//S?RIES
		case 'serie_excluir':
		case 'serie_desativar':
		case 'serie_reativar':
		case 'serie_listar':
		case 'serie_selecionar':
			require_once 'serie_lista.php';
			break;

		case 'serie_cadastrar':
		case 'serie_alterar':
		case 'serie_consultar':
			require_once 'serie_cadastro.php';
			break;


			//ASSINANTE
		case 'assinante_excluir':
		case 'assinante_listar':
		case 'assinante_selecionar':
			require_once 'assinante_lista.php';
			break;

		case 'assinante_cadastrar':
		case 'assinante_alterar':
		case 'assinante_consultar':
			require_once 'assinante_cadastro.php';
			break;

    //texto_padrao_interno_INTERNO
    case 'texto_padrao_interno_excluir':
    case 'texto_padrao_interno_listar':
    case 'texto_padrao_interno_selecionar':
      require_once 'texto_padrao_interno_lista.php';
      break;

    case 'texto_padrao_interno_cadastrar':
    case 'texto_padrao_interno_alterar':
    case 'texto_padrao_interno_consultar':
      require_once 'texto_padrao_interno_cadastro.php';
      break;

		//BASE_CONHECIMENTO
		case 'base_conhecimento_listar_associadas':
			require_once 'base_conhecimento_associacoes.php';
			break;

		case 'base_conhecimento_pesquisar':
			require_once 'base_conhecimento_pesquisar.php';
			break;

		case 'base_conhecimento_versoes':
			require_once 'base_conhecimento_versoes.php';
			break;

		case 'base_conhecimento_listar':
    case 'base_conhecimento_liberar':
    case 'base_conhecimento_cancelar_liberacao':
    case 'base_conhecimento_excluir':
			require_once 'base_conhecimento_lista.php';
			break;

		case 'base_conhecimento_cadastrar':
		case 'base_conhecimento_consultar':
		case 'base_conhecimento_nova_versao':
		case 'base_conhecimento_alterar':
		case 'base_conhecimento_upload_anexo':
			require_once 'base_conhecimento_cadastro.php';
			break;

		case 'anotacao_registrar':
			require_once 'anotacao_cadastro.php';
			break;



			//PUBLICA?AO
		case 'publicacao_agendar':
		case 'publicacao_alterar_agendamento':
			require_once 'publicacao_cadastro.php';
			break;

		case 'publicacao_listar':
		case 'publicacao_cancelar_agendamento':
			require_once 'publicacao_lista.php';
			break;

			//LOCALIZADOR
		case 'localizador_imprimir_etiqueta':
		case 'localizador_imprimir_etiqueta_pdf':
			require_once 'localizador_etiquetas.php';
			break;

		case 'localizador_protocolos_listar':
			require_once 'localizador_protocolos_listar.php';
			break;

		case 'arquivamento_migrar_localizador':
			require_once 'migrar_localizador.php';
			break;

		case 'acesso_externo_gerenciar':
		case 'acesso_externo_disponibilizar':
			require_once 'acesso_externo_gerenciar.php';
			break;

		case 'orgao_excluir':
		case 'orgao_desativar':
		case 'orgao_reativar':
		case 'orgao_listar':
		case 'orgao_selecionar':
			require_once 'orgao_lista.php';
			break;

		case 'orgao_alterar':
		case 'orgao_consultar':
		case 'orgao_cadastrar':
		case 'orgao_upload':
			require_once 'orgao_cadastro.php';
			break;

		case 'feed_excluir':
		case 'feed_listar':
			require_once 'feed_lista.php';
			break;

		case 'retorno_programado_consultar':
		case 'retorno_programado_desativar':
		case 'retorno_programado_excluir':
		case 'retorno_programado_listar':
			require_once 'retorno_programado_lista.php';
			break;

		case 'retorno_programado_alterar':
		case 'retorno_programado_cadastrar':
			require_once 'retorno_programado_cadastro.php';
			break;

		case 'unidade_hoje_gerar':
			require_once 'unidade_hoje_geracao.php';
			break;

		case 'unidade_hoje_detalhar':
			require_once 'unidade_hoje_detalhe.php';
			break;

		case 'gerar_estatisticas_ouvidoria':
		case 'gerar_estatisticas_unidade':
			require_once 'estatisticas_geracao.php';
			break;

		case 'gerar_estatisticas_arquivamento':
			require_once 'estatisticas_arquivamento.php';
			break;

		case 'estatisticas_grafico_exibir':
			require_once 'estatisticas_grafico.php';
			break;

		case 'estatisticas_detalhar_arquivamento':
			require_once 'estatisticas_arquivamento_detalhe.php';
			break;

		case 'estatisticas_detalhar_unidade':
		case 'estatisticas_detalhar_ouvidoria':
		  require_once 'estatisticas_detalhe.php';
		  break;

		case 'gerar_estatisticas_desempenho_processos':
		  require_once 'estatisticas_desempenho.php';
		  break;

	  case 'estatisticas_detalhar_desempenho':
	  case 'estatisticas_detalhar_desempenho_procedimento':
	    require_once 'estatisticas_desempenho_detalhe.php';
	    break;

    case 'inspecao_administrativa_gerar':
      require_once 'inspecao_administrativa.php';
      break;

    case 'inspecao_administrativa_detalhar':
      require_once 'inspecao_administrativa_detalhe.php';
      break;


		//GRUPO CONTATO
		case 'grupo_contato_institucional_excluir':
		case 'grupo_contato_institucional_desativar':
		case 'grupo_contato_institucional_reativar':
		case 'grupo_contato_institucional_listar':
		case 'grupo_contato_excluir':
		case 'grupo_contato_desativar':
		case 'grupo_contato_reativar':
		case 'grupo_contato_listar':
			require_once 'grupo_contato_lista.php';
			break;

		case 'grupo_contato_institucional_cadastrar':
		case 'grupo_contato_institucional_alterar':
		case 'grupo_contato_institucional_consultar':
		case 'grupo_contato_cadastrar':
		case 'grupo_contato_alterar':
		case 'grupo_contato_consultar':
			require_once 'grupo_contato_cadastro.php';
			break;

    case 'grupo_contato_selecionar':
      require_once 'grupo_contato_selecao.php';
      break;


		case 'grupo_email_institucional_listar':
		case 'grupo_email_institucional_excluir':
		case 'grupo_email_institucional_desativar':
		case 'grupo_email_institucional_reativar':
		case 'grupo_email_excluir':
		case 'grupo_email_listar':
			require_once 'grupo_email_lista.php';
			break;

		case 'grupo_email_institucional_cadastrar':
		case 'grupo_email_institucional_alterar':
		case 'grupo_email_institucional_consultar':
		case 'grupo_email_cadastrar':
		case 'grupo_email_alterar':
		case 'grupo_email_consultar':
			require_once 'grupo_email_cadastro.php';
			break;

		case 'grupo_email_selecionar':
		case 'grupo_email_institucional_selecionar':
			require_once 'grupo_email_selecao.php';
			break;

		case 'grupo_unidade_institucional_listar':
		case 'grupo_unidade_institucional_excluir':
		case 'grupo_unidade_institucional_desativar':
		case 'grupo_unidade_institucional_reativar':
		case 'grupo_unidade_excluir':
    case 'grupo_unidade_listar':
      require_once 'grupo_unidade_lista.php';
      break;

		case 'grupo_unidade_institucional_cadastrar':
		case 'grupo_unidade_institucional_alterar':
		case 'grupo_unidade_institucional_consultar':
    case 'grupo_unidade_cadastrar':
    case 'grupo_unidade_alterar':
    case 'grupo_unidade_consultar':
      require_once 'grupo_unidade_cadastro.php';
      break;

    case 'grupo_unidade_selecionar':
    case 'grupo_unidade_institucional_selecionar':
      require_once 'grupo_unidade_selecao.php';
      break;

    case 'unidade_tramitacao_selecionar':
      require_once 'unidade_tramitacao.php';
      break;

    case 'marcador_cadastrar':
    case 'marcador_alterar':
    case 'marcador_consultar':
      require_once 'marcador_cadastro.php';
      break;

    case 'marcador_listar':
    case 'marcador_excluir':
    case 'marcador_desativar':
    case 'marcador_reativar':
      require_once 'marcador_lista.php';
      break;
    
		case 'sair':
			SessaoSEI::getInstance()->sair();
			break;

		case 'procedimento_reencaminhar_ouvidoria':
			require_once 'procedimento_reencaminhar_ouvidoria.php';
			break;

		case 'procedimento_finalizar_ouvidoria':
		  require_once 'procedimento_finalizar_ouvidoria.php';
		  break;

		// Acompanhamento Especial
		case 'acompanhamento_cadastrar':
		case 'acompanhamento_alterar':
			require_once 'acompanhamento_cadastro.php';
			break;

		case 'acompanhamento_listar':
		  require_once 'acompanhamento_lista.php';
		  break;

		case 'acompanhamento_excluir':
		  if ($_GET['acao_origem']=='acompanhamento_alterar'){
		    require_once 'acompanhamento_cadastro.php';
		  }else{
			  require_once 'acompanhamento_lista.php';
		  }
			break;

		case 'grupo_acompanhamento_cadastrar':
		case 'grupo_acompanhamento_alterar':
			require_once 'grupo_acompanhamento_cadastro.php';
			break;

		case 'grupo_acompanhamento_listar':
		case 'grupo_acompanhamento_excluir':
			require_once 'grupo_acompanhamento_lista.php';
			break;

	  case 'acompanhamento_listar_ouvidoria':
	  case 'acompanhamento_gerar_grafico_ouvidoria':
	    require_once 'acompanhamento_ouvidoria.php';
	    break;

	  case 'acompanhamento_detalhar_ouvidoria':
	    require_once 'acompanhamento_ouvidoria_detalhe.php';
	    break;

    case 'controle_unidade_gerar':
    case 'controle_unidade_gerar_grafico':
      require_once 'controle_unidade.php';
      break;

    case 'controle_unidade_detalhar':
      require_once 'controle_unidade_detalhe.php';
      break;

    //modelos de protocolos
		case 'protocolo_modelo_cadastrar':
		case 'protocolo_modelo_alterar':
			require_once 'protocolo_modelo_cadastro.php';
			break;

		case 'protocolo_modelo_listar':
		case 'protocolo_modelo_excluir':
		case 'documento_modelo_selecionar':
			require_once 'protocolo_modelo_lista.php';
			break;

		case 'grupo_protocolo_modelo_cadastrar':
		case 'grupo_protocolo_modelo_alterar':
			require_once 'grupo_protocolo_modelo_cadastro.php';
			break;

		case 'grupo_protocolo_modelo_listar':
		case 'grupo_protocolo_modelo_excluir':
			require_once 'grupo_protocolo_modelo_lista.php';
			break;


		case 'controle_interno_listar':
		case 'controle_interno_excluir':
			require_once 'controle_interno_lista.php';
			break;

		case 'controle_interno_cadastrar':
		case 'controle_interno_alterar':
		case 'controle_interno_consultar':
			require_once 'controle_interno_cadastro.php';
			break;

		case 'tabela_assuntos_ativar':
    case 'tabela_assuntos_excluir':
    case 'tabela_assuntos_listar':
      require_once 'tabela_assuntos_lista.php';
      break;

    case 'tabela_assuntos_cadastrar':
    case 'tabela_assuntos_alterar':
    case 'tabela_assuntos_consultar':
      require_once 'tabela_assuntos_cadastro.php';
      break;

			//ASSUNTO
		case 'assunto_excluir':
		case 'assunto_desativar':
		case 'assunto_reativar':
		case 'assunto_listar':
		case 'assunto_selecionar':
			require_once 'assunto_lista.php';
			break;

		case 'assunto_cadastrar':
		case 'assunto_alterar':
		case 'assunto_consultar':
			require_once 'assunto_cadastro.php';
			break;

		case 'mapeamento_assunto_gerenciar':
		case 'mapeamento_assunto_listar':
			require_once 'mapeamento_assunto_lista.php';
			break;

		case 'para_saber_mais':
			require_once 'para_saber_mais.php';
			break;

		//Sistema Cliente
		case 'servico_excluir':
		case 'servico_desativar':
		case 'servico_reativar':
		case 'servico_listar':
		case 'servico_selecionar':
			require_once 'servico_lista.php';
			break;

		case 'servico_cadastrar':
		case 'servico_alterar':
		case 'servico_consultar':
			require_once 'servico_cadastro.php';
			break;

		//editor
		case 'modelo_excluir':
		case 'modelo_reativar':
		case 'modelo_desativar':
	  case 'modelo_listar':
	  case 'modelo_selecionar':
			  require_once 'editor/modelo_lista.php';
			  break;

		case 'modelo_cadastrar':
	  case 'modelo_alterar':
	  case 'modelo_consultar':
			  require_once 'editor/modelo_cadastro.php';
			  break;

		case 'modelo_clonar':
			  require_once 'editor/modelo_clonar.php';
			  break;

		case 'documento_versao_listar':
		case 'documento_versao_recuperar':
			  require_once 'editor/documento_versao_lista.php';
			  break;

    case 'documento_versao_comparar':
      require_once 'editor/documento_versao_comparar.php';
      break;

		case 'secao_modelo_excluir':
	  case 'secao_modelo_listar':
	  case 'secao_modelo_selecionar':
	  case 'secao_modelo_reativar':
	  case 'secao_modelo_desativar':
			  require_once 'editor/secao_modelo_lista.php';
			  break;

	  case 'secao_modelo_cadastrar':
	  case 'secao_modelo_alterar':
	  case 'secao_modelo_consultar':
			  require_once 'editor/secao_modelo_cadastro.php';
			  break;

		case 'estilo_excluir':
	  case 'estilo_listar':
	  case 'estilo_selecionar':
			  require_once 'editor/estilo_lista.php';
			  break;

	  case 'estilo_cadastrar':
	  case 'estilo_alterar':
	  case 'estilo_consultar':
			  require_once 'editor/estilo_cadastro.php';
			  break;

		//Servi?os
		case 'operacao_servico_excluir':
		case 'operacao_servico_listar':
		case 'operacao_servico_selecionar':
			require_once 'operacao_servico_lista.php';
			break;

		case 'operacao_servico_cadastrar':
		case 'operacao_servico_alterar':
		case 'operacao_servico_consultar':
			require_once 'operacao_servico_cadastro.php';
			break;

		case 'usuario_sistema_excluir':
		case 'usuario_sistema_desativar':
		case 'usuario_sistema_reativar':
		case 'usuario_sistema_listar':
			require_once 'usuario_sistema_lista.php';
			break;

		case 'usuario_sistema_cadastrar':
		case 'usuario_sistema_alterar':
		case 'usuario_sistema_consultar':
			require_once 'usuario_sistema_cadastro.php';
			break;

		case 'arquivo_extensao_excluir':
		case 'arquivo_extensao_listar':
		case 'arquivo_extensao_reativar':
		case 'arquivo_extensao_desativar':
			require_once 'arquivo_extensao_lista.php';
			break;

		case 'arquivo_extensao_cadastrar':
		case 'arquivo_extensao_alterar':
		case 'arquivo_extensao_consultar':
			require_once 'arquivo_extensao_cadastro.php';
			break;

		case 'imagem_formato_excluir':
		case 'imagem_formato_listar':
		case 'imagem_formato_reativar':
		case 'imagem_formato_desativar':
		  require_once 'imagem_formato_lista.php';
		  break;

		case 'imagem_formato_cadastrar':
		case 'imagem_formato_alterar':
		case 'imagem_formato_consultar':
		  require_once 'imagem_formato_cadastro.php';
		  break;

		case 'exibir_arquivo':
		  require_once 'exibe_arquivo.php';
		  break;

		case 'numeracao_listar':
    case 'numeracao_excluir':
			require_once 'numeracao_lista.php';
			break;

		case 'numeracao_ajustar':
			require_once 'numeracao_ajuste.php';
			break;

		//E-mails do sistema
		case 'email_sistema_listar':
	  case 'email_sistema_desativar':
	  case 'email_sistema_reativar':
			require_once 'email_sistema_lista.php';
			break;

		case 'email_sistema_alterar':
		case 'email_sistema_consultar':
			require_once 'email_sistema_cadastro.php';
			break;

		case 'tipo_procedimento_individual_verificar':
		  require_once 'tipo_procedimento_verificacao.php';
		  break;

	  //Tipo Conferencia
	  case 'tipo_conferencia_excluir':
	  case 'tipo_conferencia_desativar':
	  case 'tipo_conferencia_reativar':
	  case 'tipo_conferencia_listar':
	  case 'tipo_conferencia_selecionar':
	    require_once 'tipo_conferencia_lista.php';
	    break;

	  case 'tipo_conferencia_cadastrar':
	  case 'tipo_conferencia_alterar':
	  case 'tipo_conferencia_consultar':
	    require_once 'tipo_conferencia_cadastro.php';
	    break;

		case 'pesquisa_solr_ajuda':
	  case 'assinatura_digital_ajuda':

			require_once 'ajuda_exibir.php';
			break;

			//Veiculo Publica??o
		case 'veiculo_publicacao_excluir':
		case 'veiculo_publicacao_desativar':
		case 'veiculo_publicacao_reativar':
		case 'veiculo_publicacao_listar':
		case 'veiculo_publicacao_selecionar':
		  require_once 'veiculo_publicacao_lista.php';
		  break;

		case 'veiculo_publicacao_cadastrar':
		case 'veiculo_publicacao_alterar':
		case 'veiculo_publicacao_consultar':
		  require_once 'veiculo_publicacao_cadastro.php';
		  break;

		//Feriado
		case 'feriado_excluir':
		case 'feriado_listar':
		  require_once 'feriado_lista.php';
		  break;

		case 'feriado_cadastrar':
		case 'feriado_alterar':
		case 'feriado_consultar':
		  require_once 'feriado_cadastro.php';
		  break;

	  case 'veiculo_imprensa_nacional_excluir':
	  case 'veiculo_imprensa_nacional_listar':
	    require_once 'veiculo_imprensa_nacional_lista.php';
	    break;

	  case 'veiculo_imprensa_nacional_cadastrar':
	  case 'veiculo_imprensa_nacional_alterar':
	  case 'veiculo_imprensa_nacional_consultar':
	    require_once 'veiculo_imprensa_nacional_cadastro.php';
	    break;

    case 'secao_imprensa_nacional_excluir':
    case 'secao_imprensa_nacional_listar':
      require_once 'secao_imprensa_nacional_lista.php';
      break;

    case 'secao_imprensa_nacional_cadastrar':
    case 'secao_imprensa_nacional_alterar':
    case 'secao_imprensa_nacional_consultar':
      require_once 'secao_imprensa_nacional_cadastro.php';
      break;


    case 'hipotese_legal_excluir':
    case 'hipotese_legal_desativar':
    case 'hipotese_legal_reativar':
    case 'hipotese_legal_listar':
    case 'hipotese_legal_selecionar':
      require_once 'hipotese_legal_lista.php';
      break;

    case 'hipotese_legal_cadastrar':
    case 'hipotese_legal_alterar':
    case 'hipotese_legal_consultar':
      require_once 'hipotese_legal_cadastro.php';
      break;

    case 'assinatura_verificar':
    case 'assinatura_download_p7s':
      require_once 'assinatura_verificacao.php';
      break;

    case 'velocidade_transferencia_listar':
    case 'velocidade_transferencia_excluir':
      require_once 'velocidade_transferencia_lista.php';
      break;

    case 'monitoramento_servico_listar':
    case 'monitoramento_servico_excluir':
      require_once 'monitoramento_servico_lista.php';
      break;

    case 'indexar':
      require_once 'indexacao.php';
      break;

		case 'modulo_listar':
			require_once 'modulo_lista.php';
			break;
		
		case 'ajuda_variaveis_secao_modelo':
		case 'ajuda_variaveis_tarjas':
		case 'ajuda_variaveis_email_sistema':
			require_once 'ajuda/ajuda_variaveis.php';
			break;

		default:

			foreach($SEI_MODULOS as $objModulo){
				if ($objModulo->executar('processarControlador', $_GET['acao'])!=null){
					return;
				}
			}

			if ($_GET['acao']=='infra_auditoria_listar' && ConfiguracaoSEI::getInstance()->isSetValor('BancoAuditoriaSEI')){
        BancoAuditoria::setObjInfraIBanco(BancoAuditoriaSEI::getInstance());
      }

		  if (!InfraControlador::processar($_GET['acao'], PaginaSEI::getInstance(), SessaoSEI::getInstance(), BancoSEI::getInstance(), LogSEI::getInstance(), CacheSEI::getInstance())){
			  throw new InfraException('A??o \''.$_GET['acao'].'\' n?o reconhecida pelo controlador.');
		  }
  }

}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}
?>