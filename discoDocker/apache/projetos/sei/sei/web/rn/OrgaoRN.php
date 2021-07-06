<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 13/10/2009 - criado por mga
*
* Vers�o do Gerador de C�digo: 1.29.1
*
* Vers�o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class OrgaoRN extends InfraRN {
  
  public static $TCO_NENHUM = 'N';
  public static $TCO_LICENCIADO = 'L';
  public static $TCO_NATIVO_NAVEGADOR = 'B';
  
  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function validarStrSiglaRN1346(OrgaoDTO $objOrgaoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objOrgaoDTO->getStrSigla())){
      $objInfraException->adicionarValidacao('Sigla n�o informada.');
    }else{
      $objOrgaoDTO->setStrSigla(trim($objOrgaoDTO->getStrSigla()));

      if (strlen($objOrgaoDTO->getStrSigla())>30){
        $objInfraException->adicionarValidacao('Sigla possui tamanho superior a 30 caracteres.');
      }
    }
  }

  private function validarStrDescricaoRN1347(OrgaoDTO $objOrgaoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objOrgaoDTO->getStrDescricao())){
      $objInfraException->adicionarValidacao('Descri��o n�o informada.');
    }else{
      $objOrgaoDTO->setStrDescricao(trim($objOrgaoDTO->getStrDescricao()));

      if (strlen($objOrgaoDTO->getStrDescricao())>100){
        $objInfraException->adicionarValidacao('Descri��o possui tamanho superior a 100 caracteres.');
      }
    }
  }

  private function validarStrSinAtivoRN1348(OrgaoDTO $objOrgaoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objOrgaoDTO->getStrSinAtivo())){
      $objInfraException->adicionarValidacao('Sinalizador de Exclus�o L�gica n�o informado.');
    }else{
      if (!InfraUtil::isBolSinalizadorValido($objOrgaoDTO->getStrSinAtivo())){
        $objInfraException->adicionarValidacao('Sinalizador de Exclus�o L�gica inv�lido.');
      }
    }
  }
  
  private function validarStrSinEnvioProcesso(OrgaoDTO $objOrgaoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objOrgaoDTO->getStrSinEnvioProcesso())){
      $objInfraException->adicionarValidacao('Sinalizador de Envio de Processo n�o informado.');
    }else{
      if (!InfraUtil::isBolSinalizadorValido($objOrgaoDTO->getStrSinEnvioProcesso())){
        $objInfraException->adicionarValidacao('Sinalizador de Envio de Processo inv�lido.');
      }
    }
  }
  
  private function validarStrSinPublicacao(OrgaoDTO $objOrgaoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objOrgaoDTO->getStrSinPublicacao())){
      $objInfraException->adicionarValidacao('Sinalizador de Publica��o n�o informado.');
    }else{
      if (!InfraUtil::isBolSinalizadorValido($objOrgaoDTO->getStrSinPublicacao())){
        $objInfraException->adicionarValidacao('Sinalizador de Publica��o inv�lido.');
      }
    }
  }

  private function validarStrNomeArquivo(OrgaoDTO $objOrgaoDTO, InfraException $objInfraException){
    if (!InfraString::isBolVazia($objOrgaoDTO->getStrNomeArquivo()) && $objOrgaoDTO->getStrNomeArquivo()!="*REMOVER*"){
      if (!file_exists(DIR_SEI_TEMP.'/'.$objOrgaoDTO->getStrNomeArquivo())) {
        $objInfraException->adicionarValidacao('N�o foi poss�vel abrir arquivo da imagem.');
      }
    }
  }
  
  private function validarStrNumeracao(OrgaoDTO $objOrgaoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objOrgaoDTO->getStrNumeracao())){
      $objOrgaoDTO->setStrNumeracao(null);
    }else{
      $objOrgaoDTO->setStrNumeracao(trim($objOrgaoDTO->getStrNumeracao()));

      if (strlen($objOrgaoDTO->getStrNumeracao())>250){
        $objInfraException->adicionarValidacao('Formato da numera��o possui tamanho superior a 250 caracteres.');
      }
    }
  }

  private function validarStrServidorCorretorOrtografico(OrgaoDTO $objOrgaoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objOrgaoDTO->getStrServidorCorretorOrtografico())){
      $objOrgaoDTO->setStrServidorCorretorOrtografico(null);
    }else{
      $objOrgaoDTO->setStrServidorCorretorOrtografico(trim($objOrgaoDTO->getStrServidorCorretorOrtografico()));
  
      if (strlen($objOrgaoDTO->getStrServidorCorretorOrtografico())>250){
        $objInfraException->adicionarValidacao('Endere�o do servidor de corre��o ortogr�fica possui tamanho superior a 250 caracteres.');
      }
    }
  }
  
  private function validarStrCodigoSei(OrgaoDTO $objOrgaoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objOrgaoDTO->getStrCodigoSei())){
      $objOrgaoDTO->setStrCodigoSei(null);
    }else{
      $objOrgaoDTO->setStrCodigoSei(trim($objOrgaoDTO->getStrCodigoSei()));
  
      if (strlen($objOrgaoDTO->getStrCodigoSei())>10){
        $objInfraException->adicionarValidacao('C�digo SEI possui tamanho superior a 10 caracteres.');
      }
    }
  }
  
  private function validarStrStaCorretorOrtografico(OrgaoDTO $objOrgaoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objOrgaoDTO->getStrStaCorretorOrtografico()) || (
        $objOrgaoDTO->getStrStaCorretorOrtografico()!=self::$TCO_NATIVO_NAVEGADOR &&
        $objOrgaoDTO->getStrStaCorretorOrtografico()!=self::$TCO_NENHUM &&
        $objOrgaoDTO->getStrStaCorretorOrtografico()!=self::$TCO_LICENCIADO )) {
      
        $objInfraException->adicionarValidacao('Corretor Ortogr�fico n�o informado.');
      
    } else if ($objOrgaoDTO->getStrStaCorretorOrtografico()!=self::$TCO_LICENCIADO ) {
      $objOrgaoDTO->setStrServidorCorretorOrtografico(null);
    }
  }

  private function validarStrIdxOrgao(OrgaoDTO $objOrgaoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objOrgaoDTO->getStrIdxOrgao())){
      $objOrgaoDTO->setStrIdxOrgao(null);
    }else{
      $objOrgaoDTO->setStrIdxOrgao(trim($objOrgaoDTO->getStrIdxOrgao()));

      if (strlen($objOrgaoDTO->getStrIdxOrgao())>500){
        $objInfraException->adicionarValidacao('Indexa��o possui tamanho superior a 500 caracteres.');
      }
    }
  }
  
  protected function cadastrarRN1349Controlado(OrgaoDTO $objOrgaoDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('orgao_cadastrar',__METHOD__,$objOrgaoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarStrSiglaRN1346($objOrgaoDTO, $objInfraException);
      $this->validarStrDescricaoRN1347($objOrgaoDTO, $objInfraException);
      $this->validarStrSinAtivoRN1348($objOrgaoDTO, $objInfraException);
      $this->validarStrSinEnvioProcesso($objOrgaoDTO, $objInfraException);
      $this->validarStrSinPublicacao($objOrgaoDTO, $objInfraException);

      if ($objOrgaoDTO->isSetStrNomeArquivo()) {
        $this->validarStrNomeArquivo($objOrgaoDTO, $objInfraException);
      }
      
      if ($objOrgaoDTO->isSetStrNumeracao()) {
        $this->validarStrNumeracao($objOrgaoDTO, $objInfraException);
      }

      if ($objOrgaoDTO->isSetStrServidorCorretorOrtografico()) {
        $this->validarStrServidorCorretorOrtografico($objOrgaoDTO, $objInfraException);
      }

      if ($objOrgaoDTO->isSetStrCodigoSei()) {
        $this->validarStrCodigoSei($objOrgaoDTO, $objInfraException);
      }
      
      $objInfraException->lancarValidacoes();
      
      if ($objOrgaoDTO->isSetStrNomeArquivo() && !InfraString::isBolVazia($objOrgaoDTO->getStrNomeArquivo())) {
        $objOrgaoDTO->setStrTimbre(base64_encode(file_get_contents(DIR_SEI_TEMP.'/'.$objOrgaoDTO->getStrNomeArquivo())));
      }
      
      $objOrgaoDTO->setStrStaCorretorOrtografico(self::$TCO_NENHUM);

      $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
      $numIdTipoContato = $objInfraParametro->getValor('ID_TIPO_CONTATO_ORGAOS');

      $objContatoRN = new ContatoRN();

      $objContatoDTO = new ContatoDTO();
      $objContatoDTO->setBolExclusaoLogica(false);
      $objContatoDTO->retNumIdContato();
      $objContatoDTO->retStrSinAtivo();
      $objContatoDTO->setStrSigla($objOrgaoDTO->getStrSigla());
      $objContatoDTO->setStrNome($objOrgaoDTO->getStrDescricao());
      $objContatoDTO->setNumIdTipoContato($numIdTipoContato);
      $objContatoDTO = $objContatoRN->consultarRN0324($objContatoDTO);

      if ($objContatoDTO == null) {

        $objContatoDTO = new ContatoDTO();
        $objContatoDTO->setNumIdContato(null);
        $objContatoDTO->setNumIdTipoContato($numIdTipoContato);
        $objContatoDTO->setNumIdContatoAssociado(null);
        $objContatoDTO->setStrStaNatureza(ContatoRN::$TN_PESSOA_JURIDICA);
        $objContatoDTO->setDblCnpj(null);
        $objContatoDTO->setNumIdCargo(null);
        $objContatoDTO->setStrSigla($objOrgaoDTO->getStrSigla());
        $objContatoDTO->setStrNome($objOrgaoDTO->getStrDescricao());
        $objContatoDTO->setDtaNascimento(null);
        $objContatoDTO->setStrStaGenero(null);
        $objContatoDTO->setDblCpf(null);
        $objContatoDTO->setDblRg(null);
        $objContatoDTO->setStrOrgaoExpedidor(null);
        $objContatoDTO->setStrMatricula(null);
        $objContatoDTO->setStrMatriculaOab(null);
        $objContatoDTO->setStrEndereco(null);
        $objContatoDTO->setStrComplemento(null);
        $objContatoDTO->setStrEmail(null);
        $objContatoDTO->setStrSitioInternet(null);
        $objContatoDTO->setStrTelefoneFixo(null);
        $objContatoDTO->setStrTelefoneCelular(null);
        $objContatoDTO->setStrBairro(null);
        $objContatoDTO->setNumIdUf(null);
        $objContatoDTO->setNumIdCidade(null);
        $objContatoDTO->setNumIdPais(null);
        $objContatoDTO->setStrCep(null);
        $objContatoDTO->setStrObservacao(null);
        $objContatoDTO->setStrSinEnderecoAssociado('N');
        $objContatoDTO->setStrSinAtivo('S');
        $objContatoDTO->setStrStaOperacao('REPLICACAO');
        $objContatoDTO->setNumIdPaisPassaporte(null);
        $objContatoDTO->setStrNumeroPassaporte(null);
        //////////////////
        $objContatoDTO = $objContatoRN->cadastrarRN0322($objContatoDTO);
      }else{
        if ($objContatoDTO->getStrSinAtivo()=='N'){
          $objContatoRN->reativarRN0452(array($objContatoDTO));
        }
      }

      $objOrgaoDTO->setNumIdContato($objContatoDTO->getNumIdContato());
      $objOrgaoDTO->setStrIdxOrgao(null);

      $objOrgaoBD = new OrgaoBD($this->getObjInfraIBanco());
      $ret = $objOrgaoBD->cadastrar($objOrgaoDTO);

      $this->montarIndexacao($ret);

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando �rg�o.',$e);
    }
  }

  protected function alterarRN1350Controlado(OrgaoDTO $objOrgaoDTO){
    try {

      //Valida Permissao
  	   SessaoSEI::getInstance()->validarAuditarPermissao('orgao_alterar',__METHOD__,$objOrgaoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $objOrgaoDTOBanco = new OrgaoDTO();
      $objOrgaoDTOBanco->setBolExclusaoLogica(false);
      $objOrgaoDTOBanco->retNumIdContato();
      $objOrgaoDTOBanco->retStrSigla();
      $objOrgaoDTOBanco->retStrDescricao();
      $objOrgaoDTOBanco->setNumIdOrgao($objOrgaoDTO->getNumIdOrgao());

      $objOrgaoDTOBanco = $this->consultarRN1352($objOrgaoDTOBanco);


      if($objOrgaoDTOBanco->isSetNumIdContato() && $objOrgaoDTOBanco->getNumIdContato()!=$objOrgaoDTOBanco->getNumIdContato()){
        $objInfraException->lancarValidacao('N�o � poss�vel alterar o contato associado.');
      }else{
        $objOrgaoDTO->setNumIdContato($objOrgaoDTOBanco->getNumIdContato());
      }

      if ($objOrgaoDTO->isSetStrSigla()){
        $this->validarStrSiglaRN1346($objOrgaoDTO, $objInfraException);
      }else{
        $objOrgaoDTO->setStrSigla($objOrgaoDTOBanco->getStrSigla());
      }

      if ($objOrgaoDTO->isSetStrDescricao()){
        $this->validarStrDescricaoRN1347($objOrgaoDTO, $objInfraException);
      }else{
        $objOrgaoDTO->setStrDescricao($objOrgaoDTOBanco->getStrDescricao());
      }

      if ($objOrgaoDTO->isSetStrSinEnvioProcesso()){
        $this->validarStrSinEnvioProcesso($objOrgaoDTO, $objInfraException);
      }

      if ($objOrgaoDTO->isSetStrSinPublicacao()){
        $this->validarStrSinPublicacao($objOrgaoDTO, $objInfraException);
      }

      if ($objOrgaoDTO->isSetStrNumeracao()) {
        $this->validarStrNumeracao($objOrgaoDTO, $objInfraException);
      }
      
      if ($objOrgaoDTO->isSetStrServidorCorretorOrtografico()) {
        $this->validarStrServidorCorretorOrtografico($objOrgaoDTO, $objInfraException);
      }
      
      if ($objOrgaoDTO->isSetStrCodigoSei()) {
        $this->validarStrCodigoSei($objOrgaoDTO, $objInfraException);
      }
      
      if ($objOrgaoDTO->isSetStrNomeArquivo()) {
        $this->validarStrNomeArquivo($objOrgaoDTO, $objInfraException);
      }

      if ($objOrgaoDTO->isSetStrStaCorretorOrtografico()) {
        $this->validarStrStaCorretorOrtografico($objOrgaoDTO, $objInfraException);
      }

      if ($objOrgaoDTO->isSetStrSinAtivo()){
        $objOrgaoDTO->unSetStrSinAtivo();
      }

      $objInfraException->lancarValidacoes();

      
      if ($objOrgaoDTO->isSetStrNomeArquivo() && !InfraString::isBolVazia($objOrgaoDTO->getStrNomeArquivo())) {
        if ($objOrgaoDTO->getStrNomeArquivo()=="*REMOVER*") {
          $objOrgaoDTO->setStrTimbre(null);
        } else {
          $objOrgaoDTO->setStrTimbre(base64_encode(file_get_contents(DIR_SEI_TEMP.'/'.$objOrgaoDTO->getStrNomeArquivo())));
        }
      }

      if ($objOrgaoDTO->isSetStrIdxOrgao()){
        $objOrgaoDTO->unSetStrIdxOrgao();
      }

      $objOrgaoBD = new OrgaoBD($this->getObjInfraIBanco());
      $objOrgaoBD->alterar($objOrgaoDTO);

      $this->montarIndexacao($objOrgaoDTO);

      $objContatoDTO = new ContatoDTO();
      $objContatoDTO->setStrSigla($objOrgaoDTO->getStrSigla());
      $objContatoDTO->setStrNome($objOrgaoDTO->getStrDescricao());
      $objContatoDTO->setNumIdContato($objOrgaoDTO->getNumIdContato());
      $objContatoDTO->setStrStaOperacao('REPLICACAO');

      $objContatoRN = new ContatoRN();
      $objContatoRN->alterarRN0323($objContatoDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando �rg�o.',$e);
    }
  }

  protected function excluirRN1351Controlado($arrObjOrgaoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('orgao_excluir',__METHOD__,$arrObjOrgaoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      
      $objNumeracaoRN = new NumeracaoRN(); 
      for($i=0;$i<count($arrObjOrgaoDTO);$i++){
	      $objNumeracaoDTO = new NumeracaoDTO();
	      $objNumeracaoDTO->retNumIdNumeracao();
	      $objNumeracaoDTO->setNumIdOrgao($arrObjOrgaoDTO[$i]->getNumIdOrgao());
	      $objNumeracaoRN->excluir($objNumeracaoRN->listar($objNumeracaoDTO));
      }


      $objOrgaoDTO = new OrgaoDTO();
      $objOrgaoDTO->setBolExclusaoLogica(false);
      $objOrgaoDTO->retNumIdContato();
      $objOrgaoDTO->setNumIdOrgao(InfraArray::converterArrInfraDTO($arrObjOrgaoDTO,'IdOrgao'),InfraDTO::$OPER_IN);
      $arrNumIdContato = InfraArray::converterArrInfraDTO($this->listarRN1353($objOrgaoDTO),'IdContato');

      $objOrgaoBD = new OrgaoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjOrgaoDTO);$i++){
        $objOrgaoBD->excluir($arrObjOrgaoDTO[$i]);
      }

      $objContatoRN = new ContatoRN();
      foreach($arrNumIdContato as $numIdContato){
        $objContatoDTO = new ContatoDTO();
        $objContatoDTO->setNumIdContato($numIdContato);
        try{
          $objContatoRN->excluirRN0326(array($objContatoDTO));
        }catch(Exception $e){
          $objContatoRN->desativarRN0451(array($objContatoDTO));
        }
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo �rg�o.',$e);
    }
  }

  protected function consultarRN1352Conectado(OrgaoDTO $objOrgaoDTO){
    try {

      //Valida Permissao
      //SessaoSEI::getInstance()->validarAuditarPermissao('orgao_consultar'); //nao valida para montar nos formularios

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();
      
      $objOrgaoBD = new OrgaoBD($this->getObjInfraIBanco());
      $ret = $objOrgaoBD->consultar($objOrgaoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando �rg�o.',$e);
    }
  }

  protected function listarRN1353Conectado(OrgaoDTO $objOrgaoDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('orgao_listar',__METHOD__,$objOrgaoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objOrgaoBD = new OrgaoBD($this->getObjInfraIBanco());
      $ret = $objOrgaoBD->listar($objOrgaoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando �rg�os.',$e);
    }
  }

  protected function contarRN1354Conectado(OrgaoDTO $objOrgaoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('orgao_listar',__METHOD__,$objOrgaoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objOrgaoBD = new OrgaoBD($this->getObjInfraIBanco());
      $ret = $objOrgaoBD->contar($objOrgaoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando �rg�os.',$e);
    }
  }

  protected function desativarRN1355Controlado($arrObjOrgaoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('orgao_desativar',__METHOD__,$arrObjOrgaoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objOrgaoBD = new OrgaoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjOrgaoDTO);$i++){
        $objOrgaoBD->desativar($arrObjOrgaoDTO[$i]);
      }

      $objOrgaoDTO = new OrgaoDTO();
      $objOrgaoDTO->setBolExclusaoLogica(false);
      $objOrgaoDTO->retNumIdContato();
      $objOrgaoDTO->setNumIdOrgao(InfraArray::converterArrInfraDTO($arrObjOrgaoDTO,'IdOrgao'),InfraDTO::$OPER_IN);
      $objContatoRN = new ContatoRN();
      $objContatoRN->desativarRN0451(InfraArray::gerarArrInfraDTO('ContatoDTO','IdContato',InfraArray::converterArrInfraDTO($this->listarRN1353($objOrgaoDTO),'IdContato')));

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando �rg�o.',$e);
    }
  }

  protected function reativarRN1356Controlado($arrObjOrgaoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('orgao_reativar',__METHOD__,$arrObjOrgaoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objOrgaoBD = new OrgaoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjOrgaoDTO);$i++){
        $objOrgaoBD->reativar($arrObjOrgaoDTO[$i]);
      }

      $objOrgaoDTO = new OrgaoDTO();
      $objOrgaoDTO->setBolExclusaoLogica(false);
      $objOrgaoDTO->retNumIdContato();
      $objOrgaoDTO->setNumIdOrgao(InfraArray::converterArrInfraDTO($arrObjOrgaoDTO,'IdOrgao'),InfraDTO::$OPER_IN);
      $objContatoRN = new ContatoRN();
      $objContatoRN->reativarRN0452(InfraArray::gerarArrInfraDTO('ContatoDTO','IdContato',InfraArray::converterArrInfraDTO($this->listarRN1353($objOrgaoDTO),'IdContato')));

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando �rg�o.',$e);
    }
  }

  protected function bloquearRN1357Controlado(OrgaoDTO $objOrgaoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('orgao_consultar',__METHOD__,$objOrgaoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objOrgaoBD = new OrgaoBD($this->getObjInfraIBanco());
      $ret = $objOrgaoBD->bloquear($objOrgaoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando �rg�o.',$e);
    }
  }

  protected function montarIndexacaoControlado(OrgaoDTO $parObjOrgaoDTO){
    try{

      $objOrgaoDTO = new OrgaoDTO();
      $objOrgaoDTO->setBolExclusaoLogica(false);
      $objOrgaoDTO->retStrSigla();
      $objOrgaoDTO->retStrDescricao();
      $objOrgaoDTO->setNumIdOrgao($parObjOrgaoDTO->getNumIdOrgao());

      $objOrgaoDTO = $this->consultarRN1352($objOrgaoDTO);

      $strIndexacao = InfraString::prepararIndexacao($objOrgaoDTO->getStrSigla().' '.$objOrgaoDTO->getStrDescricao(), false);

      $objOrgaoDTO = new OrgaoDTO();
      $objOrgaoDTO->setStrIdxOrgao($strIndexacao);
      $objOrgaoDTO->setNumIdOrgao($parObjOrgaoDTO->getNumIdOrgao());

      $objInfraException = new InfraException();
      $this->validarStrIdxOrgao($objOrgaoDTO, $objInfraException);
      $objInfraException->lancarValidacoes();

      $objOrgaoBD = new OrgaoBD($this->getObjInfraIBanco());
      $objOrgaoBD->alterar($objOrgaoDTO);


    }catch(Exception $e){
      throw new InfraException('Erro montando indexa��o de �rgao.',$e);
    }
  }

  protected function pesquisarConectado(OrgaoDTO $objOrgaoDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('orgao_listar',__METHOD__,$objOrgaoDTO);

      if ($objOrgaoDTO->isSetStrSigla()){
        $objOrgaoDTO->setStrSigla('%'.trim($objOrgaoDTO->getStrSigla()).'%',InfraDTO::$OPER_LIKE);
      }

      if ($objOrgaoDTO->isSetStrDescricao()){
        if (trim($objOrgaoDTO->getStrDescricao())!=''){
          $strPalavrasPesquisa = InfraString::prepararPesquisa($objOrgaoDTO->getStrDescricao());
          $arrPalavrasPesquisa = explode(' ',$strPalavrasPesquisa);

          for($i=0;$i<count($arrPalavrasPesquisa);$i++){
            $arrPalavrasPesquisa[$i] = '%'.$arrPalavrasPesquisa[$i].'%';
          }

          if (count($arrPalavrasPesquisa)==1){
            $objOrgaoDTO->setStrDescricao($arrPalavrasPesquisa[0],InfraDTO::$OPER_LIKE);
          }else{
            $objOrgaoDTO->unSetStrDescricao();
            $a = array_fill(0,count($arrPalavrasPesquisa),'Descricao');
            $b = array_fill(0,count($arrPalavrasPesquisa),InfraDTO::$OPER_LIKE);
            $d = array_fill(0,count($arrPalavrasPesquisa)-1,InfraDTO::$OPER_LOGICO_AND);
            $objOrgaoDTO->adicionarCriterio($a,$b,$arrPalavrasPesquisa,$d);
          }

        }
      }

      if ($objOrgaoDTO->isSetStrPalavrasPesquisa()){

        if (!InfraString::isBolVazia($objOrgaoDTO->getStrPalavrasPesquisa())){

          $strPalavrasPesquisa = InfraString::prepararIndexacao($objOrgaoDTO->getStrPalavrasPesquisa(),false);

          $arrPalavrasPesquisa = explode(' ',$strPalavrasPesquisa);

          $numPalavrasPesquisa = count($arrPalavrasPesquisa);

          if ($numPalavrasPesquisa){

            for($i=0;$i<$numPalavrasPesquisa;$i++){
              $arrPalavrasPesquisa[$i] = '%'.$arrPalavrasPesquisa[$i].'%';
            }

            if ($numPalavrasPesquisa==1){
              $objOrgaoDTO->setStrIdxOrgao($arrPalavrasPesquisa[0],InfraDTO::$OPER_LIKE);
            }else{
              $a = array_fill(0,$numPalavrasPesquisa,'IdxOrgao');
              $b = array_fill(0,$numPalavrasPesquisa,InfraDTO::$OPER_LIKE);
              $d = array_fill(0,$numPalavrasPesquisa-1,InfraDTO::$OPER_LOGICO_AND);
              $objOrgaoDTO->adicionarCriterio($a,$b,$arrPalavrasPesquisa,$d);
            }
          }
        }
      }

      return $this->listarRN1353($objOrgaoDTO);

    }catch(Exception $e){
      throw new InfraException('Erro pesquisando �rg�os.',$e);
    }
  }
}
?>