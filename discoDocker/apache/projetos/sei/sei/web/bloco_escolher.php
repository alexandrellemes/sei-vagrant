<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 24/02/2011 - criado por mga
*
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

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  //PaginaSEI::getInstance()->salvarCamposPost(array(''));  
  
  $arrComandos = array();
  
  //Filtrar par�metros
  $strParametros = '';
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
  }
  
  if (isset($_GET['id_procedimento'])){
    $strParametros .= "&id_procedimento=".$_GET['id_procedimento'];
  } 

  
  if (isset($_GET['id_documento'])){
    $strParametros .= "&id_documento=".$_GET['id_documento'];
  } 
  

  switch($_GET['acao']){
    
    case 'bloco_escolher':
    	
   	  $strTitulo = 'Incluir em Bloco de Assinatura';  

      $numIdBloco = null;
      if (isset($_GET['id_bloco'])){
      	$numIdBloco = $_GET['id_bloco'];
      }else if (isset($_POST['selBloco'])){
      	$numIdBloco = $_POST['selBloco'];
      }

      $bolTrocouBloco = false;
      if (isset($_POST['selBloco']) && !isset($_POST['sbmIncluir'])){
        $_POST['hdnDocumentosItensSelecionados'] = '';
        $bolTrocouBloco = true;
      }
      
   	  $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
   	  
      //Monta tabela de documentos do processo
      $objProcedimentoDTO = new ProcedimentoDTO();
      $objProcedimentoDTO->retNumIdUnidadeGeradoraProtocolo();
      $objProcedimentoDTO->setDblIdProcedimento($_GET['id_procedimento']);
      $objProcedimentoDTO->setStrSinDocTodos('S');
        
      $objProcedimentoRN = new ProcedimentoRN();
      $arr = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);

			if(count($arr) == 0){
				throw new InfraException('Processo n�o encontrado.');
			}
			
			$objProcedimentoDTO = $arr[0];
      
			$objDocumentoRN = new DocumentoRN();
			$objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();
			
			$strThCheckDocumentos = PaginaSEI::getInstance()->getThCheck('','Documentos');
			
			$arrIdProtocolosBlocos = array();
			
			if ($numIdBloco!=null){
				$objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
				$objRelBlocoProtocoloDTO->retDblIdProtocolo();
				$objRelBlocoProtocoloDTO->setNumIdBloco($numIdBloco);
				
				$arrIdProtocolosBlocos = InfraArray::indexarArrInfraDTO($objRelBlocoProtocoloRN->listarRN1291($objRelBlocoProtocoloDTO),'IdProtocolo');
			}

			
			$numDocumentos = 0;
			
			if (count($objProcedimentoDTO->getArrObjDocumentoDTO())){
				
				$bolAcaoDocumentoVisualizar = SessaoSEI::getInstance()->verificarPermissao('documento_visualizar'); 
				//$bolAcaoRelBlocoProtocoloListar = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_listar');
				$bolAcaoBlocoAssinaturaListar = SessaoSEI::getInstance()->verificarPermissao('bloco_assinatura_listar');
				$bolAcaoRelBlocoProtocoloCadastrar = SessaoSEI::getInstance()->verificarPermissao('rel_bloco_protocolo_cadastrar');
				$bolAcaoBlocoAssinaturaCadastrar = SessaoSEI::getInstance()->verificarPermissao('bloco_assinatura_cadastrar');
				
				$objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
				$objRelBlocoProtocoloDTO->retDblIdProtocolo();
				$objRelBlocoProtocoloDTO->retNumIdBloco();
				$objRelBlocoProtocoloDTO->setNumIdUnidadeBloco(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
				$objRelBlocoProtocoloDTO->setDblIdProtocolo(InfraArray::converterArrInfraDTO($objProcedimentoDTO->getArrObjDocumentoDTO(),'IdDocumento'),InfraDTO::$OPER_IN);
				
				$arrBlocosProtocolos = InfraArray::indexarArrInfraDTO($objRelBlocoProtocoloRN->listarRN1291($objRelBlocoProtocoloDTO),'IdProtocolo',true);
			
				foreach($objProcedimentoDTO->getArrObjDocumentoDTO() as $objDocumentoDTO){
					
					//se n�o esta no bloco e � selecion�vel
					if($objDocumentoRN->verificarSelecaoBlocoAssinatura($objDocumentoDTO)){
					  
						$strResultadoDocumentos .= '<tr class="infraTrClara">';
						
						$strSinValor = 'N';
						if (($bolTrocouBloco || $_GET['acao_origem']!='bloco_escolher') && $_GET['id_documento']==$objDocumentoDTO->getDblIdDocumento()){
						  $strSinValor = 'S';
						}
	 
						$strResultadoDocumentos .= '<td align="center" valign="top" class="infraTd">';
						
						$strOpcoesCheck = '';
						if (isset($arrIdProtocolosBlocos[$objDocumentoDTO->getDblIdDocumento()])){  
						  $strSinValor = 'N';
						  $strOpcoesCheck = 'disabled="disabled" style="display:none;"';
						}
						
						$strResultadoDocumentos .= PaginaSEI::getInstance()->getTrCheck($numDocumentos++,$objDocumentoDTO->getDblIdDocumento(),$objDocumentoDTO->getStrProtocoloDocumentoFormatado(),$strSinValor,'Documentos',$strOpcoesCheck);
						
						$strResultadoDocumentos .= '</td>';
	
						$strResultadoDocumentos .= '<td  class="infraTd" align="center" valign="top">';
						
		        if ($bolAcaoDocumentoVisualizar){
		          $strResultadoDocumentos .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&id_documento='.$objDocumentoDTO->getDblIdDocumento()) .'" target="_blank" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" class="protocoloNormal" style="font-size:1em !important;">'.PaginaSEI::tratarHTML($objDocumentoDTO->getStrProtocoloDocumentoFormatado()).'</a>';
		        }else{
		          $strResultadoDocumentos .= PaginaSEI::tratarHTML($objDocumentoDTO->getStrProtocoloDocumentoFormatado());
		        }
						
						$strResultadoDocumentos .= '</td>';

						$strResultadoDocumentos .= '<td  class="infraTd" valign="top">';
						$strResultadoDocumentos .= PaginaSEI::tratarHTML($objDocumentoDTO->getStrNomeSerie().' '.$objDocumentoDTO->getStrNumero());
						$strResultadoDocumentos .= '</td>';

						$strResultadoDocumentos .= '<td  class="infraTd" align="center" valign="top">';
						$strResultadoDocumentos .= $objDocumentoDTO->getDtaGeracaoProtocolo();
						$strResultadoDocumentos .= '</td>';
						
						$strResultadoDocumentos .= '<td align="center" valign="top" class="infraTd">';
						if (isset($arrBlocosProtocolos[$objDocumentoDTO->getDblIdDocumento()])){
							$strSeparadorBloco = '';
							foreach($arrBlocosProtocolos[$objDocumentoDTO->getDblIdDocumento()] as $objRelBlocoProtocoloDTO){
								$strResultadoDocumentos .= $strSeparadorBloco;
								
								/*
								if ($bolAcaoRelBlocoProtocoloListar){
                  $strResultadoDocumentos .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=rel_bloco_protocolo_listar&id_bloco='.$objRelBlocoProtocoloDTO->getNumIdBloco().PaginaSEI::getInstance()->montarAncora($objDocumentoDTO->getDblIdDocumento().'-'.$objRelBlocoProtocoloDTO->getNumIdBloco()))) .'" target="_blank" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" class="linkFuncionalidade" style="font-size:1em !important;">'.$objRelBlocoProtocoloDTO->getNumIdBloco().'</a>';
								}else{
		              $strResultadoDocumentos .= $objRelBlocoProtocoloDTO->getNumIdBloco();
								}
								*/
								
								if ($bolAcaoBlocoAssinaturaListar){
                  $strResultadoDocumentos .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_assinatura_listar&id_bloco='.$objRelBlocoProtocoloDTO->getNumIdBloco().PaginaSEI::getInstance()->montarAncora($objRelBlocoProtocoloDTO->getNumIdBloco())) .'" target="_blank" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'" class="linkFuncionalidade" style="font-size:1em !important;">'.$objRelBlocoProtocoloDTO->getNumIdBloco().'</a>';
								}else{
		              $strResultadoDocumentos .= $objRelBlocoProtocoloDTO->getNumIdBloco();
								}
								
								
								$strSeparadorBloco = '<br />';
							}
						}else{
						  $strResultadoDocumentos .= '&nbsp;';
						}
						$strResultadoDocumentos .= '</td>';
						
	          $strResultadoDocumentos .= '</tr>';
								
					}
				}
				
				if ($numDocumentos){
		      $strResultadoDocumentos = '<table id="tblDocumentos" width="97%" class="infraTable" summary="Lista de documentos dispon�veis para inclus�o">
		 						  									<caption class="infraCaption" >'.PaginaSEI::getInstance()->gerarCaptionTabela("documentos dispon�veis para inclus�o",$numDocumentos).'</caption> 
								 										<tr>
								 										  <th class="infraTh" width="1%">'.$strThCheckDocumentos.'</th>
								 										  <th class="infraTh" width="15%">N� SEI</th>
								  										<th class="infraTh">Documento</th>
								  										<th class="infraTh" width="15%">Data</th>
								  										<th class="infraTh" width="15%">Blocos</th>
								  									</tr>'.
		                                $strResultadoDocumentos.
		                                '</table>';
				}				
			}
                                
			if ($bolAcaoRelBlocoProtocoloCadastrar){
        $strBotaoIncluir = '<button type="submit" name="sbmIncluir" id="sbmIncluir" accesskey="I" value="Incluir" class="infraButton"><span class="infraTeclaAtalho">I</span>ncluir</button>';
			}
			
			if ($bolAcaoBlocoAssinaturaCadastrar){
	      $strBotaoNovo = '<button type="button" accesskey="N" id="btnNovoAssinatura" value="Novo" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_assinatura_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].$strParametros).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
			}
			
	    //$arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&resultado=0'.$strParametros)).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
	    
			////////////////////////////////////////////////////////////////////////////////////////////////                                
                                
			if (isset($_POST['sbmIncluir'])){
        $arrIdDocumentos = PaginaSEI::getInstance()->getArrStrItensSelecionados('Documentos');   

        $arrObjRelBlocoProtocoloDTO = array();
        
        
        foreach($arrIdDocumentos as $dblIdDocumento){
        	$objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
        	$objRelBlocoProtocoloDTO->setNumIdBloco($numIdBloco);
        	$objRelBlocoProtocoloDTO->setDblIdProtocolo($dblIdDocumento);
        	$objRelBlocoProtocoloDTO->setStrAnotacao(null);
        	$arrObjRelBlocoProtocoloDTO[] = $objRelBlocoProtocoloDTO;
        }
        
      	try{
      		
      		$objRelBlocoProtocoloRN->cadastrarMultiplo($arrObjRelBlocoProtocoloDTO);

          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].'&id_bloco='.$numIdBloco.$strParametros.PaginaSEI::getInstance()->montarAncora($arrIdDocumentos)));
          die;
      		
      	}catch(Exception $e){
      		PaginaSEI::getInstance()->processarExcecao($e);
      	}
			}
      break;
     
    	default:
      throw new InfraException("A��o '".$_GET['acao']."' n�o reconhecida.");
  }
 
  $bolAcaoBlocoAssinaturaListar = SessaoSEI::getInstance()->verificarPermissao('bloco_assinatura_listar');
  
  if ($bolAcaoBlocoAssinaturaListar){
    $arrComandos[] = '<a id="ancIrBlocosAssinatura" href="javascript:void(0);" onclick="irBlocosAssinatura();" class="ancoraPadraoPreta">Ir para Blocos de Assinatura</a>';
    $strLinkBlocosAssinatura = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=bloco_assinatura_listar&acao_origem='.$_GET['acao']);
  }
  
  $strItensSelBloco = BlocoINT::montarSelectAssinatura('null','&nbsp;',$numIdBloco);
  
  
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

#lblBloco {position:absolute;left:0%;top:00%;}
#selBloco {position:absolute;left:0%;top:40%;width:75%;}

#sbmIncluir {position:absolute;left:76%;top:40%;width:10%;}
#btnNovoAssinatura {position:absolute;left:87%;top:40%;width:10%;}


<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  infraEfeitoTabelas();
  self.setTimeout('document.getElementById(\'selBloco\').focus()',500);
}

function OnSubmitForm() {
 
  if (!infraSelectSelecionado('selBloco')) {
    alert('Selecione um Bloco de Assinatura.');
    document.getElementById('selBloco').focus();
    return false;
  }
 
 if (document.getElementById('hdnDocumentosItensSelecionados').value==''){
    alert('Nenhum documento selecionado.');
    return false;
  }

  return true;  
}

<? if ($bolAcaoBlocoAssinaturaListar){ ?>
  function irBlocosAssinatura(){
    parent.parent.document.location.href = '<?=$strLinkBlocosAssinatura?>';
  }
<?}?>  

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmBlocoEscolher" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>" >
<?
  //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  //PaginaSEI::getInstance()->montarAreaValidacao();
  PaginaSEI::getInstance()->abrirAreaDados('5em');
?>
  <?=$strLinkIrParaBlocosAssinatura?>
  <label id="lblBloco" for="selBloco" accesskey="B" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">B</span>loco:</label>
  <select id="selBloco" name="selBloco" class="infraSelect" onchange="this.form.submit();" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
  <?=$strItensSelBloco?>
  </select>
  <?=$strBotaoIncluir?>
  <?=$strBotaoNovo?>
<?
  PaginaSEI::getInstance()->fecharAreaDados();
  if ($numDocumentos){
    PaginaSEI::getInstance()->montarAreaTabela($strResultadoDocumentos,$numDocumentos);
  }else{
  	if ($numIdBloco!=null){
  	  echo '<label>Nenhum documento dispon�vel para inclus�o neste bloco de assinatura.</label>';
  	}else{
  		echo '<label>Nenhum documento dispon�vel para inclus�o em bloco de assinatura.</label>';
  	}
  }
  //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
?>
</form>
<?
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>