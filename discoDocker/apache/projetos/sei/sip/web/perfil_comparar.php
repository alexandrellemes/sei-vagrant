<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 19/07/2017 - criado por fbv@trf4.jus.br
*
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
  
  //SessaoSip::getInstance()->validarSessao();
  SessaoSip::getInstance()->validarLink();

  SessaoSip::getInstance()->validarPermissao($_GET['acao']);
	
  PaginaSip::getInstance()->salvarCamposPost(array('selOrgaoOrigem','selSistemaOrigem','selPerfilOrigem',
                                                   'selOrgaoDestino','selSistemaDestino','selPerfilDestino',
                                                   'txtOrgaoDestino','txtSistemaDestino','txtPerfilDestino'));

  $objCompararPerfilDTO = new CompararPerfilDTO();
  $arrObjRecursoLocalDTO = array();
  $arrObjRecursoRemotoDTO = array();
  
  $arrComandos = array();
  
  switch($_GET['acao']){
    case 'perfil_comparar':
      $strTitulo = 'Comparar Perfil';
      $arrComandos[] = '<input type="submit" id="btnCompararPerfil" name="btnCompararPerfil" value="Comparar" class="infraButton" />';

      //dados do sistema origem
      $numIdOrgaoOrigem = PaginaSip::getInstance()->recuperarCampo('selOrgaoOrigem');
			$objCompararPerfilDTO->setNumIdOrgaoSistemaOrigem($numIdOrgaoOrigem);

      $numIdSistemaOrigem = PaginaSip::getInstance()->recuperarCampo('selSistemaOrigem');
			$objCompararPerfilDTO->setNumIdSistemaOrigem($numIdSistemaOrigem);

      $numIdPerfilOrigem = PaginaSip::getInstance()->recuperarCampo('selPerfilOrigem');
			$objCompararPerfilDTO->setNumIdPerfilOrigem($numIdPerfilOrigem);

      $numIdMenuOrigem = $_POST['selMenuOrigem'];//PaginaSip::getInstance()->recuperarCampo('selMenuOrigem');

      if ($_POST['rdoBase']==''){
        $_POST['rdoBase'] = 'L';
      }
      $rdoBaseComparacao = $_POST['rdoBase'];
      $objCompararPerfilDTO->setStrStaBaseComparacao($rdoBaseComparacao);

      //dados do sistema destino, qdo a base de compara?ao ? local
      $numIdOrgaoDestino = PaginaSip::getInstance()->recuperarCampo('selOrgaoDestino');
			$objCompararPerfilDTO->setNumIdOrgaoSistemaDestino($numIdOrgaoDestino);

      $numIdSistemaDestino = PaginaSip::getInstance()->recuperarCampo('selSistemaDestino');
			$objCompararPerfilDTO->setNumIdSistemaDestino($numIdSistemaDestino);

      $numIdPerfilDestino = PaginaSip::getInstance()->recuperarCampo('selPerfilDestino');
			$objCompararPerfilDTO->setNumIdPerfilDestino($numIdPerfilDestino);

      //dados do sistema destino, qdo a base de compara?ao ? remota
			//preenche com valores da infraParametro, caso existentes [os mesmos serao sobrescritos com os valores do $_POST]
			$objInfraParametro = new InfraParametro(BancoSip::getInstance());

			$strBancoServidor = $_POST['txtBancoServidor'];
			if(empty($strBancoServidor)) {
        $strBancoServidor = $objInfraParametro->getValor('SIP_PERFIL_COMPARAR_BANCO_SERVIDOR', false);
      }
			$strBancoPorta = $_POST['txtBancoPorta'];
			if(empty($strBancoPorta)) {
        $strBancoPorta = $objInfraParametro->getValor('SIP_PERFIL_COMPARAR_BANCO_PORTA', false);
      }
			$strBancoNome = $_POST['txtBancoNome'];
			if(empty($strBancoNome)) {
        $strBancoNome = $objInfraParametro->getValor('SIP_PERFIL_COMPARAR_BANCO_NOME', false);
      }
			$strBancoUsuario = $_POST['txtBancoUsuario'];
			if(empty($strBancoUsuario)) {
        $strBancoUsuario = $objInfraParametro->getValor('SIP_PERFIL_COMPARAR_BANCO_USUARIO', false);
      }
			$strBancoSenha = $_POST['pwdBancoSenha'];
			if(empty($strBancoSenha)) {
        $strBancoSenha = $objInfraParametro->getValor('SIP_PERFIL_COMPARAR_BANCO_SENHA', false);
      }
			$strStaTipoBanco = $_POST['selTipoBanco'];
			if(empty($strStaTipoBanco)) {
        $strStaTipoBanco = $objInfraParametro->getValor('SIP_PERFIL_COMPARAR_BANCO_TIPO', false);
      }

      //dados do banco destino
			$objCompararPerfilDTO->setStrBancoServidor($strBancoServidor);
		  $objCompararPerfilDTO->setStrBancoPorta($strBancoPorta);
		  $objCompararPerfilDTO->setStrBancoNome($strBancoNome);
		  $objCompararPerfilDTO->setStrBancoUsuario($strBancoUsuario);
		  $objCompararPerfilDTO->setStrBancoSenha($strBancoSenha);
      $objCompararPerfilDTO->setStrStaTipoBanco($strStaTipoBanco);

      //dados do sistema destino
      $strOrgaoDestino = PaginaSip::getInstance()->recuperarCampo('txtOrgaoDestino');
			$objCompararPerfilDTO->setStrSiglaOrgaoSistemaDestino($strOrgaoDestino);

      $strSistemaDestino = PaginaSip::getInstance()->recuperarCampo('txtSistemaDestino');
			$objCompararPerfilDTO->setStrSiglaSistemaDestino($strSistemaDestino);

      $strPerfilDestino = PaginaSip::getInstance()->recuperarCampo('txtPerfilDestino');
			$objCompararPerfilDTO->setStrPerfilDestino($strPerfilDestino);

      $objCompararPerfilDTO->setStrSinSomenteDiferencas(PaginaSip::getInstance()->getCheckbox($_POST['chkSomenteDiferencas']));

      if (isset($_POST['btnCompararPerfil'])) {
        try{
          //dados do banco destino
          $objCompararPerfilDTO->setStrBancoServidor($_POST['txtBancoServidor']);
          $objCompararPerfilDTO->setStrBancoPorta($_POST['txtBancoPorta']);
          $objCompararPerfilDTO->setStrBancoNome($_POST['txtBancoNome']);
          $objCompararPerfilDTO->setStrBancoUsuario($_POST['txtBancoUsuario']);
          $objCompararPerfilDTO->setStrBancoSenha($_POST['pwdBancoSenha']);
          $objCompararPerfilDTO->setStrStaTipoBanco($_POST['selTipoBanco']);

          $objPerfilRN = new PerfilRN();
          $objCompararPerfilDTO = $objPerfilRN->compararPerfil($objCompararPerfilDTO);
//          echo '<pre>';print_r($objCompararPerfilDTO);die;

          $arrObjRecursoLocalDTO = $objCompararPerfilDTO->getArrObjRecursoOrigemDTO();
          $arrObjRecursoRemotoDTO = $objCompararPerfilDTO->getArrObjRecursoDestinoDTO();

          $arrComandos[] = '<input type="submit" id="btnImportarRecursosMenus" name="btnImportarRecursosMenus" value="Importar" class="infraButton" />';
        }catch(Exception $e){
          PaginaSip::getInstance()->processarExcecao($e);
        }
			}
			if (isset($_POST['btnImportarRecursosMenus'])) {
//        echo '<pre>';print_r($_POST);die;
        $arrRecursosMenus = array();
        foreach ($_POST as $chave => $valor){
          if(substr($chave, 0, 10) == 'chkRecurso'){
            foreach ($_POST as $chave2 => $valor2){
              if((substr($chave2, 0, 7) == 'chkMenu')&&(substr($valor2, 0, strlen($valor)) == $valor)){
//                echo '<pre>';print_r($chave);print_r($valor);print_r($chave2);print_r($valor2);die;
                $arrRecursosMenus[$valor][] = substr($valor2, (strlen($valor)+1));
              }
            }
            if(!array_key_exists($valor, $arrRecursosMenus)){
              $arrRecursosMenus[$valor] = array();
            }
          }
        }
//        echo '<pre>';print_r($arrRecursosMenus);die;

        $objImportarRecursosDTO = new ImportarRecursosDTO();
        $objImportarRecursosDTO->setNumIdSistema($numIdSistemaOrigem);
        $objImportarRecursosDTO->setNumIdPerfil($numIdPerfilOrigem);
        $objImportarRecursosDTO->setNumIdMenu($numIdMenuOrigem);
        $objImportarRecursosDTO->setArrRecursosMenus($arrRecursosMenus);

        $objPerfilRN = new PerfilRN();
        $objPerfilRN->importarPerfil($objImportarRecursosDTO);

				PaginaSip::getInstance()->adicionarMensagem('Opera??o realizada com sucesso.');
      }
      break;

    default:
      throw new InfraException("A??o '".$_GET['acao']."' n?o reconhecida.");
  }

  $arrComandos[] = '<input type="button" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSip::getInstance()->assinarLink('controlador.php?acao='.PaginaSip::getInstance()->getAcaoRetorno().'&acao_origem=perfil_comparar').'\';" class="infraButton" />';

  //indexa DTOs pelo nome do recurso
  $arrObjRecursosPerfilLocal = InfraArray::indexarArrInfraDTO($arrObjRecursoLocalDTO,'Nome');
  $arrObjRecursosPerfilRemoto = InfraArray::indexarArrInfraDTO($arrObjRecursoRemotoDTO,'Nome');
//  echo '<pre>';print_r($arrObjRecursosPerfilLocal);print_r($arrObjRecursosPerfilRemoto);die('#');

  //a diferen?a consiste nos recursos apenas em A e em B, ou seja, [(A - interse?ao) U (B - interse?ao)]
  //somado aos recursos da interse?ao onde os menus forem diferentes
  $arrIntersecao = array_intersect(array_keys($arrObjRecursosPerfilLocal),array_keys($arrObjRecursosPerfilRemoto));
  sort($arrIntersecao);
  $arrDiffLocalItersecao = array_diff(array_keys($arrObjRecursosPerfilLocal), $arrIntersecao);
  sort($arrDiffLocalItersecao);
  $arrDiffRemotoItersecao = array_diff(array_keys($arrObjRecursosPerfilRemoto), $arrIntersecao);
  sort($arrDiffRemotoItersecao);
  $arrMergeForaIntersecao = array_merge($arrDiffLocalItersecao,$arrDiffRemotoItersecao);
  sort($arrMergeForaIntersecao);
  $arrMergeUnique = array_merge($arrMergeForaIntersecao,$arrIntersecao);
  sort($arrMergeUnique);
//  echo '<pre>';print_r($arrIntersecao);print_r($arrMergeForaIntersecao);die;
//  echo '<pre>';print_r($arrMergeUnique);print_r($arrMergeForaIntersecao);print_r($arrIntersecao);die('@');

  $numRegistros = InfraArray::contar($arrMergeUnique);

  $numIndiceCheckRecurso = 0;
  $numIndiceCheckMenu = 0;

  if ($numRegistros > 0) {

    $strResultado = '';
    $strResultado .= '<table width="99%" class="infraTable" summary="Tabela de Recursos">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSip::getInstance()->gerarCaptionTabela('Recursos', $numRegistros).'</caption>';

    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" colspan="2" valign="center" width="50%"><div style="padding:.2em;">Origem</div></th>';
    $strResultado .= '<th class="infraTh" colspan="2" valign="center" width="50%"><div style="padding:.2em;">Destino</div></th>';
    $strResultado .= '</tr>'."\n";

    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" width="25%"><div style="padding:.2em;text-align:left;">Recurso</div></th>';
    $strResultado .= '<th class="infraTh"><div style="padding:.2em;text-align:left;">Menu</div></th>';

    $strResultado .= '<th class="infraTh" width="25%"><div style="padding:.2em;text-align:left;"><a onclick="selecaoMultiplaRecursos();" tabindex="'.PaginaSip::getInstance()->getProxTabDados().'">&nbsp;<img src="imagens/check.gif" id="imgInfraCheckRecursos" title="Selecionar Tudo" alt="Selecionar Tudo" class="infraImg"></a> Recurso</div></th>';
    $strResultado .= '<th class="infraTh"><div style="padding:.2em;text-align:left;"><a onclick="selecaoMultiplaMenus();" tabindex="'.PaginaSip::getInstance()->getProxTabDados().'">&nbsp;<img src="imagens/check.gif" id="imgInfraCheckMenus" title="Selecionar Tudo" alt="Selecionar Tudo" class="infraImg"></a> Menu</div></th>';
    $strResultado .= '</tr>'."\n";

    $arrRecursosPerfilLocal = array_keys($arrObjRecursosPerfilLocal);
    $arrRecursosPerfilRemoto = array_keys($arrObjRecursosPerfilRemoto);

    $strTrClass = '';
    for($i=0;$i<$numRegistros;$i++){
      if ($strTrClass == '<tr class="infraTrClara">') {
        $strTrClass = '<tr class="infraTrEscura">';
      } else {
        $strTrClass = '<tr class="infraTrClara">';
      }
      $itemRecurso = array_shift($arrMergeUnique);

      $bolAmbos = false;
      if (in_array($itemRecurso, $arrIntersecao)) {//se recurso existe em ambos os perfis
        $bolAmbos = true;
      }

      if($bolAmbos){//recurso de ambos os perfis
        //monta comparativo do menu
        $strResultado .= $strTrClass;
        $strResultado .= '<td valign="center" align="left">'.$itemRecurso.'</td>';

        //monta menus recurso perfil local
        $strMenusLocal = '';
        if(!empty($arrObjRecursosPerfilLocal[$itemRecurso])){
          $arrMenusLocal = $arrObjRecursosPerfilLocal[$itemRecurso]->getArrObjItemMenuDTO();
          $novaLinha = '';
          for ($j = 0; $j < InfraArray::contar($arrMenusLocal); $j++) {
            if($arrMenusLocal[$j]->getStrSinPerfil() == 'S') {
              $strMenusLocal .= $novaLinha . $arrMenusLocal[$j]->getStrRamificacao();
              $novaLinha = '<br />';
            }
          }
        }
        $strResultado .= '<td valign="center" align="left">'.$strMenusLocal.'</td>';

        //monta menus recurso perfil remoto
        $strMenusRemoto = '';
        if(!empty($arrObjRecursosPerfilRemoto[$itemRecurso])){
          $arrMenusRemoto = $arrObjRecursosPerfilRemoto[$itemRecurso]->getArrObjItemMenuDTO();
          $novaLinha = '';
          for ($j = 0; $j < InfraArray::contar($arrMenusRemoto); $j++) {
            if($arrMenusRemoto[$j]->getStrSinPerfil() == 'S') {
              $strCheckMenus = '';
              if($objCompararPerfilDTO->getStrSinSomenteDiferencas()=='S'){
                $strCheckMenus .= '<input type="checkbox" id="chkMenu_'.$numIndiceCheckMenu.'" name="chkMenu_'.$numIndiceCheckMenu.'" class="infraCheckbox" value="'.$itemRecurso.';'.$arrMenusRemoto[$j]->getStrRamificacao().'" title="'.$itemRecurso.'" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'" />';
                $numIndiceCheckMenu++;
              }
              $strMenusRemoto .= $novaLinha . $strCheckMenus.' '. $arrMenusRemoto[$j]->getStrRamificacao();
              $novaLinha = '<br />';
            }
          }
        }

        $strCheckbox = '';
        if($objCompararPerfilDTO->getStrSinSomenteDiferencas()=='S'){
          $strCheckbox = '<input type="checkbox" id="chkRecurso_'.$numIndiceCheckRecurso.'" name="chkRecurso_'.$numIndiceCheckRecurso.'" checked onclick="selecionarMenu(this);" class="infraCheckbox" value="'.$itemRecurso.'" title="'.$itemRecurso.'" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'" style="display: none;" />';
          $numIndiceCheckRecurso++;
        }

        $strResultado .= '<td valign="center" align="left">'.$strCheckbox.' '.$itemRecurso.'</td>';
        $strResultado .= '<td valign="center" align="left">'.$strMenusRemoto.'</td>';
      }else{
        if(in_array($itemRecurso, $arrRecursosPerfilLocal)){
          $strResultado .= $strTrClass;

          //recurso do perfil origem
          $strResultado .= '<td valign="center" align="left">'.$itemRecurso.'</td>';

          //monta menus recurso perfil local
          $strMenusLocal = '';
          if(!empty($arrObjRecursosPerfilLocal[$itemRecurso])){
            $arrMenusLocal = $arrObjRecursosPerfilLocal[$itemRecurso]->getArrObjItemMenuDTO();
            $novaLinha = '';
            for ($j = 0; $j < InfraArray::contar($arrMenusLocal); $j++) {
              if($arrMenusLocal[$j]->getStrSinPerfil() == 'S') {
                $strMenusLocal .= $novaLinha . $arrMenusLocal[$j]->getStrRamificacao();
                $novaLinha = '<br />';
              }
            }
          }
          $strResultado .= '<td valign="center" align="left">'.$strMenusLocal.'</td>';

          $strResultado .= '<td valign="center" align="left"></td>';
          $strResultado .= '<td valign="center" align="left"></td>';

        }else if(in_array($itemRecurso, $arrRecursosPerfilRemoto)){

          $strResultado .= $strTrClass;
          //recurso do perfil destino
          $strResultado .= '<td valign="center" align="left"></td>';
          $strResultado .= '<td valign="center" align="left"></td>';

          //monta menus recurso perfil remoto
          $strMenusRemoto = '';
          if(!empty($arrObjRecursosPerfilRemoto[$itemRecurso])){
            $arrMenusRemoto = $arrObjRecursosPerfilRemoto[$itemRecurso]->getArrObjItemMenuDTO();
            $novaLinha = '';
            for ($j = 0; $j < InfraArray::contar($arrMenusRemoto); $j++) {
              if($arrMenusRemoto[$j]->getStrSinPerfil() == 'S') {
                $strCheckMenus = '';
                if($objCompararPerfilDTO->getStrSinSomenteDiferencas()=='S'){
                  $strCheckMenus .= '<input type="checkbox" id="chkMenu_'.$numIndiceCheckMenu.'" name="chkMenu_'.$numIndiceCheckMenu.'" class="infraCheckbox" value="'.$itemRecurso.';'.$arrMenusRemoto[$j]->getStrRamificacao().'" title="'.$itemRecurso.'" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'" disabled />';
                  $numIndiceCheckMenu++;
                }
                $strMenusRemoto .= $novaLinha . $strCheckMenus.' '. $arrMenusRemoto[$j]->getStrRamificacao();
                $novaLinha = '<br />';
              }
            }
          }

          $strCheckbox = '';
          if($objCompararPerfilDTO->getStrSinSomenteDiferencas()=='S'){
            $strCheckbox = '<input type="checkbox" id="chkRecurso_'.$numIndiceCheckRecurso.'" name="chkRecurso_'.$numIndiceCheckRecurso.'" onclick="selecionarMenu(this);" class="infraCheckbox" value="'.$itemRecurso.'" title="'.$itemRecurso.'" tabindex="'.PaginaSip::getInstance()->getProxTabTabela().'" />';
            $numIndiceCheckRecurso++;
          }

          $strResultado .= '<td valign="center" align="left">'.$strCheckbox.' '.$itemRecurso.'</td>';
          $strResultado .= '<td valign="center" align="left">'.$strMenusRemoto.'</td>';

        }
      }

      $strResultado .= '</tr>'."\n";
    }
  }
  $strResultado .= '</table>';
  
  //dados origem
	$strItensSelOrgaosOrigem = OrgaoINT::montarSelectSiglaAdministrados('null','&nbsp;', $numIdOrgaoOrigem);
  $strItensSelSistemasOrigem = SistemaINT::montarSelectSiglaAdministrados('null','&nbsp;', $numIdSistemaOrigem, $numIdOrgaoOrigem);
  $strItensSelPerfisOrigem = PerfilINT::montarSelectSiglaAutorizados('null','&nbsp;', $numIdPerfilOrigem, $numIdSistemaOrigem);
  $strItensSelMenusOrigem = MenuINT::montarSelectNome('null','&nbsp;', $numIdMenuOrigem, $numIdSistemaOrigem);

  //dados destino para compara?ao com base local
	$strItensSelOrgaosDestino = OrgaoINT::montarSelectSiglaAdministrados('null','&nbsp;', $numIdOrgaoDestino);
  $strItensSelSistemasDestino = SistemaINT::montarSelectSiglaAdministrados('null','&nbsp;', $numIdSistemaDestino, $numIdOrgaoDestino);
  $strItensSelPerfisDestino = PerfilINT::montarSelectSiglaAutorizados('null','&nbsp;', $numIdPerfilDestino, $numIdSistemaDestino);

  $strItensSelTipoBanco = SistemaINT::montarSelectTipoBanco('null','',$objCompararPerfilDTO->getStrStaTipoBanco());

}catch(Exception $e){
  PaginaSip::getInstance()->processarExcecao($e);
}

PaginaSip::getInstance()->montarDocType();
PaginaSip::getInstance()->abrirHtml();
PaginaSip::getInstance()->abrirHead();
PaginaSip::getInstance()->montarMeta();
PaginaSip::getInstance()->montarTitle(PaginaSip::getInstance()->getStrNomeSistema().' - Comparar Perfil');
PaginaSip::getInstance()->montarStyle();
PaginaSip::getInstance()->abrirStyle();
?>
<?if(0){?><style><?}?>

#lblOrgaoSistemaOrigem {position:absolute;left:0%;top:0%;width:25%;}
#selOrgaoOrigem {position:absolute;left:0%;top:5%;width:25%;}

#lblSistemaOrigem {position:absolute;left:0%;top:10%;width:25%;}
#selSistemaOrigem {position:absolute;left:0%;top:15%;width:25%;}

#lblPerfilOrigem {position:absolute;left:0%;top:20%;width:25%;}
#selPerfilOrigem {position:absolute;left:0%;top:25%;width:25%;}

#lblMenuOrigem {position:absolute;left:0%;top:30%;width:25%;}
#selMenuOrigem {position:absolute;left:0%;top:35%;width:25%;}

#lblBaseComparacao {position:absolute;left:0%;top:45%;width:25%;}
#divBaseComparacao {position:absolute;left:0%;top:50%;}

#lblOrgaoSistemaDestino {position:absolute;left:0%;top:60%;width:25%;}
#txtOrgaoDestino {position:absolute;left:0%;top:65%;width:25%;}
#selOrgaoDestino {position:absolute;left:0%;top:65%;width:25%;}

#lblSistemaDestino {position:absolute;left:0%;top:70%;width:25%;}
#txtSistemaDestino {position:absolute;left:0%;top:75%;width:25%;}
#selSistemaDestino {position:absolute;left:0%;top:75%;width:25%;}

#lblPerfilDestino {position:absolute;left:0%;top:80%;width:25%;}
#txtPerfilDestino {position:absolute;left:0%;top:85%;width:25%;}
#selPerfilDestino {position:absolute;left:0%;top:85%;width:25%;}

#divSomenteDiferencas {position:absolute;left:0%;top:92%;}

#fldBancoDestino {position:absolute;left:35%;top:0%;height:90%;width:30%;}

#lblBancoServidor {position:absolute;left:10%;top:6%;width:70%;}
#txtBancoServidor {position:absolute;left:10%;top:12%;width:70%;}

#lblBancoPorta {position:absolute;left:10%;top:21%;width:70%;}
#txtBancoPorta {position:absolute;left:10%;top:27%;width:70%;}

#lblBancoNome {position:absolute;left:10%;top:36%;width:70%;}
#txtBancoNome {position:absolute;left:10%;top:42%;width:70%;}

#lblBancoUsuario {position:absolute;left:10%;top:51%;width:70%;}
#txtBancoUsuario {position:absolute;left:10%;top:57%;width:70%;}

#lblBancoSenha {position:absolute;left:10%;top:66%;width:70%;}
#pwdBancoSenha {position:absolute;left:10%;top:72%;width:70%;}

#lblTipoBanco {position:absolute;left:10%;top:81%;width:70%;}
#selTipoBanco {position:absolute;left:10%;top:87%;width:70%;}

tr.trAmarela{
  background-color:yellow;
}

<?if(0){?></style><?}?>
<?
PaginaSip::getInstance()->fecharStyle();
PaginaSip::getInstance()->montarJavaScript();
PaginaSip::getInstance()->abrirJavaScript();
?>
<?if(0){?><script type="text/javascript"><?}?>

function inicializar(){
  if(document.getElementById('optBaseLocal').checked) {
    processarBaseComparacao(document.getElementById('optBaseLocal'));
  } else if (document.getElementById('optBaseRemota').checked) {
    processarBaseComparacao(document.getElementById('optBaseRemota'));
  }
  //infraEfeitoTabelas();
}

function processarBaseComparacao(obj){
  //alert(obj.value);
  if(obj.value == 'L'){//base (L)ocal
    document.getElementById('txtOrgaoDestino').style.display = 'none';
    document.getElementById('selOrgaoDestino').style.display = '';
    document.getElementById('txtSistemaDestino').style.display = 'none';
    document.getElementById('selSistemaDestino').style.display = '';
    document.getElementById('txtPerfilDestino').style.display = 'none';
    document.getElementById('selPerfilDestino').style.display = '';
    document.getElementById('fldBancoDestino').style.display = 'none';
  }else{//base (R)emota
    document.getElementById('txtOrgaoDestino').style.display = '';
    document.getElementById('selOrgaoDestino').style.display = 'none';
    document.getElementById('txtSistemaDestino').style.display = '';
    document.getElementById('selSistemaDestino').style.display = 'none';
    document.getElementById('txtPerfilDestino').style.display = '';
    document.getElementById('selPerfilDestino').style.display = 'none';
    document.getElementById('fldBancoDestino').style.display = '';
  }
}

function trocarOrgaoSistemaOrigem(obj){
	document.getElementById('selSistemaOrigem').value='null';
	trocarSistemaOrigem(obj);
}

function trocarSistemaOrigem(obj){
	document.getElementById('selPerfilOrigem').value='null';
	obj.form.submit();
}

function trocarOrgaoSistemaDestino(obj){
	document.getElementById('selSistemaDestino').value='null';
	trocarSistemaDestino(obj);
}

function trocarSistemaDestino(obj){
	document.getElementById('selPerfilDestino').value='null';
	obj.form.submit();
}

function selecionarMenu(obj){
	for (m=0; m < <?=$numIndiceCheckMenu?>; m++) {
	  boxMenu = document.getElementById('chkMenu_'+m);
	  if (boxMenu.title==obj.title){
			if (obj.checked==false){
					boxMenu.checked = false;
					boxMenu.disabled = true;
			}else{
					boxMenu.disabled = false;
			}
			//break;
		}
	}

}

function selecaoMultiplaRecursos() {
  infraCheckRecursos = document.getElementById('imgInfraCheckRecursos');

  for (i=0; i < <?=$numIndiceCheckRecurso?>; i++) {
    boxRecurso = document.getElementById('chkRecurso_'+i);
		if (!boxRecurso.disabled){
			if (infraCheckRecursos.title == 'Selecionar Tudo') {
				boxRecurso.checked = true;
			} else {
				boxRecurso.checked = false;
			}
		}
		selecionarMenu(boxRecurso);
  }
  if (infraCheckRecursos.title == 'Selecionar Tudo') {
    infraCheckRecursos.title = 'Remover Sele??o';
    infraCheckRecursos.alt = 'Remover Sele??o';
  }
  else {
    infraCheckRecursos.title = 'Selecionar Tudo';
    infraCheckRecursos.alt = 'Selecionar Tudo';
  }

}


function selecaoMultiplaMenus() {
  infraCheckMenus = document.getElementById('imgInfraCheckMenus');

  for (i=0; i < <?=$numIndiceCheckMenu?>; i++) {
    boxMenu = document.getElementById('chkMenu_'+i);
		if (!boxMenu.disabled){
			if (infraCheckMenus.title == 'Selecionar Tudo') {
				boxMenu.checked = true;
			} else {
				boxMenu.checked = false;
			}
		}
  }
  if (infraCheckMenus.title == 'Selecionar Tudo') {
    infraCheckMenus.title = 'Remover Sele??o';
    infraCheckMenus.alt = 'Remover Sele??o';
  }
  else {
    infraCheckMenus.title = 'Selecionar Tudo';
    infraCheckMenus.alt = 'Selecionar Tudo';
  }

}

function validarForm(){
  if (!infraSelectSelecionado(document.getElementById('selOrgaoOrigem'))) {
    alert('Selecione o ?rg?o do Sistema Origem.');
    document.getElementById('selOrgaoOrigem').focus();
    return false;
  }
	
  if (!infraSelectSelecionado(document.getElementById('selSistemaOrigem'))) {
    alert('Selecione o Sistema Origem.');
    document.getElementById('selSistemaOrigem').focus();
    return false;
  }

  if (!infraSelectSelecionado(document.getElementById('selPerfilOrigem'))) {
    alert('Selecione o Perfil Origem.');
    document.getElementById('selPerfilOrigem').focus();
    return false;
  }

  if ((document.getElementById('selMenuOrigem').options.length>0)&&(!infraSelectSelecionado(document.getElementById('selMenuOrigem')))) {
    alert('Selecione o Menu Origem.');
    document.getElementById('selMenuOrigem').focus();
    return false;
  }

  if (document.getElementById('optBaseLocal').checked) {//base (L)ocal
    if (!infraSelectSelecionado(document.getElementById('selOrgaoDestino'))) {
      alert('Selecione o ?rg?o do Sistema Destino.');
      document.getElementById('selOrgaoDestino').focus();
      return false;
    }

    if (!infraSelectSelecionado(document.getElementById('selSistemaDestino'))) {
      alert('Selecione o Sistema Destino.');
      document.getElementById('selSistemaDestino').focus();
      return false;
    }

    if (!infraSelectSelecionado(document.getElementById('selPerfilDestino'))) {
      alert('Selecione o Perfil Destino.');
      document.getElementById('selPerfilDestino').focus();
      return false;
    }
  } else {//base (R)emota
    if (infraTrim(document.getElementById('txtBancoServidor').value)=='') {
      alert('Informe o Servidor do Banco de Dados Remoto.');
      document.getElementById('txtBancoServidor').focus();
      return false;
    }

    if (infraTrim(document.getElementById('txtBancoPorta').value)=='') {
      alert('Informe a Porta do Banco de Dados Remoto.');
      document.getElementById('txtBancoPorta').focus();
      return false;
    }

    if (infraTrim(document.getElementById('txtBancoNome').value)=='') {
      alert('Informe o Nome do Banco de Dados Remoto.');
      document.getElementById('txtBancoNome').focus();
      return false;
    }

    if (infraTrim(document.getElementById('txtBancoUsuario').value)=='') {
      alert('Informe o Usu?rio do Banco de Dados Remoto.');
      document.getElementById('txtBancoUsuario').focus();
      return false;
    }

  if (infraTrim(document.getElementById('pwdBancoSenha').value)=='') {
    alert('Informe a Senha do Banco de Dados Remoto.');
    document.getElementById('pwdBancoSenha').focus();
    return false;
  }

    if (!infraSelectSelecionado(document.getElementById('selTipoBanco'))) {
      alert('Selecione o Tipo do Banco de Dados Remoto.');
      document.getElementById('selTipoBanco').focus();
      return false;
    }

    if (infraTrim(document.getElementById('txtOrgaoDestino').value)=='') {
      alert('Informe o ?rg?o do Sistema Destino.');
      document.getElementById('txtOrgaoDestino').focus();
      return false;
    }

    if (infraTrim(document.getElementById('txtSistemaDestino').value)=='') {
      alert('Informe o Sistema Destino.');
      document.getElementById('txtSistemaDestino').focus();
      return false;
    }

    if (infraTrim(document.getElementById('txtPerfilDestino').value)=='') {
      alert('Informe o Perfil Destino.');
      document.getElementById('txtPerfilDestino').focus();
      return false;
    }
  }

  return true;
}

function OnSubmitForm() {
  if (!validarForm()){
    return false;
  }

  return true;
}

<?if(0){?></script><?}?>
<?
PaginaSip::getInstance()->fecharJavaScript();
PaginaSip::getInstance()->fecharHead();
PaginaSip::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmPerfilMontar" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSip::getInstance()->assinarLink('perfil_comparar.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
<?
//PaginaSip::getInstance()->montarBarraLocalizacao($strTitulo);
PaginaSip::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSip::getInstance()->montarAreaValidacao();
PaginaSip::getInstance()->abrirAreaDados('42em');
?>
  <label id="lblOrgaoSistemaOrigem" for="selOrgaoOrigem" accesskey="r" class="infraLabelObrigatorio">?<span class="infraTeclaAtalho">r</span>g?o do Sistema Origem:</label>
  <select id="selOrgaoOrigem" name="selOrgaoOrigem" onchange="trocarOrgaoSistemaOrigem(this);" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelOrgaosOrigem?>
  </select>

  <label id="lblSistemaOrigem" for="selSistemaOrigem" accesskey="S" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">S</span>istema Origem:</label>
  <select id="selSistemaOrigem" name="selSistemaOrigem" onchange="trocarSistemaOrigem(this);" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>">
  <?=$strItensSelSistemasOrigem?>
  </select>

  <label id="lblPerfilOrigem" for="selPerfilOrigem" accesskey="P" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">P</span>erfil Origem:</label>
  <select id="selPerfilOrigem" name="selPerfilOrigem" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>">
  <?=$strItensSelPerfisOrigem?>
  </select>

  <label id="lblMenuOrigem" for="selMenuOrigem" accesskey="M" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">M</span>enu Origem:</label>
  <select id="selMenuOrigem" name="selMenuOrigem" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>">
  <?=$strItensSelMenusOrigem?>
  </select>

  <label id="lblBaseComparacao" accesskey="" for="" class="infraLabelObrigatorio">Comparar com perfil de:</label>

  <div id="divBaseComparacao" class="infraDivRadio">
    <input type="radio" name="rdoBase" id="optBaseLocal" value="L" onclick="processarBaseComparacao(this);" class="infraRadio" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" <?=$rdoBaseComparacao=='L'?'checked=checked':''?> />
    <label id="lblBaseLocal" accesskey="" for="optBaseLocal" class="infraLabelCheckbox">Base Local</label>

    <input type="radio" name="rdoBase" id="optBaseRemota" value="R" onclick="processarBaseComparacao(this);" class="infraRadio" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" <?=$rdoBaseComparacao=='R'?'checked=checked':''?> />
    <label id="lblBaseRemota" accesskey="" for="optBaseRemota" class="infraLabelCheckbox">Base Remota</label>
  </div>

  <label id="lblOrgaoSistemaDestino" for="txtOrgaoDestino" accesskey="r" class="infraLabelObrigatorio">?<span class="infraTeclaAtalho">r</span>g?o do Sistema Destino:</label>
  <input type="text" id="txtOrgaoDestino" name="txtOrgaoDestino" style="display: none;" maxlength="15" class="infraText" value="<?=PaginaSip::tratarHTML($objCompararPerfilDTO->getStrSiglaOrgaoSistemaDestino());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />
  <select id="selOrgaoDestino" name="selOrgaoDestino" onchange="trocarOrgaoSistemaDestino(this);" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelOrgaosDestino?>
  </select>

  <label id="lblSistemaDestino" for="txtSistemaDestino" accesskey="S" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">S</span>istema Destino:</label>
  <input type="text" id="txtSistemaDestino" name="txtSistemaDestino" style="display: none;" class="infraText" value="<?=PaginaSip::tratarHTML($objCompararPerfilDTO->getStrSiglaSistemaDestino());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />
  <select id="selSistemaDestino" name="selSistemaDestino" onchange="trocarSistemaDestino(this);" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>">
  <?=$strItensSelSistemasDestino?>
  </select>

  <label id="lblPerfilDestino" for="txtPerfilDestino" accesskey="P" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">P</span>erfil Destino:</label>
  <input type="text" id="txtPerfilDestino" name="txtPerfilDestino" style="display: none;" class="infraText" value="<?=PaginaSip::tratarHTML($objCompararPerfilDTO->getStrPerfilDestino());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />
  <select id="selPerfilDestino" name="selPerfilDestino" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>">
  <?=$strItensSelPerfisDestino?>
  </select>

  <div id="divSomenteDiferencas" class="infraDivCheckbox">
    <input type="checkbox" id="chkSomenteDiferencas" name="chkSomenteDiferencas" <?=PaginaSip::getInstance()->setCheckbox($objCompararPerfilDTO->getStrSinSomenteDiferencas())?> class="infraCheckbox" />
    <label id="lblSomenteDiferencas" for="chkSomenteDiferencas" accesskey="t" class="infraLabelObrigatorio">Visualizar somen<span class="infraTeclaAtalho">t</span>e diferen?as</label>
  </div>


	<fieldset id="fldBancoDestino" style="display: none;">
	  <legend style="font-weight: bold;background-color:#e0e0e0;">&nbsp;Banco de Dados Remoto&nbsp;</legend>

		<label id="lblBancoServidor" for="txtBancoServidor" accesskey="S" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">S</span>ervidor:</label>
		<input type="text" id="txtBancoServidor" name="txtBancoServidor" class="infraText" value="<?=PaginaSip::tratarHTML($objCompararPerfilDTO->getStrBancoServidor());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

		<label id="lblBancoPorta" for="txtBancoPorta" accesskey="P" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">P</span>orta:</label>
		<input type="text" id="txtBancoPorta" name="txtBancoPorta" class="infraText" value="<?=PaginaSip::tratarHTML($objCompararPerfilDTO->getStrBancoPorta());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />
		
		<label id="lblBancoNome" for="txtBancoNome" accesskey="N" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">N</span>ome:</label>
		<input type="text" id="txtBancoNome" name="txtBancoNome" class="infraText" value="<?=PaginaSip::tratarHTML($objCompararPerfilDTO->getStrBancoNome());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

		<label id="lblBancoUsuario" for="txtBancoUsuario" accesskey="U" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">U</span>su?rio:</label>
		<input type="text" id="txtBancoUsuario" name="txtBancoUsuario" class="infraText" value="<?=PaginaSip::tratarHTML($objCompararPerfilDTO->getStrBancoUsuario());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />
		
		<label id="lblBancoSenha" for="pwdBancoSenha" accesskey="e" class="infraLabelObrigatorio">S<span class="infraTeclaAtalho">e</span>nha:</label>
		<input type="password" id="pwdBancoSenha" name="pwdBancoSenha" autocomplete="off" class="infraText" value="<?=PaginaSip::tratarHTML($objCompararPerfilDTO->getStrBancoSenha());?>" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>" />

    <label id="lblTipoBanco" for="selTipoBanco" accesskey="" class="infraLabelObrigatorio">Tipo:</label>
    <select id="selTipoBanco" name="selTipoBanco" class="infraSelect" tabindex="<?=PaginaSip::getInstance()->getProxTabDados()?>">
      <?=$strItensSelTipoBanco?>
    </select>

	</fieldset>

<?
  PaginaSip::getInstance()->fecharAreaDados();
	//echo $strResultado;
	PaginaSip::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  //PaginaSip::getInstance()->montarAreaDebug();
  PaginaSip::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSip::getInstance()->fecharBody();
PaginaSip::getInstance()->fecharHtml();
?>