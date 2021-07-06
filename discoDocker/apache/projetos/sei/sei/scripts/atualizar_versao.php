<?
	try{
	
    require_once dirname(__FILE__).'/../web/SEI.php';

    class VersaoSeiRN extends InfraScriptVersao {

      public function __construct(){
        parent::__construct();
      }

      protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
      }

      public function versao_3_0_0($strVersaoAtual){
      }

      public function versao_3_1_0($strVersaoAtual){
        try{

          $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
          $objInfraMetaBD->setBolValidarIdentificador(true);

          if (BancoSEI::getInstance() instanceof InfraMySql){
            $objScriptRN = new ScriptRN();
            $objScriptRN->atualizarSequencias();
          }

          if (BancoSEI::getInstance() instanceof InfraOracle){
            $objInfraMetaBD->alterarColuna('acompanhamento','tipo_visualizacao',$objInfraMetaBD->tipoNumero(),'not null');
            $objInfraMetaBD->alterarColuna('andamento_situacao','sin_ultimo',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('assinatura','id_tarja_assinatura',$objInfraMetaBD->tipoNumero(),'not null');
            $objInfraMetaBD->alterarColuna('assunto','id_tabela_assuntos',$objInfraMetaBD->tipoNumero(),'not null');
            $objInfraMetaBD->alterarColuna('base_conhecimento','sta_documento',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('contato','sta_natureza',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('contato','sin_endereco_associado',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('contato','id_contato_associado',$objInfraMetaBD->tipoNumero(),'not null');
            $objInfraMetaBD->alterarColuna('contato','id_tipo_contato',$objInfraMetaBD->tipoNumero(),'not null');
            $objInfraMetaBD->alterarColuna('controle_unidade','id_situacao',$objInfraMetaBD->tipoNumero(),'not null');
            $objInfraMetaBD->alterarColuna('documento','sta_documento',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('grupo_contato','sin_ativo',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('grupo_contato','sta_tipo',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('infra_log','sta_tipo',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('infra_navegador','user_agent',$objInfraMetaBD->tipoTextoVariavel(4000),'not null');
            $objInfraMetaBD->alterarColuna('orgao','id_contato',$objInfraMetaBD->tipoNumero(),'not null');
            $objInfraMetaBD->alterarColuna('serie','sin_interno',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('tarja_assinatura','sin_ativo',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('tarja_assinatura','sta_tarja_assinatura',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('tipo_contato','sin_sistema',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('tipo_contato','sta_acesso',$objInfraMetaBD->tipoTextoFixo(1),'not null');
            $objInfraMetaBD->alterarColuna('usuario','id_contato',$objInfraMetaBD->tipoNumero(),'not null');
            $objInfraMetaBD->alterarColuna('usuario','sin_acessibilidade',$objInfraMetaBD->tipoTextoFixo(1),'not null');
          }

          $this->fixIndices($objInfraMetaBD);

          InfraDebug::getInstance()->setBolDebugInfra(true);

          $this->logar('ATUALIZANDO PARAMETROS...');

          $rs = BancoSEI::getInstance()->consultarSql('select count(*) as total from infra_parametro where nome = \'SEI_HABILITAR_VERIFICACAO_REPOSITORIO\'');
          if ($rs[0]['total']==0) {
            BancoSEI::getInstance()->executarSql('insert into infra_parametro (nome, valor) values (\'SEI_HABILITAR_VERIFICACAO_REPOSITORIO\',\'0\')');
          }

          $rs = BancoSEI::getInstance()->consultarSql('select count(*) as total from infra_parametro where nome = \'SEI_EXIBIR_ARVORE_RESTRITO_SEM_ACESSO\'');
          if ($rs[0]['total']==0) {
            BancoSEI::getInstance()->executarSql('insert into infra_parametro (nome, valor) values (\'SEI_EXIBIR_ARVORE_RESTRITO_SEM_ACESSO\',\'0\')');
          }

          $rs = BancoSEI::getInstance()->consultarSql('select count(*) as total from infra_parametro where nome = \'SEI_EMAIL_CONVERTER_ANEXO_HTML_PARA_PDF\'');
          if ($rs[0]['total']==0) {
            BancoSEI::getInstance()->executarSql('insert into infra_parametro (nome, valor) values (\'SEI_EMAIL_CONVERTER_ANEXO_HTML_PARA_PDF\',\'0\')');
          }

          $rs = BancoSEI::getInstance()->consultarSql('select count(*) as total from infra_parametro where nome = \'SEI_ALTERACAO_NIVEL_ACESSO_DOCUMENTO\'');
          if ($rs[0]['total']==0) {
            BancoSEI::getInstance()->executarSql('insert into infra_parametro (nome, valor) values (\'SEI_ALTERACAO_NIVEL_ACESSO_DOCUMENTO\',\'0\')');
          }

          $objInfraMetaBD->adicionarColuna('assinatura','agrupador',$objInfraMetaBD->tipoTextoVariavel(36),'null');
          $objInfraMetaBD->criarIndice('assinatura','i01_assinatura', array('agrupador'));

          if (count($objInfraMetaBD->obterColunasTabela('protocolo','protocolo_formatado_pesq_inv'))==0){
            $objInfraMetaBD->adicionarColuna('protocolo', 'protocolo_formatado_pesq_inv', $objInfraMetaBD->tipoTextoVariavel(50), 'null');
            BancoSEI::getInstance()->executarSql('update protocolo set protocolo_formatado_pesq_inv = reverse(protocolo_formatado_pesquisa)');
            $objInfraMetaBD->alterarColuna('protocolo', 'protocolo_formatado_pesq_inv', $objInfraMetaBD->tipoTextoFixo(50), 'not null');
            $objInfraMetaBD->criarIndice('protocolo', 'ak4_protocolo', array('protocolo_formatado_pesq_inv'), true);
          }

          BancoSEI::getInstance()->executarSql('update infra_agendamento_tarefa set comando = replace(comando,\' \',\'\')');

          BancoSEI::getInstance()->executarSql('update infra_agendamento_tarefa set sta_periodicidade_execucao=\'N\', periodicidade_complemento=\'0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55\' where comando=\'AgendamentoRN::testarAgendamento\'');

          BancoSEI::getInstance()->executarSql('UPDATE tarefa SET sin_permite_processo_fechado=\'S\' WHERE id_tarefa='.TarefaRN::$TI_CANCELAMENTO_LIBERACAO_ACESSO_EXTERNO);

          BancoSEI::getInstance()->executarSql('update contato set sin_endereco_associado=\'N\' where id_contato=id_contato_associado and sin_endereco_associado=\'S\'');

          //altera opcao do corretor ortografico de avaliacao gratuita para nativo do navegador
          BancoSEI::getInstance()->executarSql('update orgao set sta_corretor_ortografico=\'B\' where sta_corretor_ortografico=\'G\'');

          if (BancoSEI::getInstance() instanceof InfraSqlServer){
            BancoSEI::getInstance()->executarSql('update tarefa set nome = replace(nome,\'\r\n\',char(10))');
          }

          if (BancoSEI::getInstance() instanceof InfraOracle){
            BancoSEI::getInstance()->executarSql('update tarefa set nome = replace(nome,\'\r\n\',CHR(10))');
          }


          $objInfraMetaBD->adicionarColuna('protocolo','dta_inclusao',$objInfraMetaBD->tipoDataHora(),'null');
          $this->fixDataCadastroProtocolo();
          $objInfraMetaBD->alterarColuna('protocolo','dta_inclusao',$objInfraMetaBD->tipoDataHora(),'not null');
          $objInfraMetaBD->criarIndice('protocolo','i07_protocolo',array('dta_inclusao','sta_protocolo','id_unidade_geradora'));

          $objInfraMetaBD->adicionarColuna('contato','numero_passaporte',$objInfraMetaBD->tipoTextoVariavel(15),'null');
          $objInfraMetaBD->adicionarColuna('contato','id_pais_passaporte',$objInfraMetaBD->tipoNumero(),'null');
          $objInfraMetaBD->adicionarChaveEstrangeira('fk_contato_pais_passaporte','contato',array('id_pais_passaporte'),'pais',array('id_pais'));

          $this->fixAcessoProcessosAnexadosRestritos();

          $this->fixQuantidadeControleProcessos();

          $this->fixNumeracao();

          $this->fixIndexacaoOrgaos();
          $this->fixIndexacaoUnidades();
          $this->fixIndexacaoUsuarios();
          $this->fixIndexacaoContatos();
          $this->fixIndexacaoAssuntos();

          BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'126\',\'Alterado tipo do processo de "@TIPO_PROCESSO_ANTERIOR@" para "@TIPO_PROCESSO_ATUAL@"\',\'N\',\'S\',\'S\',\'N\',\'N\')');
          BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'128\',\'Alterado número do processo de "@PROTOCOLO_ANTERIOR@" para "@PROTOCOLO_ATUAL@"\',\'N\',\'S\',\'S\',\'N\',\'N\')');
          BancoSEI::getInstance()->executarSql('insert into tarefa (id_tarefa,nome,sin_historico_resumido,sin_historico_completo,sin_fechar_andamentos_abertos,sin_lancar_andamento_fechado,sin_permite_processo_fechado) values (\'129\',\'Alterada data de autuação do processo de "@DATA_ANTERIOR@" para "@DATA_ATUAL@"\',\'N\',\'S\',\'S\',\'N\',\'N\')');

        }catch(Exception $e){
          InfraDebug::getInstance()->setBolLigado(false);
          InfraDebug::getInstance()->setBolDebugInfra(false);
          InfraDebug::getInstance()->setBolEcho(false);
          throw new InfraException('Erro atualizando versão.', $e);
        }
      }

      protected function fixIndices(InfraMetaBD $objInfraMetaBD)
      {
        InfraDebug::getInstance()->setBolDebugInfra(true);

        $this->logar('ATUALIZANDO INDICES...');

        $arrTabelas31 = array('acesso','acesso_externo','acompanhamento','andamento_marcador','andamento_situacao','anexo','anotacao',
            'arquivamento','arquivo_extensao','assinante','assinatura','assunto','assunto_proxy','atividade','atributo',
            'atributo_andamento','auditoria_protocolo','base_conhecimento','bloco','cargo','cargo_funcao','cidade',
            'conjunto_estilos','conjunto_estilos_item','contato','contexto','controle_interno','controle_unidade',
            'documento','documento_conteudo','dominio','email_grupo_email','email_sistema','email_unidade','email_utilizado',
            'estatisticas','estilo','feed','feriado','grupo_acompanhamento','grupo_contato','grupo_email','grupo_protocolo_modelo',
            'grupo_serie','grupo_unidade','hipotese_legal','imagem_formato','localizador','lugar_localizador','mapeamento_assunto',
            'marcador','modelo','monitoramento_servico','nivel_acesso_permitido','notificacao','novidade','numeracao',
            'observacao','operacao_servico','ordenador_despesa','orgao','pais','participante','procedimento','protocolo',
            'protocolo_modelo','publicacao','publicacao_legado','rel_acesso_ext_protocolo','rel_assinante_unidade',
            'rel_base_conhec_tipo_proced','rel_bloco_protocolo','rel_bloco_unidade','rel_controle_interno_orgao',
            'rel_controle_interno_serie','rel_controle_interno_tipo_proc','rel_controle_interno_unidade','rel_grupo_contato',
            'rel_grupo_unidade_unidade','rel_notificacao_documento','rel_protocolo_assunto','rel_protocolo_atributo',
            'rel_protocolo_protocolo','rel_secao_modelo_estilo','rel_secao_mod_cj_estilos_item','rel_serie_assunto',
            'rel_serie_veiculo_publicacao','rel_situacao_unidade','rel_tipo_procedimento_assunto','rel_unidade_tipo_contato',
            'retorno_programado','secao_documento','secao_imprensa_nacional','secao_modelo','serie','serie_escolha',
            'serie_publicacao','serie_restricao','servico','situacao','tabela_assuntos','tarefa','tarja_assinatura',
            'texto_padrao_interno','tipo_conferencia','tipo_contato','tipo_formulario','tipo_localizador','tipo_procedimento',
            'tipo_procedimento_escolha','tipo_proced_restricao','tipo_suporte','tratamento','uf','unidade','unidade_publicacao',
            'usuario','veiculo_imprensa_nacional','veiculo_publicacao','velocidade_transferencia','versao_secao_documento',
            'vocativo')
        ;

        $objInfraMetaBD->processarIndicesChavesEstrangeiras($arrTabelas31);

        $objInfraMetaBD->criarIndice('numeracao', 'ak_numeracao', array('ano', 'id_serie', 'id_orgao', 'id_unidade'), true);
        $objInfraMetaBD->criarIndice('documento', 'i04_documento', array('numero', 'id_serie'));
        $objInfraMetaBD->criarIndice('atributo_andamento', 'i02_atributo_andamento', array('nome', 'id_origem'));
        $objInfraMetaBD->criarIndice('atividade', 'i03_atividade', array('id_unidade', 'dth_conclusao', 'sin_inicial'));
        $objInfraMetaBD->criarIndice('atividade','i10_atividade',array('dth_abertura','id_tarefa'));
        $objInfraMetaBD->criarIndice('acesso', 'i02_acesso', array('id_protocolo', 'sta_tipo'));
        $objInfraMetaBD->criarIndice('acesso','i03_acesso',array('id_protocolo','id_unidade','id_usuario'));
        $objInfraMetaBD->criarIndice('andamento_marcador', 'i02_andamento_marcador', array('id_unidade', 'id_procedimento', 'sin_ultimo'));
        $objInfraMetaBD->criarIndice('retorno_programado', 'i06_retorno_programado', array('dta_programada'));
        $objInfraMetaBD->criarIndice('protocolo', 'i10_protocolo', array('protocolo_formatado_pesquisa', 'sta_nivel_acesso_global', 'id_protocolo'));
        $objInfraMetaBD->criarIndice('protocolo', 'i11_protocolo', array('sta_protocolo', 'sta_nivel_acesso_global', 'id_protocolo'));
        $objInfraMetaBD->criarIndice('protocolo', 'i12_protocolo', array('sta_estado', 'sta_protocolo', 'sta_nivel_acesso_global', 'id_protocolo'));
        $objInfraMetaBD->criarIndice('protocolo', 'i13_protocolo', array('id_protocolo', 'sta_protocolo', 'id_usuario_gerador', 'id_unidade_geradora', 'dta_geracao'));
        $objInfraMetaBD->criarIndice('protocolo', 'i14_protocolo', array('id_protocolo', 'id_hipotese_legal', 'id_unidade_geradora'));
        $objInfraMetaBD->criarIndice('atributo_andamento', 'i04_atributo_andamento', array('id_atividade', 'id_atributo_andamento'));
        $objInfraMetaBD->criarIndice('atividade', 'i16_atividade', array('id_unidade', 'id_protocolo', 'dth_conclusao', 'id_usuario', 'id_atividade', 'id_usuario_atribuicao'));

        InfraDebug::getInstance()->setBolDebugInfra(false);
      }

      protected function fixAcessoProcessosAnexadosRestritosConectado(){
        try{

          InfraDebug::getInstance()->setBolDebugInfra(false);

          InfraDebug::getInstance()->gravar('RESTABELECENDO ACESSO EM PROCESSOS ANEXADOS RESTRITOS');

          $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
          $objRelProtocoloProtocoloDTO->setDistinct(true);
          $objRelProtocoloProtocoloDTO->retDblIdProtocolo1();
          $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);

          $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
          $arrIdProcessosPai = InfraArray::converterArrInfraDTO($objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO),'IdProtocolo1');

          $objAtividadeRN = new AtividadeRN();
          $objAcessoRN = new AcessoRN();
          $objProtocoloRN = new ProtocoloRN();

          $n = 0;
          $numRegistros = count($arrIdProcessosPai);
          foreach($arrIdProcessosPai as $dblIdProcessoPai) {

            $arrAtualizacao = array();

            if ((++$n >= 100 && $n % 100 == 0) || $n == 1 || $n == $numRegistros) {
              InfraDebug::getInstance()->gravar('VERIFICANDO '.$n.' DE '.$numRegistros);
            }

            $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
            $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
            $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
            $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($dblIdProcessoPai);

            $arrIdProtocolos = $arrIdProcessosFilho = InfraArray::converterArrInfraDTO($objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO),'IdProtocolo2');

            $arrIdProtocolos[] = $dblIdProcessoPai;

            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->retDblIdProtocolo();
            $objProtocoloDTO->retStrProtocoloFormatado();
            $objProtocoloDTO->retStrStaNivelAcessoGlobal();
            $objProtocoloDTO->setDblIdProtocolo($arrIdProtocolos, InfraDTO::$OPER_IN);
            $arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->listarRN0668($objProtocoloDTO),'IdProtocolo');


            foreach($arrIdProcessosFilho as $dblIdProcessoFilho) {
              if ($arrObjProtocoloDTO[$dblIdProcessoPai]->getStrStaNivelAcessoGlobal() != $arrObjProtocoloDTO[$dblIdProcessoFilho]->getStrStaNivelAcessoGlobal()) {
                InfraDebug::getInstance()->gravar($arrObjProtocoloDTO[$dblIdProcessoPai]->getStrProtocoloFormatado().' -> '.$arrObjProtocoloDTO[$dblIdProcessoFilho]->getStrProtocoloFormatado());
                $arrAtualizacao[$dblIdProcessoPai] = 0;
                break;
              }
            }

            if (!isset($arrAtualizacao[$dblIdProcessoPai]) && $arrObjProtocoloDTO[$dblIdProcessoPai]->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_RESTRITO){

              $objAtividadeDTO = new AtividadeDTO();
              $objAtividadeDTO->setDistinct(true);
              $objAtividadeDTO->retNumIdUnidade();
              $objAtividadeDTO->setNumIdTarefa(TarefaRN::getArrTarefasTramitacao(), InfraDTO::$OPER_IN);
              $objAtividadeDTO->setDblIdProtocolo($dblIdProcessoPai);
              $objAtividadeDTO->setOrdNumIdUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);

              $arrIdUnidadesTramitacao = InfraArray::converterArrInfraDTO($objAtividadeRN->listarRN0036($objAtividadeDTO), 'IdUnidade');

              $objAcessoDTO = new AcessoDTO();
              $objAcessoDTO->retDblIdProtocolo();
              $objAcessoDTO->retNumIdUnidade();
              $objAcessoDTO->setDblIdProtocolo($arrIdProtocolos, InfraDTO::$OPER_IN);
              $objAcessoDTO->setStrStaTipo(AcessoRN::$TA_RESTRITO_UNIDADE);
              $objAcessoDTO->setOrdDblIdProtocolo(InfraDTO::$TIPO_ORDENACAO_ASC);
              $objAcessoDTO->setOrdNumIdUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);

              $arrObjAcessoDTO = InfraArray::indexarArrInfraDTO($objAcessoRN->listar($objAcessoDTO),'IdProtocolo', true);

              foreach($arrObjAcessoDTO as $dblIdProcessoAcesso => $arr) {

                $arrIdUnidadesAcesso = InfraArray::converterArrInfraDTO($arr,'IdUnidade');

                if ($arrIdUnidadesTramitacao != $arrIdUnidadesAcesso) {
                  InfraDebug::getInstance()->gravar($arrObjProtocoloDTO[$dblIdProcessoAcesso]->getStrProtocoloFormatado());
                  $arrAtualizacao[$dblIdProcessoPai] = 0;
                  break;
                }
              }
            }


            if (count($arrAtualizacao)) {

              foreach(array_keys($arrAtualizacao) as $dblIdProcesso) {
                $objMudarNivelAcessoDTO = new MudarNivelAcessoDTO();
                $objMudarNivelAcessoDTO->setStrSinLancarAndamento('N');
                $objMudarNivelAcessoDTO->setStrStaOperacao(ProtocoloRN::$TMN_ANEXACAO);
                $objMudarNivelAcessoDTO->setDblIdProtocolo($dblIdProcesso);
                $objMudarNivelAcessoDTO->setStrStaNivel(null);
                $objProtocoloRN->mudarNivelAcesso($objMudarNivelAcessoDTO);
              }
            }
          }

          InfraDebug::getInstance()->setBolDebugInfra(true);

        }catch(Exception $e){
          throw new InfraException('Erro restabelecendo acesso em processos anexados restritos.', $e);
        }
      }

      protected function fixQuantidadeControleProcessosConectado(){
        try{

          InfraDebug::getInstance()->gravar('CORRIGINDO QUANTIDADE DE PROCESSOS DO CONTROLE DE PROCESSOS');

          InfraDebug::getInstance()->setBolDebugInfra(false);

          $objUnidadeDTO = new UnidadeDTO();
          $objUnidadeDTO->setBolExclusaoLogica(false);
          $objUnidadeDTO->retNumIdUnidade();

          $objUnidadeRN = new UnidadeRN();
          $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);

          $objAtividadeBD = new AtividadeBD(BancoSEI::getInstance());

          foreach($arrObjUnidadeDTO as $objUnidadeDTO) {


            $sql = 'select count(*) as total, '.
                BancoSEI::getInstance()->formatarSelecaoDbl('atividade', 'id_protocolo', 'idprotocolo').', '.
                BancoSEI::getInstance()->formatarSelecaoNum('atividade', 'id_unidade', 'idunidade').', '.
                BancoSEI::getInstance()->formatarSelecaoNum('atividade', 'id_usuario', 'idusuario').
                ' from atividade '.
                ' where id_tarefa in (32, 61, 66, 118) and dth_conclusao is null '.
                ' and id_unidade='.$objUnidadeDTO->getNumIdUnidade().
                ' group by id_protocolo, id_unidade, id_usuario '.
                ' having count(*) > 1 '.
                ' order by id_protocolo';

            $rs = BancoSEI::getInstance()->consultarSql($sql);

            $numRegistros = count($rs);

            $n = 0;

            foreach ($rs as $item) {

              if ((++$n >= 100 && $n % 100 == 0) || $n == $numRegistros) {
                InfraDebug::getInstance()->gravar($objUnidadeDTO->getNumIdUnidade().': '.$n.' DE '.$numRegistros);
              }

              $objAtividadeDTO = new AtividadeDTO();
              $objAtividadeDTO->retNumIdAtividade();
              $objAtividadeDTO->retDthAbertura();
              $objAtividadeDTO->setDblIdProtocolo(BancoSEI::getInstance()->formatarLeituraDbl($item['idprotocolo']));
              $objAtividadeDTO->setNumIdUnidade(BancoSEI::getInstance()->formatarLeituraNum($item['idunidade']));
              $objAtividadeDTO->setNumIdUsuario(BancoSEI::getInstance()->formatarLeituraNum($item['idusuario']));
              $objAtividadeDTO->setDthConclusao(null);
              $objAtividadeDTO->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);
              $arrObjAtividadeDTO = $objAtividadeBD->listar($objAtividadeDTO);

              $numAndamentos = count($arrObjAtividadeDTO);

              if ($numAndamentos > 1) {
                for ($i = 1; $i < $numAndamentos; $i++) {
                  $arrObjAtividadeDTO[$i]->unSetDthAbertura();
                  $arrObjAtividadeDTO[$i]->setDthConclusao($arrObjAtividadeDTO[0]->getDthAbertura());
                  $objAtividadeBD->alterar($arrObjAtividadeDTO[$i]);
                }
              }
            }
          }

          InfraDebug::getInstance()->setBolDebugInfra(true);

        }catch(Exception $e){
          throw new InfraException('Erro corrigindo quantidade de processos do Controle de Processos.', $e);
        }
      }

      protected function fixNumeracaoConectado(){
        try{

          $rs = BancoSEI::getInstance()->consultarSql('select count(*), ano, id_serie, id_orgao, id_unidade from numeracao group by ano, id_serie, id_orgao, id_unidade having count(*) > 1');

          $objNumeracaoDTO = new NumeracaoDTO();

          $objNumeracaoRN = new NumeracaoRN();

          foreach($rs as $item) {

            $objNumeracaoDTO->retNumIdNumeracao();
            $objNumeracaoDTO->setNumAno($item['ano']);
            $objNumeracaoDTO->setNumIdSerie($item['id_serie']);
            $objNumeracaoDTO->setNumIdOrgao($item['id_orgao']);
            $objNumeracaoDTO->setNumIdUnidade($item['id_unidade']);
            $objNumeracaoDTO->setOrdNumIdNumeracao(InfraDTO::$TIPO_ORDENACAO_ASC);

            $arrObjNumeracaoDTO = $objNumeracaoRN->listar($objNumeracaoDTO);

            for($i=1;$i<count($arrObjNumeracaoDTO);$i++){
              $objNumeracaoRN->excluir(array($arrObjNumeracaoDTO[$i]));
            }
          }

        }catch(Exception $e){
          throw new InfraException('Erro corrigindo numeração.', $e);
        }
      }

      protected function fixDataCadastroProtocoloConectado(){
        try{

          InfraDebug::getInstance()->setBolDebugInfra(false);

          InfraDebug::getInstance()->gravar('POPULANDO DATA DE CADASTRO EM PROTOCOLO');

          $objAtividadeDTO = new AtividadeDTO();
          $objAtividadeDTO->retDthAbertura();
          $objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_ASC);
          $objAtividadeDTO->setNumMaxRegistrosRetorno(1);

          $objAtividadeRN = new AtividadeRN();
          $objAtributoAdamentoRN = new AtributoAndamentoRN();
          $objAtividadeDTO = $objAtividadeRN->consultarRN0033($objAtividadeDTO);
          if($objAtividadeDTO){
            $dtaInicial = substr($objAtividadeDTO->getDthAbertura(),0,10);
          } else {
            $dtaInicial = InfraData::getStrDataAtual();
          }
          $dtaFinal = InfraData::getStrDataAtual();

          $mesAno = substr($dtaInicial,3,2).'/'.substr($dtaInicial,6,4);

          while(InfraData::compararDatasSimples($dtaInicial,$dtaFinal)>=0) {

            $mesAnoAtual = substr($dtaInicial,3,2).'/'.substr($dtaInicial,6,4);

            if ($mesAnoAtual!=$mesAno) {
              InfraDebug::getInstance()->gravar($mesAnoAtual.'...');
              $mesAno = $mesAnoAtual;
            }

            $objAtividadeDTO = new AtividadeDTO();
            $objAtividadeDTO->retDblIdProtocolo();
            $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_GERACAO_PROCEDIMENTO);
            $objAtividadeDTO->adicionarCriterio(array('Abertura', 'Abertura'),
                array(InfraDTO::$OPER_MAIOR_IGUAL, InfraDTO::$OPER_MENOR_IGUAL),
                array($dtaInicial.' 00:00:00', $dtaInicial.' 23:59:59'),
                InfraDTO::$OPER_LOGICO_AND);

            $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

            if (count($arrObjAtividadeDTO)) {

              $arrIdProcessos = InfraArray::converterArrInfraDTO($arrObjAtividadeDTO, 'IdProtocolo');

              $sql = 'update protocolo set dta_inclusao='.BancoSEI::getInstance()->formatarGravacaoDta($dtaInicial).' where ';

              //oracle
              $arrPartes = array_chunk($arrIdProcessos, 1000);

              $strOr = '';
              foreach ($arrPartes as $arrParte) {
                if ($strOr != '') {
                  $sql .= $strOr;
                }
                $sql .= ' id_protocolo in ('.implode(',', $arrParte).')';
                $strOr = ' OR ';
              }

              BancoSEI::getInstance()->executarSql($sql);

              $arrTarefasDocumentos = array(TarefaRN::$TI_GERACAO_DOCUMENTO, TarefaRN::$TI_RECEBIMENTO_DOCUMENTO);

              foreach ($arrTarefasDocumentos as $numIdTarefaDocumento) {

                $objAtividadeDTO = new AtividadeDTO();
                $objAtividadeDTO->retNumIdAtividade();
                $objAtividadeDTO->setNumIdTarefa($numIdTarefaDocumento);
                $objAtividadeDTO->adicionarCriterio(array('Abertura', 'Abertura'),
                    array(InfraDTO::$OPER_MAIOR_IGUAL, InfraDTO::$OPER_MENOR_IGUAL),
                    array($dtaInicial.' 00:00:00', $dtaInicial.' 23:59:59'),
                    InfraDTO::$OPER_LOGICO_AND);

                $arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);

                if (count($arrObjAtividadeDTO)) {

                  $objAtributoAdamentoDTO = new AtributoAndamentoDTO();
                  $objAtributoAdamentoDTO->retStrIdOrigem();
                  $objAtributoAdamentoDTO->setNumIdAtividade(InfraArray::converterArrInfraDTO($arrObjAtividadeDTO, 'IdAtividade'), InfraDTO::$OPER_IN);
                  $objAtributoAdamentoDTO->setStrNome('DOCUMENTO');

                  $arrObjAtributoAndamentoDTO = $objAtributoAdamentoRN->listarRN1367($objAtributoAdamentoDTO);

                  if (count($arrObjAtributoAndamentoDTO)) {

                    $arrIdDocumentos = InfraArray::converterArrInfraDTO($arrObjAtributoAndamentoDTO, 'IdOrigem');

                    $sql = 'update protocolo set dta_inclusao='.BancoSEI::getInstance()->formatarGravacaoDta($dtaInicial).' where ';

                    //oracle
                    $arrPartes = array_chunk($arrIdDocumentos, 1000);

                    $strOr = '';
                    foreach ($arrPartes as $arrParte) {
                      if ($strOr != '') {
                        $sql .= $strOr;
                      }
                      $sql .= ' id_protocolo in ('.implode(',', $arrParte).')';
                      $strOr = ' OR ';
                    }

                    BancoSEI::getInstance()->executarSql($sql);
                  }
                }
              }
            }
            $dtaInicial = InfraData::calcularData(1,InfraData::$UNIDADE_DIAS,InfraData::$SENTIDO_ADIANTE, $dtaInicial);
          }

          BancoSEI::getInstance()->executarSql('update protocolo set dta_inclusao=dta_geracao where dta_inclusao is null');

          InfraDebug::getInstance()->setBolDebugInfra(true);

        }catch(Exception $e){
          throw new InfraException('Erro populando data de cadastro em protocolo.', $e);
        }
      }

      protected function fixIndexacaoOrgaosConectado(){
        try{

          InfraDebug::getInstance()->setBolDebugInfra(false);

          $objOrgaoDTO = new OrgaoDTO();
          $objOrgaoDTO->setBolExclusaoLogica(false);
          $objOrgaoDTO->retNumIdOrgao();

          $objOrgaoRN = new OrgaoRN();
          $arrObjOrgaoDTO = $objOrgaoRN->listarRN1353($objOrgaoDTO);

          InfraDebug::getInstance()->gravar('REINDEXANDO ORGAOS...'.count($arrObjOrgaoDTO));

          foreach($arrObjOrgaoDTO as $objOrgaoDTO){
            $objOrgaoRN->montarIndexacao($objOrgaoDTO);
          }

          InfraDebug::getInstance()->setBolDebugInfra(true);

        }catch(Exception $e){
          throw new InfraException('Erro executando indexação de órgãos.',$e);
        }
      }

      protected function fixIndexacaoUnidadesConectado(){
        try{

          InfraDebug::getInstance()->setBolDebugInfra(false);

          $objUnidadeDTO = new UnidadeDTO();
          $objUnidadeDTO->setBolExclusaoLogica(false);
          $objUnidadeDTO->retNumIdUnidade();

          $objUnidadeRN = new UnidadeRN();
          $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);

          InfraDebug::getInstance()->gravar('REINDEXANDO UNIDADES...'.count($arrObjUnidadeDTO));

          foreach($arrObjUnidadeDTO as $objUnidadeDTO){
            $objUnidadeRN->montarIndexacao($objUnidadeDTO);
          }

          InfraDebug::getInstance()->setBolDebugInfra(true);

        }catch(Exception $e){
          throw new InfraException('Erro executando indexação de unidades.',$e);
        }
      }

      protected function fixIndexacaoUsuariosConectado(){
        try{

          InfraDebug::getInstance()->setBolDebugInfra(false);
          InfraDebug::getInstance()->gravar('REINDEXANDO USUARIOS...');

          $rs = BancoSEI::getInstance()->consultarSql('select id_usuario from usuario');

          $numRegistros = count($rs);

          $objUsuarioDTO = new UsuarioDTO();
          $objUsuarioRN = new UsuarioRN();

          $n = 0;
          foreach($rs as $item){

            $objUsuarioDTO->setNumIdUsuario($item['id_usuario']);
            $objUsuarioRN->montarIndexacao($objUsuarioDTO);

            if ((++$n >= 1000 && $n % 1000 == 0) || $n == $numRegistros) {
              InfraDebug::getInstance()->gravar($n . ' DE ' . $numRegistros);
            }
          }

          InfraDebug::getInstance()->setBolDebugInfra(true);

        }catch(Exception $e){
          throw new InfraException('Erro executando indexação de usuários.',$e);
        }
      }

      protected function fixIndexacaoContatosConectado(){
        try{

          InfraDebug::getInstance()->setBolDebugInfra(false);
          InfraDebug::getInstance()->gravar('REINDEXANDO CONTATOS...');

          $rs = BancoSEI::getInstance()->consultarSql('select id_contato from contato');

          $numRegistros = count($rs);

          $objContatoDTO = new ContatoDTO();
          $objContatoRN = new ContatoRN();

          $n = 0;
          foreach($rs as $item){

            $objContatoDTO->setNumIdContato($item['id_contato']);
            $objContatoRN->montarIndexacaoRN0450($objContatoDTO);

            if ((++$n >= 1000 && $n % 1000 == 0) || $n == $numRegistros) {
              InfraDebug::getInstance()->gravar($n . ' DE ' . $numRegistros);
            }
          }

          InfraDebug::getInstance()->setBolDebugInfra(true);

        }catch(Exception $e){
          throw new InfraException('Erro executando indexação de contatos.',$e);
        }
      }

      protected function fixIndexacaoAssuntosConectado(){
        try{

          InfraDebug::getInstance()->setBolDebugInfra(false);

          $objAssuntoDTO = new AssuntoDTO();
          $objAssuntoDTO->setBolExclusaoLogica(false);
          $objAssuntoDTO->retNumIdAssunto();

          $objAssuntoRN = new AssuntoRN();
          $arrObjAssuntoDTO = $objAssuntoRN->listarRN0247($objAssuntoDTO);

          InfraDebug::getInstance()->gravar('REINDEXANDO ASSUNTOS...'.count($arrObjAssuntoDTO));

          foreach($arrObjAssuntoDTO as $objAssuntoDTO){
            $objAssuntoRN->montarIndexacaoRN0505($objAssuntoDTO);
          }

          InfraDebug::getInstance()->setBolDebugInfra(true);

        }catch(Exception $e){
          throw new InfraException('Erro executando indexação de assuntos.',$e);
        }
      }
    }

    session_start();

    SessaoSEI::getInstance(false);

    BancoSEI::getInstance()->setBolScript(true);

    if (!ConfiguracaoSEI::getInstance()->isSetValor('BancoSEI','UsuarioScript')){
      throw new InfraException('Chave BancoSEI/UsuarioScript não encontrada.');
    }

    if (InfraString::isBolVazia(ConfiguracaoSEI::getInstance()->getValor('BancoSEI','UsuarioScript'))){
      throw new InfraException('Chave BancoSEI/UsuarioScript não possui valor.');
    }

    if (!ConfiguracaoSEI::getInstance()->isSetValor('BancoSEI','SenhaScript')){
      throw new InfraException('Chave BancoSEI/SenhaScript não encontrada.');
    }

    if (InfraString::isBolVazia(ConfiguracaoSEI::getInstance()->getValor('BancoSEI','SenhaScript'))){
      throw new InfraException('Chave BancoSEI/SenhaScript não possui valor.');
    }

    $objVersaoSeiRN = new VersaoSeiRN();
    $objVersaoSeiRN->setStrNome('SEI');
    $objVersaoSeiRN->setStrVersaoAtual('3.1.0');
    $objVersaoSeiRN->setStrParametroVersao('SEI_VERSAO');
    $objVersaoSeiRN->setArrVersoes(array('3.0.0' => 'versao_3_0_0',
                                         '3.1.0' => 'versao_3_1_0'
    ));
    $objVersaoSeiRN->setStrVersaoInfra('1.517');
    $objVersaoSeiRN->setBolMySql(true);
    $objVersaoSeiRN->setBolOracle(true);
    $objVersaoSeiRN->setBolSqlServer(true);
    $objVersaoSeiRN->setBolErroVersaoInexistente(true);

    $objVersaoSeiRN->atualizarVersao();

	}catch(Exception $e){
		echo(InfraException::inspecionar($e));
		try{LogSEI::getInstance()->gravar(InfraException::inspecionar($e));	}catch (Exception $e){}
		exit(1);
	}
?>