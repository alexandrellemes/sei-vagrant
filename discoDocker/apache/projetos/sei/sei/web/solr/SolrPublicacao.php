<?
require_once dirname(__FILE__).'/../SEI.php';

class SolrPublicacao {

  public static function executar(PesquisaPublicacaoSolrDTO $objPesquisaPublicacaoSolrDTO, &$numRegistros) {

    //die($objPesquisaPublicacaoSolrDTO->__toString());

    $objInfraException = new InfraException();

    $partialfields = '';

    $arr = array();
    foreach($objPesquisaPublicacaoSolrDTO->getArrNumIdOrgao() as $numIdOrgao){
      if (!is_numeric($numIdOrgao)){
        $objInfraException->lancarValidacao('Valor inv?lido para o filtro de ?rg?o.');
      }
      array_push($arr, "id_org_resp:".$numIdOrgao);
    }
    if (count($arr)>0){
      $partialfields .= '('.implode(" OR ", $arr).')';
    }


    if ($objPesquisaPublicacaoSolrDTO->getStrResumo()!=null){
      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '('.SolrUtil::formatarOperadores($objPesquisaPublicacaoSolrDTO->getStrResumo(),'resumo').')';
    }

    if ($objPesquisaPublicacaoSolrDTO->getNumIdUnidadeResponsavel()!=null){

      if (!is_numeric($objPesquisaPublicacaoSolrDTO->getNumIdUnidadeResponsavel())){
        $objInfraException->lancarValidacao('Valor inv?lido para o filtro de Unidade Respons?vel.');
      }

      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '(id_uni_resp:'.$objPesquisaPublicacaoSolrDTO->getNumIdUnidadeResponsavel().')';
    }

    if ($objPesquisaPublicacaoSolrDTO->getNumIdSerie()!=null){

      if (!is_numeric($objPesquisaPublicacaoSolrDTO->getNumIdSerie())){
        $objInfraException->lancarValidacao('Valor inv?lido para o filtro de Tipo de Documento.');
      }

      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '(id_serie:'.$objPesquisaPublicacaoSolrDTO->getNumIdSerie().')';
    }

    if ($objPesquisaPublicacaoSolrDTO->getStrNumero()!=null){
      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '(numero:*'.$objPesquisaPublicacaoSolrDTO->getStrNumero().'*)';
    }

    if ($objPesquisaPublicacaoSolrDTO->getStrProtocoloPesquisa()!=null){
      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '(prot_pesq:*'.InfraUtil::retirarFormatacao($objPesquisaPublicacaoSolrDTO->getStrProtocoloPesquisa(),false).'*)';
    }

    if ($objPesquisaPublicacaoSolrDTO->getNumIdVeiculoPublicacao()!=null){

      if (!is_numeric($objPesquisaPublicacaoSolrDTO->getNumIdVeiculoPublicacao())){
        $objInfraException->lancarValidacao('Valor inv?lido para o filtro de Ve?culo de Publica??o.');
      }

      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }
      $partialfields .= '(id_veic_pub:'.$objPesquisaPublicacaoSolrDTO->getNumIdVeiculoPublicacao().')';
    }

    if ($objPesquisaPublicacaoSolrDTO->getDtaGeracao()!=null){

      if (!InfraData::validarData($objPesquisaPublicacaoSolrDTO->getDtaGeracao())){
        $objInfraException->lancarValidacao('Valor inv?lido para o filtro de Data do Documento.');
      }


      if ($partialfields!=''){
        $partialfields .= ' AND ';
      }

      $dia = substr($objPesquisaPublicacaoSolrDTO->getDtaGeracao(), 0, 2);
      $mes = substr($objPesquisaPublicacaoSolrDTO->getDtaGeracao(), 3, 2);
      $ano = substr($objPesquisaPublicacaoSolrDTO->getDtaGeracao(), 6, 4);

      $partialfields .=	'dta_doc:"' . $ano . '-' . $mes . '-' . $dia . 'T00:00:00Z"';
    }

    if (!in_array($objPesquisaPublicacaoSolrDTO->getStrStaTipoData(), array('H','I','E'))){
      $objInfraException->lancarValidacao('Valor inv?lido para o filtro de tipo de Data de Publica??o.');
    }

    if ($objPesquisaPublicacaoSolrDTO->getStrStaTipoData()=='H') {
      if ($partialfields != '') {
        $partialfields .= ' AND ';
      }

      $dia = substr(InfraData::getStrDataAtual(), 0, 2);
      $mes = substr(InfraData::getStrDataAtual(), 3, 2);
      $ano = substr(InfraData::getStrDataAtual(), 6, 4);

      $partialfields .= 'dta_pub:"' . $ano . '-' . $mes . '-' . $dia . 'T00:00:00Z"';
    }

    if ($objPesquisaPublicacaoSolrDTO->getStrStaTipoData()=='E'){

      if (!InfraData::validarData($objPesquisaPublicacaoSolrDTO->getDtaInicio())){
        $objInfraException->lancarValidacao('Valor inv?lido para o filtro de Data Inicial.');
      }

      if (!InfraData::validarData($objPesquisaPublicacaoSolrDTO->getDtaFim())){
        $objInfraException->lancarValidacao('Valor inv?lido para o filtro de Data Final.');
      }

      $dtaInicio = $objPesquisaPublicacaoSolrDTO->getDtaInicio();
      $dtaFim = $objPesquisaPublicacaoSolrDTO->getDtaFim();

      if ($dtaInicio!=null && $dtaFim!=null) {
        $dia1 = substr($dtaInicio, 0, 2);
        $mes1 = substr($dtaInicio, 3, 2);
        $ano1 = substr($dtaInicio, 6, 4);

        $dia2 = substr($dtaFim, 0, 2);
        $mes2 = substr($dtaFim, 3, 2);
        $ano2 = substr($dtaFim, 6, 4);

        if ($partialfields != '') {
          $partialfields .= ' AND ';
        }

        $partialfields .= 'dta_pub:[' . $ano1 . '-' . $mes1 . '-' . $dia1 . 'T00:00:00Z TO ' . $ano2 . '-' . $mes2 . '-' . $dia2 . 'T00:00:00Z]';
      }
    }

    //die($partialfields);

    $parametros = new stdClass();
    $parametros->q = SolrUtil::formatarOperadores($objPesquisaPublicacaoSolrDTO->getStrPalavrasChave());

    if ($parametros->q != '' && $partialfields != ''){
      $parametros->q = '('.$parametros->q.') AND '.$partialfields;
    }else if ($partialfields != ''){
      $parametros->q = $partialfields;
    }

    $parametros->q = utf8_encode($parametros->q);
    $parametros->start = $objPesquisaPublicacaoSolrDTO->getNumInicioPaginacao();
    $parametros->rows = 20;
    $parametros->sort =  'id_pub desc';

    $urlBusca = ConfiguracaoSEI::getInstance()->getValor('Solr','Servidor') . '/'.ConfiguracaoSEI::getInstance()->getValor('Solr','CorePublicacoes') .'/select?' . http_build_query($parametros).'&hl=true&hl.snippets=2&hl.fl=content&hl.fragsize=100&hl.maxAnalyzedChars=1048576&hl.alternateField=content&hl.maxAlternateFieldLength=100&fl=id,id_doc,id_pub,id_pub_leg,id_prot_agrup,dta_doc,id_org_resp,id_serie,id_uni_resp,numero,prot_doc,dta_pub,num_pub,id_veic_pub,resumo,id_veic_io,dta_pub_io,id_sec_io,pag_io';

    //InfraDebug::getInstance()->gravar('URL:'.$urlBusca);
    //InfraDebug::getInstance()->gravar("PAR?METROS: " . print_r($parametros, true));

    try{
      $resultados = file_get_contents($urlBusca, false);
    }catch(Exception $e){
      throw new InfraException('Erro realizando pesquisa.',$e, urldecode($urlBusca),false);
    }

    if ($resultados == ''){
      throw new InfraException('Nenhum retorno encontrado no resultado da pesquisa.');
    }
    
    $xml = simplexml_load_string($resultados);

    $arrRet = $xml->xpath('/response/result/@numFound');
    
    $itens = array_shift($arrRet);

    $html = '';

    if ($itens == 0) {

      $html .= "<div class=\"sem-resultado\">";
      $html .= "Sua pesquisa pelo termo <b>" . PaginaSEI::tratarHTML($_POST["q"]) . "</b> n?o encontrou nenhum protocolo correspondente.";
      $html .= "<br/>";
      $html .= "<br/>";
      $html .= "Sugest?es:";
      $html .= "<ul>";
      $html .= "<li>Certifique-se de que todas as palavras estejam escritas corretamente.</li>";
      $html .= "<li>Tente palavras-chave diferentes.</li>";
      $html .= "<li>Tente palavras-chave mais gen?ricas.</li>";
      $html .= "</ul>";
      $html .= "</div>";

    }else{

      $registros = $xml->xpath('/response/result/doc');

      $numRegistros = count($registros);

      if ($numRegistros > 0) {

        $arrObjResultadoPublicacaoSolrDTO = array();

        for ($i = 0; $i < $numRegistros; $i++) {

          $id = SolrUtil::obterTag($registros[$i], 'id', 'str');

          $objResultadoPublicacaoSolrDTO = new ResultadoPublicacaoSolrDTO();
          $objResultadoPublicacaoSolrDTO->setDblIdDocumento(SolrUtil::obterTag($registros[$i], "id_doc", 'long'));
          $objResultadoPublicacaoSolrDTO->setNumIdPublicacao(SolrUtil::obterTag($registros[$i], "id_pub", 'int'));
          $objResultadoPublicacaoSolrDTO->setNumIdPublicacaoLegado(SolrUtil::obterTag($registros[$i], "id_pub_leg", 'int'));
          $objResultadoPublicacaoSolrDTO->setDblIdProtocoloAgrupador(SolrUtil::obterTag($registros[$i], "id_prot_agrup", 'long'));
          $objResultadoPublicacaoSolrDTO->setNumIdOrgaoResponsavel(SolrUtil::obterTag($registros[$i], "id_org_resp", 'int'));
          $objResultadoPublicacaoSolrDTO->setNumIdUnidadeResponsavel(SolrUtil::obterTag($registros[$i], "id_uni_resp", 'int'));
          $objResultadoPublicacaoSolrDTO->setNumIdSerie(SolrUtil::obterTag($registros[$i], "id_serie", 'int'));
          $objResultadoPublicacaoSolrDTO->setStrNumero(SolrUtil::obterTag($registros[$i], "numero", 'str'));
          $objResultadoPublicacaoSolrDTO->setStrProtocoloDocumentoFormatado(SolrUtil::obterTag($registros[$i], "prot_doc", 'str'));
          $objResultadoPublicacaoSolrDTO->setDtaDocumento(preg_replace("/(\d{4})-(\d{2})-(\d{2})(.*)/", "$3/$2/$1", SolrUtil::obterTag($registros[$i], "dta_doc", 'date')));
          $objResultadoPublicacaoSolrDTO->setDtaPublicacao(preg_replace("/(\d{4})-(\d{2})-(\d{2})(.*)/", "$3/$2/$1", SolrUtil::obterTag($registros[$i], "dta_pub", 'date')));
          $objResultadoPublicacaoSolrDTO->setNumNumeroPublicacao(SolrUtil::obterTag($registros[$i], "num_pub", 'str'));
          $objResultadoPublicacaoSolrDTO->setNumIdVeiculoPublicacao(SolrUtil::obterTag($registros[$i], "id_veic_pub", 'int'));
          $objResultadoPublicacaoSolrDTO->setStrResumo(SolrUtil::obterTag($registros[$i], "resumo", 'str'));
          $objResultadoPublicacaoSolrDTO->setNumIdVeiculoIO(SolrUtil::obterTag($registros[$i], "id_veic_io", 'int'));
          $objResultadoPublicacaoSolrDTO->setDtaPublicacaoIO(preg_replace("/(\d{4})-(\d{2})-(\d{2})(.*)/", "$3/$2/$1", SolrUtil::obterTag($registros[$i], "dta_pub_io", 'date')));
          $objResultadoPublicacaoSolrDTO->setNumIdSecaoIO(SolrUtil::obterTag($registros[$i], "id_sec_io", 'int'));
          $objResultadoPublicacaoSolrDTO->setStrPaginaIO(SolrUtil::obterTag($registros[$i], "pag_io", 'str'));

          // SNIPPET
          $temp = $xml->xpath("/response/lst[@name='highlighting']/lst[@name='".$id."']/arr[@name='content']/str");

          $snippet = '';
          for ($j = 0; $j < count($temp); $j++) {
            $snippetTemp = utf8_decode($temp[$j]);
            $snippetTemp = strtoupper(trim(strip_tags($snippetTemp))) == "NULL" ? null : $snippetTemp;
            $snippetTemp = preg_replace("/<br>/i", "<br />", $snippetTemp);
            $snippetTemp = preg_replace("/&lt;.*?&gt;/", "", $snippetTemp);
            $snippet .= $snippetTemp.'<b>&nbsp;&nbsp;...&nbsp;&nbsp;</b>';
          }

          $objResultadoPublicacaoSolrDTO->setStrSnippet($snippet);
          $arrObjResultadoPublicacaoSolrDTO[] = $objResultadoPublicacaoSolrDTO;
        }

        $arrIdOrgao = array();
        $arrIdUnidadeResponsavel = array();
        $arrIdSerie = array();
        $arrIdVeiculoPublicacao = array();
        $arrIdVeiculoImprensaNacional = array();
        $arrIdSecaoImprensaNacional = array();

        foreach ($arrObjResultadoPublicacaoSolrDTO as $objResultadoPublicacaoSolrDTO) {
          $arrIdOrgao[$objResultadoPublicacaoSolrDTO->getNumIdOrgaoResponsavel()] = 0;
          $arrIdUnidadeResponsavel[$objResultadoPublicacaoSolrDTO->getNumIdUnidadeResponsavel()] = 0;
          $arrIdSerie[$objResultadoPublicacaoSolrDTO->getNumIdSerie()] = 0;
          $arrIdVeiculoPublicacao[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoPublicacao()] = 0;
          $arrIdVeiculoImprensaNacional[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoIO()] = 0;
          $arrIdSecaoImprensaNacional[$objResultadoPublicacaoSolrDTO->getNumIdSecaoIO()] = 0;
        }

        $objOrgaoDTO = new OrgaoDTO();
        $objOrgaoDTO->setBolExclusaoLogica(false);
        $objOrgaoDTO->retNumIdOrgao();
        $objOrgaoDTO->retStrSigla();
        $objOrgaoDTO->retStrDescricao();
        $objOrgaoDTO->setNumIdOrgao(array_keys($arrIdOrgao), InfraDTO::$OPER_IN);

        $objOrgaoRN = new OrgaoRN();
        $arrObjOrgaoDTO = InfraArray::indexarArrInfraDTO($objOrgaoRN->listarRN1353($objOrgaoDTO),'IdOrgao');

        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->setBolExclusaoLogica(false);
        $objUnidadeDTO->retNumIdUnidade();
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->retStrDescricao();
        $objUnidadeDTO->setNumIdUnidade(array_keys($arrIdUnidadeResponsavel), InfraDTO::$OPER_IN);

        $objUnidadeRN = new UnidadeRN();
        $arrObjUnidadeDTO = InfraArray::indexarArrInfraDTO($objUnidadeRN->listarRN0127($objUnidadeDTO),'IdUnidade');

        $arrObjSerieDTO = null;
        if (count($arrIdSerie)) {
          $objSerieDTO = new SerieDTO();
          $objSerieDTO->setBolExclusaoLogica(false);
          $objSerieDTO->retNumIdSerie();
          $objSerieDTO->retStrNome();
          $objSerieDTO->setNumIdSerie(array_keys($arrIdSerie), InfraDTO::$OPER_IN);
          $objSerieRN = new SerieRN();
          $arrObjSerieDTO = InfraArray::indexarArrInfraDTO($objSerieRN->listarRN0646($objSerieDTO), 'IdSerie');
        }

        $arrObjVeiculoPublicacaoDTO = null;
        if (count($arrIdVeiculoPublicacao)) {
          $objVeiculoPublicacaoDTO = new VeiculoPublicacaoDTO();
          $objVeiculoPublicacaoDTO->setBolExclusaoLogica(false);
          $objVeiculoPublicacaoDTO->retNumIdVeiculoPublicacao();
          $objVeiculoPublicacaoDTO->retStrNome();
          $objVeiculoPublicacaoDTO->retStrStaTipo();
          $objVeiculoPublicacaoDTO->setNumIdVeiculoPublicacao(array_keys($arrIdVeiculoPublicacao), InfraDTO::$OPER_IN);

          $objVeiculoPublicacaoRN = new VeiculoPublicacaoRN();
          $arrObjVeiculoPublicacaoDTO = InfraArray::indexarArrInfraDTO($objVeiculoPublicacaoRN->listar($objVeiculoPublicacaoDTO), 'IdVeiculoPublicacao');
        }

        $arrObjVeiculoImprensaNacionalDTO = null;
        if (count($arrIdVeiculoImprensaNacional)) {
          $objVeiculoImprensaNacionalDTO = new VeiculoImprensaNacionalDTO();
          $objVeiculoImprensaNacionalDTO->setBolExclusaoLogica(false);
          $objVeiculoImprensaNacionalDTO->retNumIdVeiculoImprensaNacional();
          $objVeiculoImprensaNacionalDTO->retStrSigla();
          $objVeiculoImprensaNacionalDTO->retStrDescricao();
          $objVeiculoImprensaNacionalDTO->setNumIdVeiculoImprensaNacional(array_keys($arrIdVeiculoImprensaNacional), InfraDTO::$OPER_IN);

          $objVeiculoImprensaNacionalRN = new VeiculoImprensaNacionalRN();
          $arrObjVeiculoImprensaNacionalDTO = InfraArray::indexarArrInfraDTO($objVeiculoImprensaNacionalRN->listar($objVeiculoImprensaNacionalDTO), 'IdVeiculoImprensaNacional');
        }

        $arrObjSecaoImprensaNacionalDTO = null;
        if (count($arrIdSecaoImprensaNacional)) {
          $objSecaoImprensaNacionalDTO = new SecaoImprensaNacionalDTO();
          $objSecaoImprensaNacionalDTO->setBolExclusaoLogica(false);
          $objSecaoImprensaNacionalDTO->retNumIdSecaoImprensaNacional();
          $objSecaoImprensaNacionalDTO->retStrNome();
          $objSecaoImprensaNacionalDTO->setNumIdSecaoImprensaNacional(array_keys($arrIdSecaoImprensaNacional), InfraDTO::$OPER_IN);

          $objSecaoImprensaNacionalRN = new SecaoImprensaNacionalRN();
          $arrObjSecaoImprensaNacionalDTO = InfraArray::indexarArrInfraDTO($objSecaoImprensaNacionalRN->listar($objSecaoImprensaNacionalDTO), 'IdSecaoImprensaNacional');
        }

        $strSumarioTabela = 'Tabela de Publica??es Eletr?nicas.';
        $html .= '<table id="tblPublicacoes" width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";

        $html .= '<caption class="infraCaption">';
        $html .= SolrUtil::criarBarraEstatisticas($itens, $parametros->start, ($parametros->start + $parametros->rows));
        $html .= '</caption>';

        $html .= '<tr>';
        $html .= '<th class="infraTh" width="1%" valign="center">'.PaginaPublicacoes::getInstance()->getThCheck().'</th>'."\n";
        $html .= '<th class="infraTh" width="1%">Protocolo</th>'."\n";
        $html .= '<th class="infraTh" width="15%">Descri??o</th>'."\n";
        $html .= '<th class="infraTh" width="8%">Ve?culo</th>'."\n";
        $html .= '<th class="infraTh" width="8%">Data de Publica??o</th>'."\n";
        $html .= '<th class="infraTh" width="5%">Unidade</th>'."\n";
        $html .= '<th class="infraTh" width="5%">?rg?o</th>'."\n";
        $html .= '<th class="infraTh">Resumo</th>'."\n";
        $html .= '<th class="infraTh" width="8%">Imprensa Nacional</th>'."\n";
        $html .= '<th class="infraTh" width="5%">A??es</th>'."\n";
        $html .= '</tr>'."\n";

        $html .= '</tr>'."\n";

        $i = 0;

        $arrObjPublicacaoDTO = array();
        $arrObjPublicacaoLegadoDTO = array();
        foreach ($arrObjResultadoPublicacaoSolrDTO as $objResultadoPublicacaoSolrDTO) {
          if ($objResultadoPublicacaoSolrDTO->getNumIdPublicacao() != null) {
            $objPublicacaoDTO = new PublicacaoDTO();
            $objPublicacaoDTO->setDblIdDocumento($objResultadoPublicacaoSolrDTO->getDblIdDocumento());
            $arrObjPublicacaoDTO[] = $objPublicacaoDTO;
          } else {
            $objPublicacaoLegadoDTO = new PublicacaoLegadoDTO();
            $objPublicacaoLegadoDTO->setNumIdPublicacaoLegado($objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado());
            $arrObjPublicacaoLegadoDTO[] = $objPublicacaoLegadoDTO;
          }
        }

        $objPublicacaoRN = new PublicacaoRN();
        $objPublicacaoLegadoRN = new PublicacaoLegadoRN();
        $arrIdPublicacaoRelacionada = InfraArray::converterArrInfraDTO($objPublicacaoRN->retornarPublicacoesRelacionadas($arrObjPublicacaoDTO), 'IdPublicacao');
        $arrIdPublicacaoLegadoRelacionada = InfraArray::converterArrInfraDTO($objPublicacaoLegadoRN->retornarPublicacoesRelacionadasLegado($arrObjPublicacaoLegadoDTO), 'IdPublicacaoLegado');

        $strTrClass = 'infraTrEscura';

        foreach ($arrObjResultadoPublicacaoSolrDTO as $objResultadoPublicacaoSolrDTO) {

          //die($objResultadoPublicacaoSolrDTO->__toString());

          if (isset($arrObjOrgaoDTO[$objResultadoPublicacaoSolrDTO->getNumIdOrgaoResponsavel()])) {
            $objOrgaoDTO = $arrObjOrgaoDTO[$objResultadoPublicacaoSolrDTO->getNumIdOrgaoResponsavel()];
            $strSiglaOrgaoResponsavel = PaginaPublicacoes::tratarHTML($objOrgaoDTO->getStrSigla());
            $strDescricaoOrgaoResponsavel = PaginaPublicacoes::tratarHTML($objOrgaoDTO->getStrDescricao());
          } else {
            $strSiglaOrgaoResponsavel = '[?rg?o n?o encontrado]';
            $strDescricaoOrgaoResponsavel = '[?rg?o n?o encontrado]';
          }

          if (isset($arrObjUnidadeDTO[$objResultadoPublicacaoSolrDTO->getNumIdUnidadeResponsavel()])) {
            $objUnidadeDTO = $arrObjUnidadeDTO[$objResultadoPublicacaoSolrDTO->getNumIdUnidadeResponsavel()];
            $strSiglaUnidadeResponsavel = PaginaPublicacoes::tratarHTML($objUnidadeDTO->getStrSigla());
            $strDescricaoUnidadeResponsavel = PaginaPublicacoes::tratarHTML($objUnidadeDTO->getStrDescricao());
          } else {
            $strSiglaUnidadeResponsavel = '[unidade n?o encontrada]';
            $strDescricaoUnidadeResponsavel = '[unidade n?o encontrada]';
          }

          $strNomeSerie = '';
          if (isset($arrObjSerieDTO[$objResultadoPublicacaoSolrDTO->getNumIdSerie()])) {
            $strNomeSerie = PaginaPublicacoes::tratarHTML($arrObjSerieDTO[$objResultadoPublicacaoSolrDTO->getNumIdSerie()]->getStrNome());
          } else {
            $strNomeSerie = '[tipo de documento n?o encontrado]';
          }

          $strNomeVeiculoPublicacao = '';
          $strStaTipoVeiculoPublicacao = '';
          if (isset($arrObjVeiculoPublicacaoDTO[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoPublicacao()])) {
            $strNomeVeiculoPublicacao = PaginaPublicacoes::tratarHTML($arrObjVeiculoPublicacaoDTO[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoPublicacao()]->getStrNome());
            $strStaTipoVeiculoPublicacao = $arrObjVeiculoPublicacaoDTO[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoPublicacao()]->getStrStaTipo();
          } else {
            $strNomeVeiculoPublicacao = '[ve?culo de publica??o n?o encontrado]';
            $strStaTipoVeiculoPublicacao = '[ve?culo de publica??o n?o encontrado]';
          }

          $strSiglaVeiculoImprensaNacional = '';
          $strDescricaoVeiculoImprensaNacional = '';
          if ($objResultadoPublicacaoSolrDTO->getNumIdVeiculoIO() != null) {
            if (isset($arrObjVeiculoImprensaNacionalDTO[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoIO()])) {
              $strSiglaVeiculoImprensaNacional = PaginaPublicacoes::tratarHTML($arrObjVeiculoImprensaNacionalDTO[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoIO()]->getStrSigla());
              $strDescricaoVeiculoImprensaNacional = PaginaPublicacoes::tratarHTML($arrObjVeiculoImprensaNacionalDTO[$objResultadoPublicacaoSolrDTO->getNumIdVeiculoIO()]->getStrDescricao());
            } else {
              $strSiglaVeiculoPublicacao = '[ve?culo de publica??o nacional n?o encontrado]';
              $strDescricaoVeiculoImprensaNacional = '[ve?culo de publica??o nacional n?o encontrado]';
            }
          }

          $strNomeSecaoImprensaNacional = '';
          if ($objResultadoPublicacaoSolrDTO->getNumIdSecaoIO() != null) {
            if (isset($arrObjSecaoImprensaNacionalDTO[$objResultadoPublicacaoSolrDTO->getNumIdSecaoIO()])) {
              $strNomeSecaoImprensaNacional = PaginaPublicacoes::tratarHTML($arrObjSecaoImprensaNacionalDTO[$objResultadoPublicacaoSolrDTO->getNumIdSecaoIO()]->getStrNome());
            } else {
              $strNomeSecaoImprensaNacional = '[se??o do ve?culo de publica??o nacional n?o encontrada.]';
            }
          }

          $strTrClass = ($strTrClass == 'infraTrClara') ? 'infraTrEscura' : 'infraTrClara';
          $html .= '<tr id="trPublicacaoA'.$i.'" class="'.$strTrClass.'">';

          if ($objResultadoPublicacaoSolrDTO->isSetStrSnippet()) {
            $strRowSpanCheck = 'rowspan="2"';
          }

          $numIdTabela = $objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado() != null ? 'legado-'.$objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado() : 'sei-'.$objResultadoPublicacaoSolrDTO->getDblIdDocumento();
          $html .= '<td '.$strRowSpanCheck.' valign="center" class="tdCheck">'.PaginaPublicacoes::getInstance()->getTrCheck($i, $numIdTabela, $objResultadoPublicacaoSolrDTO->getStrProtocoloDocumentoFormatado()).'</td>';
          $html .= '<td align="center" class="tdDados"><a href="'.SessaoPublicacoes::getInstance()->assinarLink('controlador_publicacoes.php?acao=publicacao_visualizar&id_documento='.$objResultadoPublicacaoSolrDTO->getDblIdDocumento()).($objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado() != null ? '&id_publicacao_legado='.$objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado() : '').'" target="_blank" alt="'.$strNomeSerie.'" title="'.$strNomeSerie.'" class="ancoraPadraoAzul">'.$objResultadoPublicacaoSolrDTO->getStrProtocoloDocumentoFormatado().'</a></td>';
          $html .= '<td align="center" class="tdDados">'.$strNomeSerie.' '.$objResultadoPublicacaoSolrDTO->getStrNumero().'</td>';

          $html .= '<td align="center" class="tdDados">'.$strNomeVeiculoPublicacao;
          if (!InfraString::isBolVazia($objResultadoPublicacaoSolrDTO->getNumNumeroPublicacao())) {
            $html .= ' N? '.$objResultadoPublicacaoSolrDTO->getNumNumeroPublicacao();
          }
          $html .= '</td>';


          $html .= '<td align="center" class="tdDados">'.$objResultadoPublicacaoSolrDTO->getDtaPublicacao().'</td>';
          $html .= '<td align="center" class="tdDados"><a alt="'.$strDescricaoUnidadeResponsavel.'" title="'.$strDescricaoUnidadeResponsavel.'" class="ancoraSigla">'.$strSiglaUnidadeResponsavel.'</a></td>';
          $html .= '<td align="center" class="tdDados"><a alt="'.$strDescricaoOrgaoResponsavel.'" title="'.$strDescricaoOrgaoResponsavel.'" class="ancoraSigla">'.$strSiglaOrgaoResponsavel.'</a></td>';

          $html .= '<td align="left" class="tdDados">'.$objResultadoPublicacaoSolrDTO->getStrResumo().'</td>';
          $html .= '<td align="center" class="tdDados">&nbsp;';

          $objPublicacaoDTO = new PublicacaoDTO();
          $objPublicacaoDTO->setStrSiglaVeiculoImprensaNacional($strSiglaVeiculoImprensaNacional);
          $objPublicacaoDTO->setStrDescricaoVeiculoImprensaNacional($strDescricaoVeiculoImprensaNacional);
          $objPublicacaoDTO->setDtaPublicacaoIO($objResultadoPublicacaoSolrDTO->getDtaPublicacaoIO());
          $objPublicacaoDTO->setStrNomeSecaoImprensaNacional($strNomeSecaoImprensaNacional);
          $objPublicacaoDTO->setStrPaginaIO($objResultadoPublicacaoSolrDTO->getStrPaginaIO());
          $objPublicacaoDTO->setStrStaTipoVeiculoPublicacao($strStaTipoVeiculoPublicacao);
          $objPublicacaoDTO->setNumIdPublicacao($objResultadoPublicacaoSolrDTO->getNumIdPublicacao());
          $objPublicacaoDTO->setNumIdVeiculoPublicacao($objResultadoPublicacaoSolrDTO->getNumIdVeiculoPublicacao());
          $objPublicacaoDTO->setStrStaTipoVeiculoPublicacao($strStaTipoVeiculoPublicacao);
          $html .= PublicacaoINT::montarDadosImprensaNacional($objPublicacaoDTO);

          $html .= '</td>';
          $html .= '<td align="center" class="tdDados">&nbsp;';

          if ($objResultadoPublicacaoSolrDTO->getNumIdPublicacao() != null && in_array($objResultadoPublicacaoSolrDTO->getNumIdPublicacao(), $arrIdPublicacaoRelacionada) ||
              $objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado() != null && in_array($objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado(), $arrIdPublicacaoLegadoRelacionada)
          ) {
            $html .= '<a onclick="visualizarPublicacoesRelacionadas(\''.SessaoPublicacoes::getInstance()->assinarLink('controlador_publicacoes.php?acao=publicacao_relacionada_visualizar&id_publicacao='.$objResultadoPublicacaoSolrDTO->getNumIdPublicacao().'&id_publicacao_legado='.$objResultadoPublicacaoSolrDTO->getNumIdPublicacaoLegado().'&id_documento='.$objResultadoPublicacaoSolrDTO->getDblIdDocumento()).'\');" tabindex="'.PaginaPublicacoes::getInstance()->getProxTabTabela().'"><img src="../imagens/relacionada.gif" title="Consultar Publica??es Relacionadas" alt="Consultar Publica??es Relacionadas" class="infraImg" /></a>';
          }

          $html .= '</td>'."\n";
          $html .= '</tr>'."\n";

          if ($objResultadoPublicacaoSolrDTO->isSetStrSnippet()) {
            $html .= '<tr id="trPublicacaoB'.$i.'" class="'.$strTrClass.' trSnippet">'."\n";
            $html .= '<td colspan="9">'.$objResultadoPublicacaoSolrDTO->getStrSnippet().'</td>';
            $html .= '</tr>'."\n";

            $html .= '<tr class="trEspaco"><td colspan="10">&nbsp;</td></tr>'."\n";
          }

          $i++;
        }
        $html .= '</table>';
        $html .= '<div id="divRodape">'.SolrUtil::criarBarraNavegacao($itens, $parametros->start, $parametros->rows).'</div>';
      }
    }

    return $html;
  }
}
?>