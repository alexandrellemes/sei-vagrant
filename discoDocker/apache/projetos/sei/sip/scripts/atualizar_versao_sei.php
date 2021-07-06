<?
 require_once dirname(__FILE__).'/../web/Sip.php';

class VersaoSipRN extends InfraScriptVersao {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSip::getInstance();
  }

  public function versao_2_0_0($strVersaoAtual){

  }

  public function versao_2_1_0($strVersaoAtual){
    try{

      $objInfraMetaBD = new InfraMetaBD(BancoSip::getInstance());
      $objInfraMetaBD->setBolValidarIdentificador(true);

      if (BancoSip::getInstance() instanceof InfraMySql){
        $objScriptRN = new ScriptRN();
        $objScriptRN->atualizarSequencias();
      }

      if (BancoSip::getInstance() instanceof InfraOracle){
        $objInfraMetaBD->alterarColuna('orgao','ordem',$objInfraMetaBD->tipoNumero(),'not null');
        $objInfraMetaBD->alterarColuna('infra_log','sta_tipo',$objInfraMetaBD->tipoTextoFixo(1),'not null');
      }

      $numIdSistemaSei = ScriptSip::obterIdSistema('SEI');
      $numIdPerfilSeiBasico = ScriptSip::obterIdPerfil($numIdSistemaSei,'Básico');
      $numIdPerfilSeiAdministrador = ScriptSip::obterIdPerfil($numIdSistemaSei,'Administrador');

      $numIdSistemaSip = ScriptSip::obterIdSistema('SIP');
      $numIdPerfilSipAdministradorSistema = ScriptSip::obterIdPerfil($numIdSistemaSip,'Administrador de Sistema');
      $numIdMenuSip = ScriptSip::obterIdMenu($numIdSistemaSip,'Principal');
      $numIdItemMenuSipPerfis = ScriptSip::obterIdItemMenu($numIdSistemaSip,$numIdMenuSip,'Perfis');

      $this->logar('ATUALIZANDO BASE/RECURSOS SIP...');

      $this->fixIndices($objInfraMetaBD);

      BancoSip::getInstance()->executarSql('update infra_agendamento_tarefa set sta_periodicidade_execucao=\'N\', periodicidade_complemento=\'0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55\' where comando=\'AgendamentoRN::testarAgendamento\'');

      ScriptSip::adicionarRecursoPerfil($numIdSistemaSip, $numIdPerfilSipAdministradorSistema, 'perfil_importar');
      $objRecursoDTO = ScriptSip::adicionarRecursoPerfil($numIdSistemaSip, $numIdPerfilSipAdministradorSistema, 'perfil_comparar');
      ScriptSip::adicionarItemMenu($numIdSistemaSip,$numIdPerfilSipAdministradorSistema,$numIdMenuSip,$numIdItemMenuSipPerfis,$objRecursoDTO->getNumIdRecurso(),'Comparar', 80);

      $this->logar('ATUALIZANDO RECURSOS SEI...');
      ScriptSip::adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'usuario_cadastrar');
      ScriptSip::adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'usuario_excluir');
      ScriptSip::adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'serie_publicacao_excluir');
      ScriptSip::adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'unidade_publicacao_excluir');
      ScriptSip::adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'anexo_download');

      ScriptSip::adicionarAuditoria($numIdSistemaSei,'Geral',array(
          'procedimento_gerar_pdf',
          'procedimento_gerar_zip'));

    }catch(Exception $e){
      throw new InfraException('Erro atualizando versão.', $e);
    }
  }

  protected function fixIndices(InfraMetaBD $objInfraMetaBD)
  {

    InfraDebug::getInstance()->setBolDebugInfra(true);

    $this->logar('ATUALIZANDO INDICES...');

    $arrTabelas21 = array('administrador_sistema','contexto','coordenador_perfil','coordenador_unidade','dtproperties','grupo_rede',
    'hierarquia','item_menu','login','menu','orgao','perfil','permissao','recurso','recurso_vinculado','regra_auditoria',
    'rel_grupo_rede_unidade','rel_hierarquia_unidade','rel_orgao_autenticacao','rel_perfil_item_menu','rel_perfil_recurso',
    'rel_regra_auditoria_recurso','servidor_autenticacao','sistema','tipo_permissao','unidade','usuario');

    $objInfraMetaBD->processarIndicesChavesEstrangeiras($arrTabelas21);

    InfraDebug::getInstance()->setBolDebugInfra(false);
  }

}
  try{

    session_start();

    SessaoSip::getInstance(false);

    BancoSip::getInstance()->setBolScript(true);

    if (!ConfiguracaoSip::getInstance()->isSetValor('BancoSip','UsuarioScript')){
      throw new InfraException('Chave BancoSip/UsuarioScript não encontrada.');
    }

    if (InfraString::isBolVazia(ConfiguracaoSip::getInstance()->getValor('BancoSip','UsuarioScript'))){
      throw new InfraException('Chave BancoSip/UsuarioScript não possui valor.');
    }

    if (!ConfiguracaoSip::getInstance()->isSetValor('BancoSip','SenhaScript')){
      throw new InfraException('Chave BancoSip/SenhaScript não encontrada.');
    }

    if (InfraString::isBolVazia(ConfiguracaoSip::getInstance()->getValor('BancoSip','SenhaScript'))){
      throw new InfraException('Chave BancoSip/SenhaScript não possui valor.');
    }

    $objVersaoSipRN = new VersaoSipRN();
    $objVersaoSipRN->setStrNome('SIP');
    $objVersaoSipRN->setStrParametroVersao('SIP_VERSAO');
    $objVersaoSipRN->setArrVersoes(array('2.0.0' => 'versao_2_0_0',
                                         '2.1.0' => 'versao_2_1_0'
    ));
    $objVersaoSipRN->setStrVersaoAtual('2.1.0');
    $objVersaoSipRN->setStrVersaoInfra('1.517');
    $objVersaoSipRN->setBolMySql(true);
    $objVersaoSipRN->setBolOracle(true);
    $objVersaoSipRN->setBolSqlServer(true);
    $objVersaoSipRN->setBolErroVersaoInexistente(true);

    $objVersaoSipRN->atualizarVersao();

	}catch(Exception $e){
		echo(InfraException::inspecionar($e));
		try{LogSip::getInstance()->gravar(InfraException::inspecionar($e));	}catch (Exception $e){}
		exit(1);
	}
?>