<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 26/08/2010 - criado por jonatas_db
*
* Vers?o do Gerador de C?digo: 1.30.0
*
* Vers?o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class RetornoProgramadoRN extends InfraRN {

  public function __construct(){
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco(){
    return BancoSEI::getInstance();
  }
  
  private function validarNumIdUnidade(RetornoProgramadoDTO $objRetornoProgramadoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRetornoProgramadoDTO->getNumIdUnidade())){
      $objInfraException->adicionarValidacao('Unidade n?o informada.');
    }
  }

  private function validarNumIdAtividadeEnvio(RetornoProgramadoDTO $objRetornoProgramadoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRetornoProgramadoDTO->getNumIdAtividadeEnvio())){
      $objInfraException->adicionarValidacao('Atividade de Envio n?o informada.');
    }
  }
  
  private function validarNumIdAtividadeRetorno(RetornoProgramadoDTO $objRetornoProgramadoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRetornoProgramadoDTO->getNumIdAtividadeRetorno())){
      $objInfraException->adicionarValidacao('Atividade de Retorno n?o informada.');
    }
  }

  private function validarNumIdUsuario(RetornoProgramadoDTO $objRetornoProgramadoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRetornoProgramadoDTO->getNumIdUsuario())){
      $objInfraException->adicionarValidacao('Usu?rio n?o informado.');
    }
  }

  private function validarDtaProgramada(RetornoProgramadoDTO $objRetornoProgramadoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRetornoProgramadoDTO->getDtaProgramada())){
      $objInfraException->adicionarValidacao('Data Programada n?o informada.');
    }else{
      if (!InfraData::validarData($objRetornoProgramadoDTO->getDtaProgramada())){
        $objInfraException->adicionarValidacao('Data Programada inv?lida.');
      }

      if (InfraData::compararDatas(InfraData::getStrDataAtual(),$objRetornoProgramadoDTO->getDtaProgramada())<0){
        $objInfraException->adicionarValidacao('Data Programada n?o pode estar no passado.');
      }
      
      if ($objRetornoProgramadoDTO->getNumIdRetornoProgramado()!=null){
      	$objRetornoProgramadoDTOBanco = new RetornoProgramadoDTO();
      	$objRetornoProgramadoDTOBanco->retDtaProgramada();
      	$objRetornoProgramadoDTOBanco->setNumIdRetornoProgramado($objRetornoProgramadoDTO->getNumIdRetornoProgramado());
      	
      	$objRetornoProgramadoDTOBanco = $this->consultar($objRetornoProgramadoDTOBanco);
      	
      	if (InfraData::compararDatas($objRetornoProgramadoDTOBanco->getDtaProgramada(),$objRetornoProgramadoDTO->getDtaProgramada())<0){
      		$objInfraException->adicionarValidacao('N?o ? poss?vel diminuir o prazo estabelecido anteriormente.');
      	}
      }
      
    }
  }

  private function validarDthAlteracao(RetornoProgramadoDTO $objRetornoProgramadoDTO, InfraException $objInfraException){
    if (InfraString::isBolVazia($objRetornoProgramadoDTO->getDthAlteracao())){
      $objRetornoProgramadoDTO->setDthAlteracao(null);
    }else{
      if (!InfraData::validarDataHora($objRetornoProgramadoDTO->getDthAlteracao())){
        $objInfraException->adicionarValidacao('Data de Altera??o inv?lida.');
      }
    }
  }

  protected function cadastrarControlado(RetornoProgramadoDTO $objRetornoProgramadoDTO) {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('retorno_programado_cadastrar',__METHOD__,$objRetornoProgramadoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarNumIdUnidade($objRetornoProgramadoDTO, $objInfraException);
      $this->validarNumIdAtividadeEnvio($objRetornoProgramadoDTO, $objInfraException);
      $this->validarNumIdUsuario($objRetornoProgramadoDTO, $objInfraException);
      $this->validarDtaProgramada($objRetornoProgramadoDTO, $objInfraException);
      $this->validarDthAlteracao($objRetornoProgramadoDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

      $objRetornoProgramadoBD = new RetornoProgramadoBD($this->getObjInfraIBanco());
      $ret = $objRetornoProgramadoBD->cadastrar($objRetornoProgramadoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando retorno.',$e);
    }
  }

  protected function alterarControlado(RetornoProgramadoDTO $objRetornoProgramadoDTO){
    try {

      //Valida Permissao
  	  SessaoSEI::getInstance()->validarAuditarPermissao('retorno_programado_alterar',__METHOD__,$objRetornoProgramadoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objRetornoProgramadoDTO->isSetNumIdUnidade()){
        $this->validarNumIdUnidade($objRetornoProgramadoDTO, $objInfraException);
      }
      if ($objRetornoProgramadoDTO->isSetNumIdAtividadeEnvio()){
        $this->validarNumIdAtividadeEnvio($objRetornoProgramadoDTO, $objInfraException);
      }
      if ($objRetornoProgramadoDTO->isSetNumIdAtividadeRetorno()){
        $this->validarNumIdAtividadeRetorno($objRetornoProgramadoDTO, $objInfraException);
      }
      if ($objRetornoProgramadoDTO->isSetNumIdUsuario()){
        $this->validarNumIdUsuario($objRetornoProgramadoDTO, $objInfraException);
      }
      if ($objRetornoProgramadoDTO->isSetDtaProgramada()){
        $this->validarDtaProgramada($objRetornoProgramadoDTO, $objInfraException);
      }
      
      $objRetornoProgramadoDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
      $objRetornoProgramadoDTO->setDthAlteracao(InfraData::getStrDataHoraAtual());

      $objInfraException->lancarValidacoes();

      $objRetornoProgramadoBD = new RetornoProgramadoBD($this->getObjInfraIBanco());
      $objRetornoProgramadoBD->alterar($objRetornoProgramadoDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando retorno.',$e);
    }
  }

  protected function excluirControlado($arrObjRetornoProgramadoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('retorno_programado_excluir',__METHOD__,$arrObjRetornoProgramadoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      for($i=0;$i<count($arrObjRetornoProgramadoDTO);$i++) {
        $dto = new RetornoProgramadoDTO();
        $dto->retStrProtocoloFormatadoAtividadeEnvio();
        $dto->retStrSiglaUnidadeAtividadeEnvio();
        $dto->setNumIdRetornoProgramado($arrObjRetornoProgramadoDTO[$i]->getNumIdRetornoProgramado());
        $dto->setNumIdAtividadeRetorno(null,InfraDTO::$OPER_DIFERENTE);

        $dto = $this->consultar($dto);

        if ($dto != null) {
          $objInfraException->adicionarValidacao('Processo '.$dto->getStrProtocoloFormatadoAtividadeEnvio().' j? foi devolvido pela unidade '.$dto->getStrSiglaUnidadeAtividadeEnvio().'.');
        }
      }
      $objInfraException->lancarValidacoes();

      $objRetornoProgramadoBD = new RetornoProgramadoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjRetornoProgramadoDTO);$i++){
        $objRetornoProgramadoBD->excluir($arrObjRetornoProgramadoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro excluindo retorno.',$e);
    }
  }

  protected function consultarConectado(RetornoProgramadoDTO $objRetornoProgramadoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('retorno_programado_consultar',__METHOD__,$objRetornoProgramadoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRetornoProgramadoBD = new RetornoProgramadoBD($this->getObjInfraIBanco());
      $ret = $objRetornoProgramadoBD->consultar($objRetornoProgramadoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro consultando retorno.',$e);
    }
  }

  protected function listarConectado(RetornoProgramadoDTO $objRetornoProgramadoDTO) {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('retorno_programado_listar',__METHOD__,$objRetornoProgramadoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRetornoProgramadoBD = new RetornoProgramadoBD($this->getObjInfraIBanco());
      $ret = $objRetornoProgramadoBD->listar($objRetornoProgramadoDTO);

      //Auditoria

      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando retornos.',$e);
    }
  }

  protected function contarConectado(RetornoProgramadoDTO $objRetornoProgramadoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('retorno_programado_listar',__METHOD__,$objRetornoProgramadoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRetornoProgramadoBD = new RetornoProgramadoBD($this->getObjInfraIBanco());
      $ret = $objRetornoProgramadoBD->contar($objRetornoProgramadoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro contando retornos.',$e);
    }
  }

	protected function listarDevolucoesEntregasConectado(RetornoProgramadoDTO $parObjRetornoProgramadoDTO){
		try{
		
			$objRetornoProgramadoDTO = new RetornoProgramadoDTO();
			$objRetornoProgramadoDTO->retNumIdRetornoProgramado();
			$objRetornoProgramadoDTO->retStrSiglaUnidade();
			$objRetornoProgramadoDTO->retStrSiglaUsuario();
			$objRetornoProgramadoDTO->retDtaProgramada();
			$objRetornoProgramadoDTO->retDblIdProtocoloAtividadeEnvio();
      $objRetornoProgramadoDTO->retNumIdAtividadeRetorno();
			$objRetornoProgramadoDTO->retNumIdUnidadeOrigemAtividadeEnvio();
			$objRetornoProgramadoDTO->retNumIdUnidadeAtividadeEnvio();
			$objRetornoProgramadoDTO->retDthAberturaAtividadeEnvio();
			$objRetornoProgramadoDTO->retDthAberturaAtividadeRetorno();
			$objRetornoProgramadoDTO->retStrSiglaUnidadeOrigemAtividadeEnvio();
			$objRetornoProgramadoDTO->retStrDescricaoUnidadeOrigemAtividadeEnvio();
			$objRetornoProgramadoDTO->retStrSiglaUnidadeAtividadeEnvio();
			$objRetornoProgramadoDTO->retStrDescricaoUnidadeAtividadeEnvio();
			
			$objRetornoProgramadoDTO->adicionarCriterio(array('IdUnidadeOrigemAtividadeEnvio','IdUnidadeAtividadeEnvio'),
			                                            array(InfraDTO::$OPER_IGUAL,InfraDTO::$OPER_IGUAL),
			                                            array(SessaoSEI::getInstance()->getNumIdUnidadeAtual(),SessaoSEI::getInstance()->getNumIdUnidadeAtual()),
			                                            array(InfraDTO::$OPER_LOGICO_OR));
			
			if ($parObjRetornoProgramadoDTO->isSetDtaInicial() && $parObjRetornoProgramadoDTO->isSetDtaFinal()){
				
        $objRetornoProgramadoDTO->adicionarCriterio(array('Programada','Programada'),
                                          array(InfraDTO::$OPER_MAIOR_IGUAL,InfraDTO::$OPER_MENOR_IGUAL),
                                          array($parObjRetornoProgramadoDTO->getDtaInicial(),$parObjRetornoProgramadoDTO->getDtaFinal()),
                                          array(InfraDTO::$OPER_LOGICO_AND));				
				 
			}else{
				$objRetornoProgramadoDTO->setDtaProgramada($parObjRetornoProgramadoDTO->getDtaProgramada());
			}				

			$objRetornoProgramadoDTO->setOrdDtaProgramada(InfraDTO::$TIPO_ORDENACAO_ASC);

			$arrObjRetornoProgramadoDTO = $this->listar($objRetornoProgramadoDTO);

			//n?o faz processamento se montando calendario
				
			if (count($arrObjRetornoProgramadoDTO)>0){
				
				$objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
        $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
				$objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
				$objPesquisaProtocoloDTO->setDblIdProtocolo(InfraArray::converterArrInfraDTO($arrObjRetornoProgramadoDTO,'IdProtocoloAtividadeEnvio'));
				
				$objProtocoloRN = new ProtocoloRN();
				$arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO),'IdProtocolo');
			}

			$arrRet = array();
			foreach($arrObjRetornoProgramadoDTO as $objRetornoProgramadoDTO){
				
				//se tem acesso
				if (isset($arrObjProtocoloDTO[$objRetornoProgramadoDTO->getDblIdProtocoloAtividadeEnvio()])){
					
				  $objRetornoProgramadoDTO->setObjProtocoloDTO($arrObjProtocoloDTO[$objRetornoProgramadoDTO->getDblIdProtocoloAtividadeEnvio()]);
				  
					if ($objRetornoProgramadoDTO->getDthAberturaAtividadeRetorno()==null){
						$objRetornoProgramadoDTO->setNumDiasPrazo(InfraData::compararDatas(InfraData::getStrDataAtual(),$objRetornoProgramadoDTO->getDtaProgramada()));
					}else{
						$objRetornoProgramadoDTO->setNumDiasPrazo(InfraData::compararDatas($objRetornoProgramadoDTO->retDthAberturaAtividadeRetorno(),$objRetornoProgramadoDTO->getDtaProgramada()));
					}
					$arrRet[] = $objRetornoProgramadoDTO;
				}
			} 
			
		  return $arrRet;
		
		}catch(Exception $e){
		  throw new InfraException('Erro listando devolu??es e entregas.',$e);
		}
	}
  
  protected function validarExistenciaConectado(RetornoProgramadoDTO $parObjRetornoProgramadoDTO, InfraException $objInfraException){
  	try{

			$objRetornoProgramadoDTO 	= new RetornoProgramadoDTO();
			$objRetornoProgramadoDTO->setDistinct(true);
			$objRetornoProgramadoDTO->retStrSiglaUnidadeOrigemAtividadeEnvio();
			$objRetornoProgramadoDTO->retStrProtocoloFormatadoAtividadeEnvio();

			$objRetornoProgramadoDTO->setNumIdUnidadeAtividadeEnvio(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

			$objRetornoProgramadoDTO->setDblIdProtocoloAtividadeEnvio($parObjRetornoProgramadoDTO->getDblIdProtocoloAtividadeEnvio());

      if ($parObjRetornoProgramadoDTO->isSetNumIdUnidadeOrigemAtividadeEnvio()) {

        if (is_array($parObjRetornoProgramadoDTO->getNumIdUnidadeOrigemAtividadeEnvio())){
          $objRetornoProgramadoDTO->setNumIdUnidadeOrigemAtividadeEnvio($parObjRetornoProgramadoDTO->getNumIdUnidadeOrigemAtividadeEnvio(),InfraDTO::$OPER_NOT_IN);
        }else{
          $objRetornoProgramadoDTO->setNumIdUnidadeOrigemAtividadeEnvio($parObjRetornoProgramadoDTO->getNumIdUnidadeOrigemAtividadeEnvio(),InfraDTO::$OPER_DIFERENTE);
        }

      }

      $objRetornoProgramadoDTO->setNumIdAtividadeRetorno(null);

      $arrObjRetornoProgramadoDTO = $this->listar($objRetornoProgramadoDTO);

      if (count($arrObjRetornoProgramadoDTO)) {

        $arrObjRetornoProgramadoDTO = InfraArray::indexarArrInfraDTO($arrObjRetornoProgramadoDTO, 'ProtocoloFormatadoAtividadeEnvio', true);

        foreach ($arrObjRetornoProgramadoDTO as $strProtocoloFormatadoAtividadeEnvio => $arr) {
          $strMsgRetornoProgramado = 'Processo ' . $strProtocoloFormatadoAtividadeEnvio . ' possui retorno programado requisitado ';
          if (count($arr) == 1) {
            $strMsgRetornoProgramado .= 'pela unidade ' . $arr[0]->getStrSiglaUnidadeOrigemAtividadeEnvio();
          } else {
            $strMsgRetornoProgramado .= 'pelas unidades: ' . implode(', ', InfraArray::converterArrInfraDTO($arr, 'SiglaUnidadeOrigemAtividadeEnvio'));
          }
          $strMsgRetornoProgramado .= '.';

          $objInfraException->adicionarValidacao($strMsgRetornoProgramado);
        }
      }

    }catch(Exception $e){
      throw new InfraException('Erro validando exist?ncia de Retorno Programado.',$e);
    }
  }
	
  /* 
  protected function desativarControlado($arrObjRetornoProgramadoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('retorno_programado_desativar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRetornoProgramadoBD = new RetornoProgramadoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjRetornoProgramadoDTO);$i++){
        $objRetornoProgramadoBD->desativar($arrObjRetornoProgramadoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando retorno.',$e);
    }
  }

  protected function reativarControlado($arrObjRetornoProgramadoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('retorno_programado_reativar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRetornoProgramadoBD = new RetornoProgramadoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjRetornoProgramadoDTO);$i++){
        $objRetornoProgramadoBD->reativar($arrObjRetornoProgramadoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando retorno.',$e);
    }
  }

  protected function bloquearControlado(RetornoProgramadoDTO $objRetornoProgramadoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('retorno_programado_consultar');

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objRetornoProgramadoBD = new RetornoProgramadoBD($this->getObjInfraIBanco());
      $ret = $objRetornoProgramadoBD->bloquear($objRetornoProgramadoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando retorno.',$e);
    }
  }

 */
}  
?>