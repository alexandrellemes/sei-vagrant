<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 08/06/2011 - criado por mga
*
* Vers?o do Gerador de C?digo: 1.13.1
*
* Vers?o no CVS: $Id$
*/

try {
  require_once dirname(__FILE__).'/SEI.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  $strParametros = '';
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
  }
  
  if (isset($_GET['id_procedimento'])){
    $strParametros .= '&id_procedimento='.$_GET['id_procedimento'];
  }
  
  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
  
  $arrComandos = array();
  
  switch($_GET['acao']){
  	
  	case 'procedimento_credencial_conceder':
  		
  		$strTitulo = 'Concess?o de Credencial';
  		
  		try{
		    $objConcederCredencialDTO = new ConcederCredencialDTO();
		    
		    $arrAtividadesOrigem = explode(',',$_POST['hdnIdAtividades']);
		 
	     	$objAtividadeRN = new AtividadeRN();
	
	      $objConcederCredencialDTO->setDblIdProcedimento($_GET['id_procedimento']);
	      $objConcederCredencialDTO->setNumIdUsuario($_POST['hdnIdUsuario']);
	      $objConcederCredencialDTO->setNumIdUnidade($_POST['selUnidade']);
	      $objConcederCredencialDTO->setArrAtividadesOrigem(InfraArray::gerarArrInfraDTO('AtividadeDTO','IdAtividade',explode(',',$_POST['hdnIdAtividades'])));
	      
	      $ret = $objAtividadeRN->concederCredencial($objConcederCredencialDTO);
	      
	      PaginaSEI::getInstance()->setStrMensagem('Opera??o realizada com sucesso.');
	      
	      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_credencial_gerenciar&acao_origem='.$_GET['acao'].'&resultado=1'.$strParametros.PaginaSEI::getInstance()->montarAncora($ret->getNumIdAtividade())));
	      die;
          
  		}catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
  		}
  		
      break;
  		
    case 'procedimento_credencial_cassar':
    	
    	$strTitulo = 'Cassa??o de Credencial';
    	
      try{
      	
      	$arrObjAtividadeDTO = InfraArray::gerarArrInfraDTO('AtividadeDTO','IdAtividade',PaginaSEI::getInstance()->getArrStrItensSelecionados());
      	
        $objAtividadeRN = new AtividadeRN();
        $objAtividadeRN->cassarCredenciais($arrObjAtividadeDTO);
        
        PaginaSEI::getInstance()->setStrMensagem('Opera??o realizada com sucesso.');
        
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].'&resultado=1'.$strParametros.PaginaSEI::getInstance()->montarAncora(implode(',',PaginaSEI::getInstance()->getArrStrItensSelecionados()))));
      die;
  	
    case 'procedimento_credencial_gerenciar':
      $strTitulo = 'Gerenciar Credenciais';
	    break;
	
	    default:
	      throw new InfraException("A??o '".$_GET['acao']."' n?o reconhecida.");
  }

  
  $arrComandos = array();

  
  $objProcedimentoDTO = new ProcedimentoDTO();
  $objProcedimentoDTO->setDblIdProcedimento($_GET['id_procedimento']);
	  
	$objAtividadeRN = new AtividadeRN();
	$arrObjAtividadeDTO = $objAtividadeRN->listarCredenciais($objProcedimentoDTO);
	
  $numRegistros = count($arrObjAtividadeDTO);

  $bolAcaoConceder = SessaoSEI::getInstance()->verificarPermissao('procedimento_credencial_conceder');
  $bolAcaoCassar = SessaoSEI::getInstance()->verificarPermissao('procedimento_credencial_cassar');
  	
  if ($bolAcaoConceder){
  	$strLinkConceder = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_credencial_conceder&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].$strParametros);
  }
  
  if ($numRegistros > 0){
  	
    if ($bolAcaoCassar){
    	//$arrComandos[] = '<button type="submit" accesskey="a" name="sbmCassar" id="sbmCassar" onclick="acaoCassacaoMultipla();" value="Cassar" class="infraButton">C<span class="infraTeclaAtalho">a</span>ssar</button>';
      $strLinkCassar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_credencial_cassar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].$strParametros);
    }
  	
    //$arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    $strResultado = '';

    $strSumarioTabela = 'Tabela de Credenciais Concedidas / Cassadas.';
    $strCaptionTabela = 'Credenciais Concedidas / Cassadas';

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n"; //90
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="1%" style="display:none;">'.PaginaSEI::getInstance()->getThCheck('','Infra','style="display:none;"').'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">Usu?rio</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">Unidade</th>'."\n";
    $strResultado .= '<th class="infraTh" width="30%">Concess?o</th>'."\n";
    $strResultado .= '<th class="infraTh" width="30%">Cassa??o</th>'."\n";
    $strResultado .= '<th class="infraTh">A??es</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    
    $n = 0;
    foreach($arrObjAtividadeDTO as $objAtividadeDTO){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      $strResultado .= "\n".'<td valign="top" style="display:none;">';
      //if ($objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_PROCESSO_CONCESSAO_CREDENCIAL || $objAtividadeDTO->getNumIdTarefa()==TarefaRN::$TI_PROCESSO_TRANSFERENCIA_CREDENCIAL){
        $strResultado .= PaginaSEI::getInstance()->getTrCheck($n++,$objAtividadeDTO->getNumIdAtividade(),$objAtividadeDTO->getStrSiglaUsuario().'/'.$objAtividadeDTO->getStrSiglaUnidade(),'N','Infra','style="visibility:hidden;"');
      //}else{
      //	$strResultado .= '&nbsp;';
      //}
      $strResultado .= '</td>';

      $strResultado .= "\n".'<td align="center"  valign="top">';
      $strResultado .= '<a alt="'.PaginaSEI::tratarHTML($objAtividadeDTO->getStrNomeUsuario()).'" title="'.PaginaSEI::tratarHTML($objAtividadeDTO->getStrNomeUsuario()).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($objAtividadeDTO->getStrSiglaUsuario()).'</a>';
      $strResultado .= '</td>';

      $strResultado .= "\n".'<td align="center"  valign="top">';
      $strResultado .= '<a alt="'.PaginaSEI::tratarHTML($objAtividadeDTO->getStrDescricaoUnidade()).'" title="'.PaginaSEI::tratarHTML($objAtividadeDTO->getStrDescricaoUnidade()).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($objAtividadeDTO->getStrSiglaUnidade()).'</a>';
      $strResultado .= '</td>'."\n";

      $strResultado .= '<td align="center" valign="top">'.substr($objAtividadeDTO->getDthAbertura(),0,16).'</td>';

      if (in_array($objAtividadeDTO->getNumIdTarefa(), TarefaRN::getArrTarefasCassacaoCredencial(false))) {
        $strResultado .= '<td align="center" valign="top">';
        foreach ($objAtividadeDTO->getArrObjAtributoAndamentoDTO() as $objAtributoAndamentoDTO) {
          if ($objAtributoAndamentoDTO->getStrNome() == 'DATA_HORA') {
            $strResultado .= substr($objAtributoAndamentoDTO->getStrValor(), 0, 16);
          }
        }
        $strResultado .= '</td>';
      }else{
        $strResultado .= '<td>&nbsp;</td>';
      }

			$strResultado .= "\n".'<td align="center" valign="top">';
		  if ($bolAcaoCassar && in_array($objAtividadeDTO->getNumIdTarefa(),TarefaRN::getArrTarefasConcessaoCredencial(false))){
        $strResultado .= '<a href="#ID-'.$objAtividadeDTO->getNumIdAtividade().'"  onclick="acaoCassar(\''.$objAtividadeDTO->getNumIdAtividade().'\',\''.$objAtividadeDTO->getStrSiglaUsuario().'/'.$objAtividadeDTO->getStrSiglaUnidade().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/sei_cassar_credencial.gif" title="Cassar Credencial de Acesso" alt="Cassar Credencial de Acesso" class="infraImg" /></a>&nbsp;';
      }else{
      	$strResultado .= '<span style="line-height:1.5em">&nbsp;</span>';
      }
			$strResultado .= '</td>';
      
      $strResultado .= '</tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  
  //$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'])).'\'" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

  $strLinkAjaxUsuario = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=usuario_auto_completar');
  $strLinkAjaxUnidadesUsuario = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=usuario_unidades_permissao');
  $strLinkMontarArvore = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_visualizar&acao_origem='.$_GET['acao'].'&id_procedimento='.$_GET['id_procedimento'].'&montar_visualizacao=0');

  //busca andamentos abertos do processo para validar na hora de salvar (verifica se ocorreu alteracao) 
  if ($_GET['acao_origem']=='arvore_visualizar' || 
      $_GET['acao_origem']=='procedimento_credencial_conceder' ||
      $_GET['acao_origem']=='procedimento_credencial_cassar'){
  	$objAtividadeRN = new AtividadeRN();
  	$objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
  	$objPesquisaPendenciaDTO->setDblIdProtocolo($_GET['id_procedimento']);
  	$objPesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
  	$objPesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
  	$arrObjProcedimentoDTO = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTO);

  	if (count($arrObjProcedimentoDTO)==0){
  		throw new InfraException('Processo n?o encontrado.');
  	}
  	$arrAtividadesOrigem = InfraArray::converterArrInfraDTO($arrObjProcedimentoDTO[0]->getArrObjAtividadeDTO(),'IdAtividade');
  }else {
  	if ($_POST['hdnIdAtividades']!=''){
  		$arrAtividadesOrigem = explode(',',$_POST['hdnIdAtividades']);
  	}
  }
  $arrNumIdAtividades = implode(',',$arrAtividadesOrigem);

  
}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
#lblUsuario {position:absolute;left:0%;top:15%;}
#txtUsuario {position:absolute;left:0%;top:50%;width:40%;}
#lblUnidade {position:absolute;left:41%;top:15%;visibility:hidden;}
#selUnidade {position:absolute;left:41%;top:50%;width:40%;visibility:hidden;}
#btnConceder {position:absolute;left:82%;top:50%;visibility:hidden;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

var objAutoCompletarUsuario = null;
var objAjaxUnidadesUsuario = null;
var objTabelaUsuariosUnidades = null;
var bolRemontandoTela = false;

function inicializar(){

  <?if (($_GET['acao_origem']=='procedimento_credencial_conceder' || $_GET['acao_origem']=='procedimento_credencial_cassar') && $_GET['resultado']=='1') { ?>
    parent.document.getElementById('ifrArvore').src = '<?=$strLinkMontarArvore?>';
  <?}?>

  objAutoCompletarUsuario = new infraAjaxAutoCompletar('hdnIdUsuario','txtUsuario','<?=$strLinkAjaxUsuario?>');
  //objAutoCompletarUsuario.maiusculas = true;
  //objAutoCompletarUsuario.mostrarAviso = true;
  //objAutoCompletarUsuario.tempoAviso = 1000;
  //objAutoCompletarUsuario.tamanhoMinimo = 3;
  objAutoCompletarUsuario.limparCampo = true;
  //objAutoCompletarUsuario.bolExecucaoAutomatica = false;

  objAutoCompletarUsuario.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtUsuario').value;
  };
  
  objAutoCompletarUsuario.processarResultado = function(id,descricao,complemento){
    if (id!=''){
      objAjaxUnidadesUsuario.executar();
    }else{
	    document.getElementById('lblUnidade').style.visibility = 'hidden';
      document.getElementById('selUnidade').style.visibility = 'hidden';
      document.getElementById('selUnidade').options.length = 0;
      document.getElementById('btnConceder').style.visibility = 'hidden';
    }
  };
  
  objAjaxUnidadesUsuario = new infraAjaxMontarSelect('selUnidade','<?=$strLinkAjaxUnidadesUsuario?>');
	  objAjaxUnidadesUsuario.prepararExecucao = function(){
	    return 'id_usuario='+document.getElementById('hdnIdUsuario').value;
	  };
	  objAjaxUnidadesUsuario.processarResultado = function(nroItens){
	    
	    document.getElementById('lblUnidade').style.visibility = 'hidden';
      document.getElementById('selUnidade').style.visibility = 'hidden';
      document.getElementById('btnConceder').style.visibility = 'hidden';
	    
      if (document.getElementById('selUnidade').options.length == 1){
        if (document.getElementById('selUnidade').options[0].value=='null'){
          alert('Usu?rio n?o tem acesso a nenhuma unidade.');
        }else{
          document.getElementById('selUnidade').options[0].selected = true;
          document.getElementById('btnConceder').style.left = '41%'; 
          document.getElementById('btnConceder').style.visibility = 'visible';
        }
	    }else if (document.getElementById('selUnidade').options.length > 1){
	      document.getElementById('lblUnidade').style.visibility = 'visible';
	      document.getElementById('selUnidade').style.visibility = 'visible';
        document.getElementById('btnConceder').style.left = '82%'; 
	      document.getElementById('selUnidade').focus();
	      
	      if (bolRemontandoTela){
	        infraSelectSelecionarItem('selUnidade','<?=$_POST['selUnidade']?>');
	        escolheuUnidade();
	      }
	    }
	  }
  
<? if ($_GET['acao']=='procedimento_credencial_conceder'){ ?>
  //erro ao conceder remonta a tela
  bolRemontandoTela = true;
  objAutoCompletarUsuario.selecionar('<?=$_POST['hdnIdUsuario']?>','<?=$_POST['txtUsuario']?>');
<? }else{ ?>
	document.getElementById('txtUsuario').focus();
<? } ?>	
	
  infraEfeitoTabelas();
}

<? if ($bolAcaoConceder){ ?>
function conceder(){
  if (infraTrim(document.getElementById('hdnIdUsuario'))==''){
    alert('Informe um Usu?rio.');
    document.getElementById('txtUsuario').focus();
    return;
  }

  if (!infraSelectSelecionado('selUnidade')){
    alert('Selecione uma Unidade.');
    document.getElementById('selUnidade').focus();
    return;
  }

  document.getElementById('frmGerenciarCredenciais').action = '<?=$strLinkConceder?>';
  document.getElementById('frmGerenciarCredenciais').submit();
}

function escolheuUnidade(){
  if (!infraSelectSelecionado('selUnidade')){
    document.getElementById('btnConceder').style.visibility = 'hidden'; 
  }else{
    document.getElementById('btnConceder').style.visibility = 'visible';
    document.getElementById('btnConceder').focus();
  }
}
<? } ?>

<? if ($bolAcaoCassar){ ?>
function acaoCassar(id,desc){
  if (confirm("Confirma cassa??o da credencial \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmGerenciarCredenciais').action='<?=$strLinkCassar?>';
    document.getElementById('frmGerenciarCredenciais').submit();
  }
}

function acaoCassacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhuma credencial selecionada.');
    return;
  }
  if (confirm("Confirma cassa??o das credenciais selecionadas?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmGerenciarCredenciais').action='<?=$strLinkCassar?>';
    document.getElementById('frmGerenciarCredenciais').submit();
  }
}
<? } ?>



function OnSubmitForm() {
	return true;
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmGerenciarCredenciais" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
<?
	//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
	PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
	//PaginaSEI::getInstance()->montarAreaValidacao();
?>	
  <div id="divUsuarios" class="infraAreaDados" style="height:5em;">
	 	<label id="lblUsuario" for="selUsuario" class="infraLabelOpcional">Conceder Credencial para:</label>
	  <input type="text" id="txtUsuario" name="txtUsuario" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
	  <input type="hidden" id="hdnIdUsuario" name="hdnIdUsuario" class="infraText" value="" />
	  
	 	<label id="lblUnidade" for="selUnidade" class="infraLabelOpcional">Unidade:</label>
	  <select id="selUnidade" name="selUnidade" class="infraSelect" onchange="escolheuUnidade();" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
	  </select>
	  
	  <button type="button" name="btnConceder" id="btnConceder" onclick="conceder();" accesskey="C" value="Conceder" class="infraButton"><span class="infraTeclaAtalho">C</span>onceder</button>
  </div>
<?	
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
	PaginaSEI::getInstance()->montarAreaDebug();
	PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
?>
  
  <input type="hidden" id="hdnIdAtividades" name="hdnIdAtividades" value="<?=$arrNumIdAtividades;?>" />
  
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>