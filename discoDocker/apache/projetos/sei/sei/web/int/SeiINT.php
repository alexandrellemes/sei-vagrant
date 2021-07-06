<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 30/05/2014 - criado por mga
*
* Vers�o do Gerador de C�digo: 1.12.0
*
* Vers�o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class SeiINT extends InfraINT {

  public static $MSG_ERRO_XSS = 'Documento possui conte�do n�o permitido';
  private static $NIVEL_VERIFICACAO_ROTINA = null;

  public static function validarHttps(){
    
    $bolHttps = ConfiguracaoSEI::getInstance()->getValor('SessaoSEI','https');
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on');
    
    if (($bolHttps && !$isHttps) || (!$bolHttps && $isHttps)){
      
      $strServer = ConfiguracaoSEI::getInstance()->getValor('SEI','URL');
    
      $posIni = strpos($strServer, '//');
      if ($posIni!==false){
        $strServer = substr($strServer, $posIni+2);
      }
    
      $posFim = strpos($strServer, '/');
      if ($posFim!==false){
        $strServer = substr($strServer, 0, $posFim);
      }
      
      header('Location: '.($bolHttps?'https':'http').'://'.$strServer.$_SERVER['REQUEST_URI']);
      die;
    }
  }
  
  public static function obterURL(){
    
    $strURL = ConfiguracaoSEI::getInstance()->getValor('SEI','URL');
    
    if (ConfiguracaoSEI::getInstance()->getValor('SessaoSEI','https')){
      $strURL = str_replace('http://','https://',$strURL);
    }else{
      $strURL = str_replace('https://','http://',$strURL);
    }
    return $strURL.'/';
  }

  public static function download($objAnexoDTO = null, $strCaminhoNomeArquivo = null, $strNomeArquivo = null, $strContentDisposition = 'inline', $bolExcluirAutomaticamente = false, $strIdentificacao = '', $dbIdDocumento = null, $bolValidacao = false){

    try {

      ini_set('memory_limit', '1024M');

      if ($objAnexoDTO!=null){

        $objAnexoRN = new AnexoRN();
        $strCaminhoNomeArquivo = $objAnexoRN->obterLocalizacao($objAnexoDTO);

        if ($strNomeArquivo==null) {
          $strNomeArquivo = $objAnexoDTO->getStrNome();
        }
      }

      $numTamanho = filesize($strCaminhoNomeArquivo);

      $binConteudo = null;

      if ($objAnexoDTO!=null){

        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $strVerificacaoHash = $objInfraParametro->getValor('SEI_HABILITAR_VERIFICACAO_REPOSITORIO', false);

        if ($strVerificacaoHash == '1') {
          if ($numTamanho > TAM_BLOCO_LEITURA_ARQUIVO) {

            if (md5_file($strCaminhoNomeArquivo) != $objAnexoDTO->getStrHash()) {
              throw new InfraException('Conte�do do arquivo corrompido.', null, $strCaminhoNomeArquivo);
            }

          } else {

            $fp = fopen($strCaminhoNomeArquivo, "rb");
            $binConteudo = fread($fp, TAM_BLOCO_LEITURA_ARQUIVO);
            fclose($fp);

            if (md5($binConteudo) != $objAnexoDTO->getStrHash()) {
              throw new InfraException('Conte�do do arquivo corrompido.', null, $strCaminhoNomeArquivo);
            }
          }
        }
      }

      $strMimeType = InfraUtil::getStrMimeType($strNomeArquivo);

      $strContentType = 'Content-Type: ' . $strMimeType . ';';

      if ($strMimeType == 'text/html' || $strMimeType == 'text/plain'){

        $strCharset = strtolower(InfraUtil::obterCharsetArquivo($strCaminhoNomeArquivo));

        if ($strCharset=='utf-8' || $strCharset=='iso-8859-1') {
          $strContentType .= ' charset='.$strCharset;
        }
      }

      $bolCabecalhoEvitarXSS = false;

      if ($strMimeType == 'text/html'){

        $bolCabecalhoEvitarXSS = true;

        if (!$bolValidacao) {

          if ($binConteudo == null) {
            $binConteudo = file_get_contents($strCaminhoNomeArquivo);
          }

          self::validarXss($binConteudo, $strIdentificacao, false, $strCaminhoNomeArquivo, $dbIdDocumento, $strCharset);
        }
      }

      InfraPagina::montarHeaderDownload($strNomeArquivo, $strContentDisposition, $strContentType, $bolCabecalhoEvitarXSS);

      ob_start();

      if ($binConteudo != null){

        echo $binConteudo;

      }else {

        $fp = fopen($strCaminhoNomeArquivo, "rb");

        while (!feof($fp)) {

          echo fread($fp, TAM_BLOCO_LEITURA_ARQUIVO);

          if (ob_get_length()) {
            ob_flush();
            flush();
            ob_end_flush();
          }
        }

        fclose($fp);
      }

      if (ob_get_length()) {
        @ob_flush();
        @flush();
        @ob_end_flush();
      }

      //@ob_end_clean();

      if ($bolExcluirAutomaticamente && substr(trim($strCaminhoNomeArquivo), 0, strlen(DIR_SEI_TEMP)) == DIR_SEI_TEMP){
        unlink($strCaminhoNomeArquivo);
      }

    }catch(Exception $e){

      if (strpos(strtoupper($e->__toString()),'NO SUCH FILE OR DIRECTORY')!==false){
        throw new InfraException('Erro acessando o reposit�rio de arquivos.', $e);
      }

      throw $e;
    }
  }

  public static function getContentDisposition($strNomeArquivo){

    $ret = 'inline';

    $strMimeType = InfraUtil::getStrMimeType($strNomeArquivo);

    $strTipo = substr($strMimeType, 0, 6);

    if ($strTipo == 'video/' || $strTipo == 'audio/' || $strMimeType == 'application/zip' || $strMimeType == 'application/rar') {
      $ret = 'attachment';
    }

    return $ret;
  }

  public static function validarXss(&$strConteudo, $strIdentificacao='', $bolGravacao = false, $strNomeArquivo = '', $dbIdDocumento = '', $strCharset = ''){
    try {

      $arrXssExcecoes = ConfiguracaoSEI::getInstance()->getValor('XSS', 'ProtocolosExcecoes', false, array());

      if (in_array($strIdentificacao, $arrXssExcecoes)){
        return;
      }

      if ($strIdentificacao!=''){
        $strIdentificacao = ' ('.$strIdentificacao.')';
      }

      if ($strNomeArquivo!=''){
        $strNomeArquivo = ', arquivo '.$strNomeArquivo;
      }

      if (self::$NIVEL_VERIFICACAO_ROTINA == null){
        $strXssNivelValidacao = ConfiguracaoSEI::getInstance()->getValor('XSS', 'NivelVerificacao', false, 'A');
      }else{
        $strXssNivelValidacao = self::$NIVEL_VERIFICACAO_ROTINA;
      }

      if (!in_array($strXssNivelValidacao,array('N','B','A'))){
        throw new InfraException('N�vel de verifica��o de XSS inv�lido ['.$strXssNivelValidacao.'].');
      }

      if (trim($strConteudo)!='') {

        if ($strXssNivelValidacao == 'B') {

          $arrXssBasico = ConfiguracaoSEI::getInstance()->getValor('XSS', 'NivelBasico', false, null);

          $arrXssNaoPermitidosBasico = null;
          if ($arrXssBasico !== null) {
            if (isset($arrXssBasico['ValoresNaoPermitidos']) && $arrXssBasico['ValoresNaoPermitidos'] !== null) {
              $arrXssNaoPermitidosBasico = $arrXssBasico['ValoresNaoPermitidos'];
            }
          }

          $objInfraXSS = new InfraXSS();
          $arrRetBasico = $objInfraXSS->verificacaoBasica($strConteudo, $arrXssNaoPermitidosBasico);

          if ($arrRetBasico != null) {

            if (count($arrRetBasico) == 1) {
              $strEncontrados = ', encontrado '.$arrRetBasico[0];
            } else {
              $strEncontrados = ', encontrados '.implode(' | ', $arrRetBasico);
            }

            throw new InfraException(self::$MSG_ERRO_XSS.$strIdentificacao.'.', null, 'N�vel '.$strXssNivelValidacao.$strNomeArquivo.$strEncontrados.'.');
          }

        } else if ($strXssNivelValidacao == 'A') {

          $arrXssAvancadoTagsPermitidas = null;
          $arrXssAvancadoTagsAtributosPermitidos = null;
          $bolXssAvancadoFiltrarConteudoConsulta = false;

          $arrXssAvancado = ConfiguracaoSEI::getInstance()->getValor('XSS', 'NivelAvancado', false, null);

          if ($arrXssAvancado !== null) {

            if (isset($arrXssAvancado['TagsPermitidas']) && $arrXssAvancado['TagsPermitidas'] !== null) {
              $arrXssAvancadoTagsPermitidas = $arrXssAvancado['TagsPermitidas'];
            }

            if (isset($arrXssAvancado['TagsAtributosPermitidos']) && $arrXssAvancado['TagsAtributosPermitidos'] !== null) {
              $arrXssAvancadoTagsAtributosPermitidos = $arrXssAvancado['TagsAtributosPermitidos'];
            }


            if (self::$NIVEL_VERIFICACAO_ROTINA == null) {
              if (isset($arrXssAvancado['FiltrarConteudoConsulta']) && $arrXssAvancado['FiltrarConteudoConsulta'] !== null) {
                $bolXssAvancadoFiltrarConteudoConsulta = $arrXssAvancado['FiltrarConteudoConsulta'];
              }
            }
          }

          if ($bolGravacao) {
            $bolXssAvancadoFiltrarConteudoConsulta = false;
          }

          $bolUtf8 = ($strNomeArquivo != '' && $strCharset == 'utf-8');

          $strConteudoXss = $strConteudo;

          $strConteudoXss = preg_replace('/(Criado por\s*<a )onclick="alert\(\'(?:[0-9\.\,\pL \-_]|\\\\&#039;)*\'\)" alt/i', '$1alt', $strConteudoXss);
          $strConteudoXss = preg_replace('/(<\/a>, vers�o \d* por\s+<a )onclick="alert\(\'(?:[0-9\.\,\pL \-_]|\\\\&#039;)*\'\)" alt/i', '$1alt', $strConteudoXss);

          if (!$bolUtf8) {
            $strConteudoXss = utf8_encode($strConteudoXss);
          }

          $objInfraXSS = new InfraXSS();
          $bolXss = $objInfraXSS->verificacaoAvancada($strConteudoXss, $arrXssAvancadoTagsPermitidas, $arrXssAvancadoTagsAtributosPermitidos);

          if ($bolXss) {

            if ($strConteudoXss != '') {

              $strDiferencas = $objInfraXSS->getStrDiferenca();

              if (!$bolUtf8) {
                $strConteudoXss = utf8_decode($strConteudoXss);
                $strDiferencas = utf8_decode($strDiferencas);
              }

            } else {
              $strDiferencas = "N�o foi poss�vel processar o conte�do.";
            }

            $strDiferencas = "\n\nDiferen�as:\n".$strDiferencas;

            $strUsuario = '';
            if (SessaoSEI::getInstance()->getStrSiglaUsuario() !== null) {
              $strUsuario .= ", usu�rio ".SessaoSEI::getInstance()->getStrSiglaUsuario();

              if (SessaoSEI::getInstance()->getStrSiglaOrgaoUsuario() !== null) {
                $strUsuario .= '/'.SessaoSEI::getInstance()->getStrSiglaOrgaoUsuario();
              }
            }

            if ($dbIdDocumento != null) {
              $strIdConteudo = ', id_documento '.$dbIdDocumento;

              $objProtocoloDTO = new ProtocoloDTO();
              $objProtocoloDTO->retStrStaNivelAcessoGlobal();
              $objProtocoloDTO->setDblIdProtocolo($dbIdDocumento);

              $objProtocoloRN = new ProtocoloRN();
              $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

              if ($objProtocoloDTO != null && $objProtocoloDTO->getStrStaNivelAcessoGlobal() != ProtocoloRN::$NA_PUBLICO) {
                $strDiferencas = '';
              }
            }

            $objInfraExceptionXss = new InfraException(self::$MSG_ERRO_XSS.$strIdentificacao.'.', null, 'N�vel '.$strXssNivelValidacao.$strUsuario.$strIdConteudo.$strNomeArquivo.'.'.$strDiferencas);

            if ($bolXssAvancadoFiltrarConteudoConsulta) {
              LogSEI::getInstance()->gravar('Descri��o:'."\n".$objInfraExceptionXss->getStrDescricao()."\n\nDetalhes:\n".$objInfraExceptionXss->getStrDetalhes());
              $strConteudo = $strConteudoXss;
            } else {
              throw $objInfraExceptionXss;
            }
          }
        }
      }

    }catch(Exception $e){
      throw new InfraException('Erro validando XSS.', $e);
    }

  }
  
  public static function rotinaVerificaoXss($strNivelVerificacao, $dtaInicio, $dtaFim){
    try{

      BancoSEI::getInstance()->abrirConexao();

      $objInfraException = new InfraException();

      ini_set('max_execution_time','0');
      ini_set('memory_limit','2048M');

      $numSeg = InfraUtil::verificarTempoProcessamento();

      self::logar('Verifica��o XSS - Iniciando an�lise de documentos...');

      if (InfraString::isBolVazia($strNivelVerificacao)){
        $objInfraException->lancarValidacao('N�vel de verifica��o n�o informado.');
      }

      if (!in_array($strNivelVerificacao,array('B','A'))){
        throw new InfraException('N�vel de verifica��o de XSS "'.$strNivelVerificacao.'" inv�lido valores poss�veis "A" (Avan�ado) e "B" (B�sico).');
      }

      self::$NIVEL_VERIFICACAO_ROTINA = $strNivelVerificacao;

      $dtaInicio = trim($dtaInicio);
      $dtaFim = trim($dtaFim);

      if ($dtaInicio!='' || $dtaFim!='') {

        if (InfraString::isBolVazia($dtaInicio)){
          $objInfraException->lancarValidacao('Data inicial n�o informada.');
        }

        if (InfraString::isBolVazia($dtaFim)){
          $objInfraException->lancarValidacao('Data final n�o informada.');
        }

        if (!InfraData::validarData($dtaInicio)) {
          $objInfraException->lancarValidacao("Data inicial [" . $dtaInicio . "] inv�lida.\n");
        }

        if (!InfraData::validarData($dtaFim)) {
          $objInfraException->lancarValidacao("Data final [" . $dtaFim . "] inv�lida.\n");
        }

        if (InfraData::compararDatas($dtaInicio, $dtaFim)<0){
          $objInfraException->lancarValidacao("Per�odo inv�lido.");
        }
      }

      if ($dtaInicio!=null && $dtaFim!=null) {
        self::logar('Verifica��o XSS - '.$dtaInicio.' ate '.$dtaFim.'...');
      }

      $arrXssExcecoes = ConfiguracaoSEI::getInstance()->getValor('XSS', 'ProtocolosExcecoes', false, array());

      $numIgnorar = count($arrXssExcecoes);
      if ($numIgnorar==0){
        self::logar('Verifica��o XSS - Nenhuma exce��o configurada...');
      }else if ($numIgnorar==1){
        self::logar('Verifica��o XSS - 1 exce��o configurada...');
      }else{
        self::logar('Verifica��o XSS - '.$numIgnorar.' exce��es configuradas...');
      }

      $strMsgErroXss = InfraString::transformarCaixaBaixa(self::$MSG_ERRO_XSS);


      $objProtocoloRN 	= new ProtocoloRN();

      $objProtocoloDTO 	= new ProtocoloDTO();
      $objProtocoloDTO->setDistinct(true);
      $objProtocoloDTO->retDtaInclusao();
      $objProtocoloDTO->setStrStaProtocolo(ProtocoloRN::$TP_PROCEDIMENTO, InfraDTO::$OPER_DIFERENTE);

      if ($dtaInicio!=null && $dtaFim!=null) {
        $objProtocoloDTO->adicionarCriterio(array('Inclusao', 'Inclusao'),
            array(InfraDTO::$OPER_MAIOR_IGUAL, InfraDTO::$OPER_MENOR_IGUAL),
            array($dtaInicio, $dtaFim),
            InfraDTO::$OPER_LOGICO_AND);
      }

      $objProtocoloDTO->setOrdDtaInclusao(InfraDTO::$TIPO_ORDENACAO_DESC);

      $arrObjProtocoloDTOData = $objProtocoloRN->listarRN0668($objProtocoloDTO);

      $objEditorRN = new EditorRN();
      $objAnexoRN = new AnexoRN();
      $objDocumentoRN = new DocumentoRN();

      $numRegistrosProcessados = 0;
      $numErros = 0;

      foreach($arrObjProtocoloDTOData as $objProtocoloDTOData){

        $dtaInclusao = $objProtocoloDTOData->getDtaInclusao();

        self::logar('Verifica��o XSS - Data '.$dtaInclusao.'...');

        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->retDblIdProtocolo();
        $objProtocoloDTO->retStrProtocoloFormatado();
        $objProtocoloDTO->retStrStaProtocolo();
        $objProtocoloDTO->retStrStaDocumentoDocumento();
        $objProtocoloDTO->setDtaInclusao($dtaInclusao);
        $objProtocoloDTO->retStrSiglaUnidadeGeradora();
        $objProtocoloDTO->retStrNomeSerieDocumento();
        $objProtocoloDTO->retStrStaNivelAcessoGlobal();
        $objProtocoloDTO->setOrdDblIdProtocolo(InfraDTO::$TIPO_ORDENACAO_DESC);
        $arrObjProtocoloDTO = $objProtocoloRN->listarRN0668($objProtocoloDTO);

        $numRegistros 			=	count($arrObjProtocoloDTO);
        $numRegistrosPagina = 50;
        $numPaginas 				= ceil($numRegistros/$numRegistrosPagina);

        $arrObjNivelAcessoDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->listarNiveisAcessoRN0878(),'StaNivel');

        for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++) {

          $arrObjProtocoloDTOPagina = array_slice($arrObjProtocoloDTO, ($numPaginaAtual * $numRegistrosPagina), $numRegistrosPagina);

          foreach($arrObjProtocoloDTOPagina as $objProtocoloDTOPagina){

            if (in_array($objProtocoloDTOPagina->getStrProtocoloFormatado(),$arrXssExcecoes)) {
              self::logar('Verifica��o XSS - Documento '.$objProtocoloDTOPagina->getStrProtocoloFormatado().' ignorado');
            }else{

              $strComplemento = '[ID='.$objProtocoloDTOPagina->getDblIdProtocolo().', Protocolo='.$objProtocoloDTOPagina->getStrProtocoloFormatado().', Tipo='.$objProtocoloDTOPagina->getStrNomeSerieDocumento().', Unidade='.$objProtocoloDTOPagina->getStrSiglaUnidadeGeradora().', Acesso='.$arrObjNivelAcessoDTO[$objProtocoloDTOPagina->getStrStaNivelAcessoGlobal()]->getStrDescricao().']';

              if ($objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_EDITOR_INTERNO) {

                $numRegistrosProcessados++;

                try {

                  $objEditorDTO = new EditorDTO();
                  $objEditorDTO->setDblIdDocumento($objProtocoloDTOPagina->getDblIdProtocolo());
                  $objEditorDTO->setNumIdBaseConhecimento(null);
                  $objEditorDTO->setStrSinCabecalho('S');
                  $objEditorDTO->setStrSinRodape('S');
                  $objEditorDTO->setStrSinCarimboPublicacao('N');
                  $objEditorDTO->setStrSinIdentificacaoVersao('N');
                  $objEditorDTO->setStrSinProcessarLinks('N');

                  $objEditorRN->consultarHtmlVersao($objEditorDTO);

                } catch (Exception $excXss) {
                  $numErros++;

                  if (strpos(InfraString::transformarCaixaBaixa($excXss->__toString()), $strMsgErroXss) !== false) {
                    self::logar('Verifica��o XSS - '.$excXss->getStrDescricao().' '.$excXss->getStrDetalhes()."\n\n".$strComplemento);
                  }else{
                    self::logar(InfraException::inspecionar($excXss));
                  }
                }


              } else if ($objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_EXTERNO ||
                         $objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_AUTOMATICO ||
                         $objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_GERADO) {


                if ($objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_AUTOMATICO || $objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_FORMULARIO_GERADO){

                  try{
                    $objDocumentoDTO = new DocumentoDTO();
                    $objDocumentoDTO->setDblIdDocumento($objProtocoloDTOPagina->getDblIdProtocolo());
                    $objDocumentoDTO->setObjInfraSessao(SessaoSEI::getInstance());
                    $objDocumentoDTO->setStrLinkDownload(null);

                    $objDocumentoRN->consultarHtmlFormulario($objDocumentoDTO);
                  } catch (Exception $excXss) {
                    $numErros++;
                    if (strpos(InfraString::transformarCaixaBaixa($excXss->__toString()), $strMsgErroXss) !== false) {
                      self::logar('Verifica��o XSS - '.$excXss->getStrDescricao().' '.$excXss->getStrDetalhes()."\n\n".$strComplemento);
                    }else{
                      self::logar(InfraException::inspecionar($excXss));
                    }
                  }
                }

                $objAnexoDTO = new AnexoDTO();
                $objAnexoDTO->retNumIdAnexo();
                $objAnexoDTO->retDthInclusao();
                $objAnexoDTO->retStrNome();
                $objAnexoDTO->retDthInclusao();
                $objAnexoDTO->retNumTamanho();
                $objAnexoDTO->retStrHash();
                $objAnexoDTO->setDblIdProtocolo($objProtocoloDTOPagina->getDblIdProtocolo());

                $arrObjAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);

                foreach ($arrObjAnexoDTO as $objAnexoDTO) {

                  if (InfraUtil::getStrMimeType($objAnexoDTO->getStrNome()) == 'text/html') {

                    $numRegistrosProcessados++;

                    $strCaminhoArquivo = $objAnexoRN->obterLocalizacao($objAnexoDTO);

                    $strMsg = '';
                    if (!file_exists($strCaminhoArquivo)) {
                      $strMsg = $strCaminhoArquivo.' n�o encontrado ';
                    } else if (filesize($strCaminhoArquivo) != $objAnexoDTO->getNumTamanho()) {
                      $strMsg = $strCaminhoArquivo.' tamanho diferente ';
                    } else if (md5_file($strCaminhoArquivo) != $objAnexoDTO->getStrHash()) {
                      $strMsg = $strCaminhoArquivo.' conte�do corrompido ';
                    }

                    if ($strMsg != '') {

                      $numErros++;
                      self::logar($strMsg.' (documento associado '.$objProtocoloDTOPagina->getStrProtocoloFormatado().')');

                    } else {

                      try {
                        $strConteudo = file_get_contents($objAnexoRN->obterLocalizacao($objAnexoDTO));
                        if ($objProtocoloDTOPagina->getStrStaDocumentoDocumento() == DocumentoRN::$TD_EXTERNO){
                          self::validarXss($strConteudo, $objProtocoloDTOPagina->getStrProtocoloFormatado(), false, $strCaminhoArquivo, $objProtocoloDTOPagina->getDblIdProtocolo());
                        }else{
                          self::validarXss($strConteudo, $objProtocoloDTOPagina->getStrProtocoloFormatado().', anexo '.$objAnexoDTO->getStrNome(), false, $strCaminhoArquivo, $objProtocoloDTOPagina->getDblIdProtocolo());
                        }

                      } catch (Exception $excXss) {
                        $numErros++;
                        if (strpos(InfraString::transformarCaixaBaixa($excXss->__toString()), $strMsgErroXss) !== false) {
                          self::logar('Verifica��o XSS - '.$excXss->getStrDescricao().' '.$excXss->getStrDetalhes()."\n\n".$strComplemento);
                        }else{
                          self::logar(InfraException::inspecionar($excXss));
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }

      $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);

      self::logar('Verifica��o XSS - '.InfraUtil::formatarMilhares($numRegistrosProcessados).' documentos verificados em '.InfraData::formatarTimestamp($numSeg). ' ('.InfraUtil::formatarMilhares($numErros).' erros)');

      $numSeg = InfraUtil::verificarTempoProcessamento();

      self::logar('Verifica��o XSS - Iniciando an�lise de bases de conhecimento...');

      $objBaseConhecimentoRN 	= new BaseConhecimentoRN();
      $objBaseConhecimentoDTO = new BaseConhecimentoDTO();
      $objBaseConhecimentoDTO->retNumIdBaseConhecimento();
      $objBaseConhecimentoDTO->retStrDescricao();
      $objBaseConhecimentoDTO->retStrSiglaUnidade();
      $objBaseConhecimentoDTO->retStrStaDocumento();
      $objBaseConhecimentoDTO->retDblIdDocumentoEdoc();
      $objBaseConhecimentoDTO->setStrStaEstado(array(BaseConhecimentoRN::$TE_LIBERADO, BaseConhecimentoRN::$TE_RASCUNHO), InfraDTO::$OPER_IN);

      if ($dtaInicio!=null && $dtaFim!=null) {
        $objBaseConhecimentoDTO->adicionarCriterio(array('Geracao', 'Geracao'),
            array(InfraDTO::$OPER_MAIOR_IGUAL, InfraDTO::$OPER_MENOR_IGUAL),
            array($dtaInicio.' 00:00:00', $dtaFim.' 23:59:59'),
            InfraDTO::$OPER_LOGICO_AND);
      }

      $objBaseConhecimentoDTO->setOrdNumIdBaseConhecimento(InfraDTO::$TIPO_ORDENACAO_DESC);

      $arrObjBaseConhecimentoDTO =	$objBaseConhecimentoRN->listar($objBaseConhecimentoDTO);

      $numRegistros 			=	count($arrObjBaseConhecimentoDTO);
      $numRegistrosPagina = 10;
      $numPaginas 				= ceil($numRegistros/$numRegistrosPagina);

      $numRegistrosProcessados = 0;
      $numErros = 0;

      $objEditorRN = new EditorRN();
      $objEdocRN = new EDocRN();

      for ($numPaginaAtual = 0; $numPaginaAtual < $numPaginas; $numPaginaAtual++){

        if ($numPaginaAtual ==  ($numPaginas-1)){
          $numRegistrosAtual = $numRegistros;
        }else{
          $numRegistrosAtual = ($numPaginaAtual+1)*$numRegistrosPagina;
        }

        self::logar('Verifica��o XSS - Bases de Conhecimento - ['.$numRegistrosAtual.' de '.$numRegistros.']...');

        $offset = ($numPaginaAtual*$numRegistrosPagina);

        if (($offset + $numRegistrosPagina) > $numRegistros) {
          $length = $numRegistros - $offset;
        }else{
          $length = $numRegistrosPagina;
        }

        $arrBasesConhecimentoDTOPagina = array_slice($arrObjBaseConhecimentoDTO, $offset, $length);

        foreach($arrBasesConhecimentoDTOPagina as $objBaseConhecimentoDTOPagina) {

          $numRegistrosProcessados++;

          try {

            if ($objBaseConhecimentoDTOPagina->getStrStaDocumento()==DocumentoRN::$TD_EDITOR_EDOC){

              $objDocumentoDTO = new DocumentoDTO();
              $objDocumentoDTO->setDblIdDocumentoEdoc($objBaseConhecimentoDTOPagina->getDblIdDocumentoEdoc());
              $objEdocRN->consultarHTMLDocumentoRN1204($objDocumentoDTO);

            }else {

              $objEditorDTO = new EditorDTO();
              $objEditorDTO->setDblIdDocumento(null);
              $objEditorDTO->setNumIdBaseConhecimento($objBaseConhecimentoDTOPagina->getNumIdBaseConhecimento());
              $objEditorDTO->setStrSinCabecalho('S');
              $objEditorDTO->setStrSinRodape('S');
              $objEditorDTO->setStrSinCarimboPublicacao('N');
              $objEditorDTO->setStrSinIdentificacaoVersao('N');
              $objEditorDTO->setStrSinProcessarLinks('N');

              $objEditorRN->consultarHtmlVersao($objEditorDTO);
            }

          } catch (Exception $excXss) {
            $numErros++;
            if (strpos(InfraString::transformarCaixaBaixa($excXss->__toString()), $strMsgErroXss) !== false) {
              self::logar('Verifica��o XSS - '.$excXss->getStrDescricao().' '.$excXss->getStrDetalhes());
            }else{
              self::logar(InfraException::inspecionar($excXss));
            }
          }

          $objAnexoDTO = new AnexoDTO();
          $objAnexoDTO->retNumIdAnexo();
          $objAnexoDTO->retDthInclusao();
          $objAnexoDTO->retStrNome();
          $objAnexoDTO->retDthInclusao();
          $objAnexoDTO->retNumTamanho();
          $objAnexoDTO->retStrHash();
          $objAnexoDTO->setNumIdBaseConhecimento($objBaseConhecimentoDTOPagina->getNumIdBaseConhecimento());

          $arrObjAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);

          foreach ($arrObjAnexoDTO as $objAnexoDTO) {

            if (InfraUtil::getStrMimeType($objAnexoDTO->getStrNome()) == 'text/html') {

              $numRegistrosProcessados++;

              $strCaminhoArquivo = $objAnexoRN->obterLocalizacao($objAnexoDTO);

              $strMsg = '';
              if (!file_exists($strCaminhoArquivo)) {
                $strMsg = $strCaminhoArquivo.' n�o encontrado ';
              } else if (filesize($strCaminhoArquivo) != $objAnexoDTO->getNumTamanho()) {
                $strMsg = $strCaminhoArquivo.' tamanho diferente ';
              } else if (md5_file($strCaminhoArquivo) != $objAnexoDTO->getStrHash()) {
                $strMsg = $strCaminhoArquivo.' conte�do corrompido ';
              }

              if ($strMsg != '') {

                $numErros++;
                self::logar($strMsg.' (base de conhecimento associada '.$objBaseConhecimentoDTOPagina->getStrDescricao().'/'.$objBaseConhecimentoDTOPagina->getStrSiglaUnidade().')');

              } else {

                try {
                  $strConteudo = file_get_contents($objAnexoRN->obterLocalizacao($objAnexoDTO));
                  self::validarXss($strConteudo, 'base de conhecimento '.$objBaseConhecimentoDTOPagina->getStrDescricao().'/'.$objBaseConhecimentoDTOPagina->getStrSiglaUnidade().', anexo '.$objAnexoDTO->getStrNome(), false, $strCaminhoArquivo);
                } catch (Exception $excXss) {
                  $numErros++;
                  if (strpos(InfraString::transformarCaixaBaixa($excXss->__toString()), $strMsgErroXss) !== false) {
                    self::logar('Verifica��o XSS - '.$excXss->getStrDescricao().' '.$excXss->getStrDetalhes());
                  }else{
                    self::logar(InfraException::inspecionar($excXss));
                  }
                }
              }
            }
          }
        }
      }

      $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);

      self::logar('Verifica��o XSS - '.InfraUtil::formatarMilhares($numRegistrosProcessados).' bases de conhecimento verificadas em '.InfraData::formatarTimestamp($numSeg). ' ('.InfraUtil::formatarMilhares($numErros).' erros)');

      BancoSEI::getInstance()->fecharConexao();
      
    }catch(Exception $e){
      throw new InfraException('Erro na rotina de verifica��o de XSS.', $e);
    }
  }

  private static function logar($strTexto, $strTipoLog='I'){
    InfraDebug::getInstance()->gravar(InfraString::excluirAcentos($strTexto));
    LogSEI::getInstance()->gravar($strTexto,$strTipoLog);
  }

  public static function montarCabecalhoConteudo($strIdentificacao, $strAcoes, $strLinkConteudo, &$strCss, &$strJsInicializar, &$strJsCorpo, &$strHtml){

    $strCss = ' 
      body {margin:0;overflow:hidden}
      #divCabecalho {position:fixed; width:100%;height:40px;z-index:9000;}
      #divIdentificacao label, #divIdentificacao a {color:white;font-size:20px;position:absolute;left:1%;top:7px;}
      #divIdentificacao a {text-decoration:none;}
      #divAcoes {float:right;}
      #divAcoes img{float:left;border:0;padding: 4px 5px 0 0;}
      #divAcoes img:hover{opacity:0.3;filter:alpha(opacity=30);};
      #divConteudo {box-sizing: border-box;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;}
      #ifrConteudo {width:100%;border:0;top:40px;position:absolute;overflow:auto;}
      ';

    $strJsInicializar = '  
        infraAdicionarEvento(window,\'resize\',redimensionar);
        redimensionar();
        ';

    $strJsCorpo = '  
      function redimensionar() {
        setTimeout(function(){
          var tamCabecalho = document.getElementById(\'divCabecalho\').offsetHeight;
          var ifrConteudo = document.getElementById(\'ifrConteudo\');
          if (tamCabecalho > ifrConteudo.offsetHeight) tamCabecalho -= ifrConteudo.offsetHeight;
          var tamConteudo = infraClientHeight() - tamCabecalho;
          ifrConteudo.style.height = (tamConteudo > 0 ? tamConteudo : 1) + \'px\';
        },0);
      }'
    ;


    $strHtml = '<body onload="inicializar()">
      <div id="divCabecalho" class="infraCorBarraSistema">
  
        <div id="divIdentificacao">
          '.$strIdentificacao.'
        </div>
  
        <div id="divAcoes">
          '.$strAcoes.'
        </div>
  
      </div>
      
      <div id="divConteudo">
        <iframe id="ifrConteudo" src="'.$strLinkConteudo.'"></iframe>
      </div>
    </body>
    ';
  }
}
?>