<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 17/12/2007 - criado por fbv
*
* Vers?o do Gerador de C?digo: 1.10.1
*
* Vers?o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class TipoContatoRN extends InfraRN {

  public static $TA_NENHUM = 'N';
  public static $TA_ALTERACAO = 'A';
  public static $TA_CONSULTA_COMPLETA = 'C';
  public static $TA_CONSULTA_RESUMIDA = 'R';

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }

  private function validarStrNomeRN0355(TipoContatoDTO $objTipoContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objTipoContatoDTO->getStrNome())){
      $objInfraException->adicionarValidacao('Nome n?o informado.');
    }else{
      $objTipoContatoDTO->setStrNome(trim($objTipoContatoDTO->getStrNome()));

      if (strlen($objTipoContatoDTO->getStrNome())>50){
        $objInfraException->adicionarValidacao('Nome possui tamanho superior a 50 caracteres.');
      }

      $dto = new TipoContatoDTO();
      $dto->retStrSinAtivo();
      $dto->setNumIdTipoContato($objTipoContatoDTO->getNumIdTipoContato(),InfraDTO::$OPER_DIFERENTE);
      $dto->setStrNome($objTipoContatoDTO->getStrNome());
      $dto->setBolExclusaoLogica(false);

      $dto = $this->consultarRN0336($dto);
      if ($dto != NULL){
        if ($dto->getStrSinAtivo() == 'S')
          $objInfraException->adicionarValidacao('Existe outra ocorr?ncia de Tipo de Contato que utiliza o mesmo Nome.');
        else
          $objInfraException->adicionarValidacao('Existe ocorr?ncia inativa de Tipo de Contato que utiliza o mesmo Nome.');
      }
    }
  }

  private function validarStrDescricaoRN0356(TipoContatoDTO $objTipoContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objTipoContatoDTO->getStrDescricao())){
      $objTipoContatoDTO->setStrDescricao(null);
    }else{
      $objTipoContatoDTO->setStrDescricao(trim($objTipoContatoDTO->getStrDescricao()));

      if (strlen($objTipoContatoDTO->getStrDescricao())>250){
        $objInfraException->adicionarValidacao('Descri??o possui tamanho superior a 250 caracteres.');
      }
    }
  }

  private function validarStrStaAcesso(TipoContatoDTO $objTipoContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objTipoContatoDTO->getStrStaAcesso())){
      $objInfraException->adicionarValidacao('Tipo de acesso n?o informado.');
    }else{
      if ($objTipoContatoDTO->getStrStaAcesso()!=self::$TA_NENHUM &&
          $objTipoContatoDTO->getStrStaAcesso()!=self::$TA_CONSULTA_COMPLETA &&
          $objTipoContatoDTO->getStrStaAcesso()!=self::$TA_CONSULTA_RESUMIDA) {
        $objInfraException->adicionarValidacao('Tipo de acesso inv?lido.');
      }
    }
  }

  private function validarStrSinSistema(TipoContatoDTO $objTipoContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objTipoContatoDTO->getStrSinSistema())){
      $objInfraException->adicionarValidacao('Sinalizador de sistema n?o informado.');
    }else{
      if (!InfraUtil::isBolSinalizadorValido($objTipoContatoDTO->getStrSinSistema())){
        $objInfraException->adicionarValidacao('Sinalizador de sistema inv?lido.');
      }
    }
  }

  private function validarStrSinAtivoRN0357(TipoContatoDTO $objTipoContatoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objTipoContatoDTO->getStrSinAtivo())){
      $objInfraException->adicionarValidacao('Sinalizador de Exclus?o L?gica n?o informado.');
    }else{
      if (!InfraUtil::isBolSinalizadorValido($objTipoContatoDTO->getStrSinAtivo())){
        $objInfraException->adicionarValidacao('Sinalizador de Exclus?o L?gica inv?lido.');
      }
    }
  }

  protected function cadastrarRN0334Controlado(TipoContatoDTO $objTipoContatoDTO) {
    try{
      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('tipo_contato_cadastrar',__METHOD__,$objTipoContatoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarStrNomeRN0355($objTipoContatoDTO, $objInfraException);
      $this->validarStrDescricaoRN0356($objTipoContatoDTO, $objInfraException);
      $this->validarStrStaAcesso($objTipoContatoDTO, $objInfraException);
      $this->validarStrSinSistema($objTipoContatoDTO, $objInfraException);
      $this->validarStrSinAtivoRN0357($objTipoContatoDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

      $objTipoContatoBD = new TipoContatoBD($this->getObjInfraIBanco());
      $ret = $objTipoContatoBD->cadastrar($objTipoContatoDTO);

      if ($objTipoContatoDTO->isSetArrObjRelUnidadeTipoContatoDTO()){
        $objRelUnidadeTipoContatoRN = new RelUnidadeTipoContatoRN(); 
        foreach($objTipoContatoDTO->getArrObjRelUnidadeTipoContatoDTO() as $objRelUnidadeTipoContatoDTO){
          $objRelUnidadeTipoContatoDTO->setNumIdRelUnidadeTipoContato(null);
          $objRelUnidadeTipoContatoDTO->setNumIdTipoContato($ret->getNumIdTipoContato());
          $objRelUnidadeTipoContatoRN->cadastrarRN0545($objRelUnidadeTipoContatoDTO);
        }
      }
      
      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Tipo de Contato.',$e);
    }
  }

  protected function alterarRN0335Controlado(TipoContatoDTO $objTipoContatoDTO){
    try {

      //Valida Permissao
  	   SessaoSEI::getInstance()->validarAuditarPermissao('tipo_contato_alterar',__METHOD__,$objTipoContatoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $objTipoContatoDTOBanco = new TipoContatoDTO();
      $objTipoContatoDTOBanco->setBolExclusaoLogica(false);
      $objTipoContatoDTOBanco->retStrSinSistema();
      $objTipoContatoDTOBanco->setNumIdTipoContato($objTipoContatoDTO->getNumIdTipoContato());
      $objTipoContatoDTOBanco = $this->consultarRN0336($objTipoContatoDTOBanco);

      if ($objTipoContatoDTOBanco==null){
        throw new InfraException('Tipo de Contato n?o encontrado ['.$objTipoContatoDTO->getNumIdTipoContato().'].');
      }

      if ($objTipoContatoDTO->isSetStrNome()){
        $this->validarStrNomeRN0355($objTipoContatoDTO, $objInfraException);
      }
      if ($objTipoContatoDTO->isSetStrDescricao()){
        $this->validarStrDescricaoRN0356($objTipoContatoDTO, $objInfraException);
      }

      if ($objTipoContatoDTO->isSetStrStaAcesso()){
        $this->validarStrStaAcesso($objTipoContatoDTO, $objInfraException);
      }

      if ($objTipoContatoDTO->isSetStrSinSistema() && $objTipoContatoDTO->getStrSinSistema()!=$objTipoContatoDTOBanco->getStrSinSistema()) {
        $objInfraException->lancarValidacao('N?o ? poss?vel alterar o sinalizador de sistema.');
      }

      if ($objTipoContatoDTO->isSetStrSinAtivo()){
        $this->validarStrSinAtivoRN0357($objTipoContatoDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();
      
      if ($objTipoContatoDTO->isSetArrObjRelUnidadeTipoContatoDTO()){
        $objRelUnidadeTipoContatoRN = new RelUnidadeTipoContatoRN();

        $objRelUnidadeTipoContatoDTO = new RelUnidadeTipoContatoDTO();
        $objRelUnidadeTipoContatoDTO->retNumIdRelUnidadeTipoContato();
        $objRelUnidadeTipoContatoDTO->setNumIdTipoContato($objTipoContatoDTO->getNumIdTipoContato());
        $objRelUnidadeTipoContatoRN->excluirRN0546($objRelUnidadeTipoContatoRN->listarRN0547($objRelUnidadeTipoContatoDTO));
        
        foreach($objTipoContatoDTO->getArrObjRelUnidadeTipoContatoDTO() as $objRelUnidadeTipoContatoDTO){
          $objRelUnidadeTipoContatoDTO->setNumIdRelUnidadeTipoContato(null);
          $objRelUnidadeTipoContatoDTO->setNumIdTipoContato($objTipoContatoDTO->getNumIdTipoContato());
          $objRelUnidadeTipoContatoRN->cadastrarRN0545($objRelUnidadeTipoContatoDTO);
        }
      }

      $objTipoContatoBD = new TipoContatoBD($this->getObjInfraIBanco());
      $objTipoContatoBD->alterar($objTipoContatoDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Tipo de Contato.',$e);
    }
  }

  protected function excluirRN0338Controlado($arrObjTipoContatoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('tipo_contato_excluir',__METHOD__,$arrObjTipoContatoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();
      
      
      $objContatoRN = new ContatoRN();
      $objContatoDTO = new ContatoDTO();
      
      $objRelUnidadeTipoContatoRN = new RelUnidadeTipoContatoRN();
      
      $objContatoDTO->setBolExclusaoLogica(false);
      for ($i=0;$i<count($arrObjTipoContatoDTO);$i++){
        
        $objTipoContatoDTO = new TipoContatoDTO();
        $objTipoContatoDTO->setBolExclusaoLogica(false);
        $objTipoContatoDTO->retStrNome();
        $objTipoContatoDTO->setNumIdTipoContato($arrObjTipoContatoDTO[$i]->getNumIdTipoContato());
        $objTipoContatoDTO = $this->consultarRN0336($objTipoContatoDTO);
        
      	$objContatoDTO->setNumIdTipoContato($arrObjTipoContatoDTO[$i]->getNumIdTipoContato());
      	$objContatoDTO->setStrSinAtivo('S');
      	if ($objContatoRN->contarRN0327($objContatoDTO)){
      		$objInfraException->adicionarValidacao('Existem contatos do tipo '.$objTipoContatoDTO->getStrNome().'.');
      	}
     	
 	      $objContatoDTO->setStrSinAtivo('N');
 	      if ($objContatoRN->contarRN0327($objContatoDTO)){
          $objInfraException->adicionarValidacao('Existem contatos inativos do tipo '.$objTipoContatoDTO->getStrNome().'.');
 	      }
 	      
 	      $objRelUnidadeTipoContatoDTO = new RelUnidadeTipoContatoDTO();
        $objRelUnidadeTipoContatoDTO->retNumIdRelUnidadeTipoContato();
 	      $objRelUnidadeTipoContatoDTO->setNumIdTipoContato($arrObjTipoContatoDTO[$i]->getNumIdTipoContato());
 	      $objRelUnidadeTipoContatoRN->excluirRN0546($objRelUnidadeTipoContatoRN->listarRN0547($objRelUnidadeTipoContatoDTO));
      }
      
      $objInfraException->lancarValidacoes();

      $objTipoContatoBD = new TipoContatoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjTipoContatoDTO);$i++){
        $objTipoContatoBD->excluir($arrObjTipoContatoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo Tipo de Contato.',$e);
    }
  }

  protected function consultarRN0336Conectado(TipoContatoDTO $objTipoContatoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('tipo_contato_consultar',__METHOD__,$objTipoContatoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objTipoContatoBD = new TipoContatoBD($this->getObjInfraIBanco());
      $ret = $objTipoContatoBD->consultar($objTipoContatoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando Tipo de Contato.',$e);
    }
  }

  protected function listarRN0337Conectado(TipoContatoDTO $objTipoContatoDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('tipo_contato_listar',__METHOD__,$objTipoContatoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objTipoContatoBD = new TipoContatoBD($this->getObjInfraIBanco());
      $ret = $objTipoContatoBD->listar($objTipoContatoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Tipos de Contato.',$e);
    }
  }

  protected function contarRN0353Conectado(TipoContatoDTO $objTipoContatoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('tipo_contato_listar',__METHOD__,$objTipoContatoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objTipoContatoBD = new TipoContatoBD($this->getObjInfraIBanco());
      $ret = $objTipoContatoBD->contar($objTipoContatoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando Tipos de Contato.',$e);
    }
  }

  protected function desativarRN0339Controlado($arrObjTipoContatoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('tipo_contato_desativar',__METHOD__,$arrObjTipoContatoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objTipoContatoBD = new TipoContatoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjTipoContatoDTO);$i++){
        $objTipoContatoBD->desativar($arrObjTipoContatoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Tipo de Contato.',$e);
    }
  }

  protected function reativarRN0354Controlado($arrObjTipoContatoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('tipo_contato_reativar',__METHOD__,$arrObjTipoContatoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objTipoContatoBD = new TipoContatoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjTipoContatoDTO);$i++){
        $objTipoContatoBD->reativar($arrObjTipoContatoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Tipo de Contato.',$e);
    }
  }

  protected function pesquisarAcessoUnidadeConectado(PesquisaTipoContatoDTO $objPesquisaTipoContatoDTO){
    try {

      $ret = array();


      //TipoContatoRN::$TA_CONSULTA_RESUMIDA < TipoContatoRN::$TA_CONSULTA_COMPLETA < TipoContatoRN::$TA_ALTERACAO

      $strStaAcesso = $objPesquisaTipoContatoDTO->getStrStaAcesso();

      if ($strStaAcesso != TipoContatoRN::$TA_NENHUM) {

        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->setBolExclusaoLogica(false);
        $objUnidadeDTO->retStrSinProtocolo();
        $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

        $objUnidadeRN = new UnidadeRN();
        $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

        if ($objUnidadeDTO->getStrSinProtocolo()=='S'){
          $objTipoContatoDTO = new TipoContatoDTO();
          $objTipoContatoDTO->setBolExclusaoLogica(false);
          $objTipoContatoDTO->retNumIdTipoContato();
          $objTipoContatoDTO->setStrSinSistema('S');
          $arrObjTipoContatoDTO = $this->listarRN0337($objTipoContatoDTO);

          foreach ($arrObjTipoContatoDTO as $objTipoContatoDTO) {
            $ret[] = $objTipoContatoDTO->getNumIdTipoContato();
          }
        }


        if ($strStaAcesso == TipoContatoRN::$TA_CONSULTA_RESUMIDA || $strStaAcesso == TipoContatoRN::$TA_CONSULTA_COMPLETA) {

          $objTipoContatoDTO = new TipoContatoDTO();
          $objTipoContatoDTO->setBolExclusaoLogica(false);
          $objTipoContatoDTO->retNumIdTipoContato();

          if ($strStaAcesso == TipoContatoRN::$TA_CONSULTA_RESUMIDA) {
            $objTipoContatoDTO->setStrStaAcesso(array(TipoContatoRN::$TA_CONSULTA_RESUMIDA, TipoContatoRN::$TA_CONSULTA_COMPLETA), InfraDTO::$OPER_IN);
          } else {
            $objTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_COMPLETA);
          }

          if ($objPesquisaTipoContatoDTO->isSetArrIdTipoContato()) {
            $objTipoContatoDTO->setNumIdTipoContato($objPesquisaTipoContatoDTO->getArrIdTipoContato(), InfraDTO::$OPER_IN);
          }

          $arrObjTipoContatoDTO = $this->listarRN0337($objTipoContatoDTO);

          foreach ($arrObjTipoContatoDTO as $objTipoContatoDTO) {
            $ret[] = $objTipoContatoDTO->getNumIdTipoContato();
          }
        }

        $objRelUnidadeTipoContatoDTO = new RelUnidadeTipoContatoDTO();
        $objRelUnidadeTipoContatoDTO->retNumIdTipoContato();

        if ($strStaAcesso == TipoContatoRN::$TA_CONSULTA_RESUMIDA || $strStaAcesso == TipoContatoRN::$TA_CONSULTA_COMPLETA) {
          $objRelUnidadeTipoContatoDTO->setStrStaAcesso(array(TipoContatoRN::$TA_CONSULTA_COMPLETA,TipoContatoRN::$TA_ALTERACAO),InfraDTO::$OPER_IN);
        }else {
          $objRelUnidadeTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_ALTERACAO);
        }

        if ($objPesquisaTipoContatoDTO->isSetArrIdTipoContato()) {
          $objRelUnidadeTipoContatoDTO->setNumIdTipoContato($objPesquisaTipoContatoDTO->getArrIdTipoContato(), InfraDTO::$OPER_IN);
        }

        $objRelUnidadeTipoContatoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objRelUnidadeTipoContatoRN = new RelUnidadeTipoContatoRN();
        $arrObjRelUnidadeTipoContatoDTO = $objRelUnidadeTipoContatoRN->listarRN0547($objRelUnidadeTipoContatoDTO);

        foreach($arrObjRelUnidadeTipoContatoDTO as $objRelUnidadeTipoContatoDTO){
          $ret[] = $objRelUnidadeTipoContatoDTO->getNumIdTipoContato();
        }

        $ret = array_unique($ret);
      }

      return $ret;

    } catch (Exception $e) {
      throw new InfraException('Erro pesquisando acesso para Tipos de Contato.', $e);
    }
  }
}
?>