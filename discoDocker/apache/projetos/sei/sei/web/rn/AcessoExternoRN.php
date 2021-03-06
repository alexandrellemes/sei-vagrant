<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
 *
 * 10/06/2010 - criado por fazenda_db
 *
 * Vers?o do Gerador de C?digo: 1.29.1
 *
 * Vers?o no CVS: $Id$
 */

require_once dirname(__FILE__).'/../SEI.php';

class AcessoExternoRN extends InfraRN
{

  public static $TA_INTERESSADO = 'I';
  public static $TA_USUARIO_EXTERNO = 'E';
  public static $TA_DESTINATARIO_ISOLADO = 'D';
  public static $TA_SISTEMA = 'S';
  public static $TA_ASSINATURA_EXTERNA = 'A';

  public static $TV_INTEGRAL = 'I';
  public static $TV_PARCIAL = 'P';
  public static $TV_NENHUM = 'N';

  public function __construct()
  {
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco()
  {
    return BancoSEI::getInstance();
  }

  private function validarNumIdAtividade(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getNumIdAtividade())) {
      $objInfraException->adicionarValidacao('Atividade n?o informado.');
    }
  }

  private function validarNumIdParticipante(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getNumIdParticipante())) {
      $objInfraException->adicionarValidacao('Interessado n?o informado.');
    }
  }

  private function validarNumIdUsuarioExterno(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getNumIdUsuarioExterno())) {
      $objInfraException->adicionarValidacao('Usu?rio Externo n?o informado.');
    }
  }

  private function validarDblIdProtocoloAtividade(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getDblIdProtocoloAtividade())) {
      $objInfraException->adicionarValidacao('Processo n?o informado.');
    }
  }

  private function validarNumIdContatoParticipante(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getNumIdContatoParticipante())) {
      $objInfraException->adicionarValidacao('Contato n?o informado.');
    }
  }

  private function validarDblIdDocumento(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getDblIdDocumento())) {
      $objInfraException->adicionarValidacao('Documento n?o informado.');
    }
  }

  private function validarNumDias(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getNumDias())) {
      $objInfraException->adicionarValidacao('Validade do acesso n?o informada..');
    } else {
      if ($objAcessoExternoDTO->getNumDias() <= 0) {
        $objInfraException->adicionarValidacao('Validade do acesso deve ser de pelo menos um dia.');
      }
      /*
    if ($objAcessoExternoDTO->getNumDias()>60){
      $objInfraException->adicionarValidacao('Validade do acesso n?o pode ser superior a 60 dias.');
    }
    */
    }
  }

  private function validarArrObjRelAcessoExtProtocolo(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {

    $arrObjRelAcessoExtProtocoloDTO = $objAcessoExternoDTO->getArrObjRelAcessoExtProtocoloDTO();

    if (count($arrObjRelAcessoExtProtocoloDTO)) {

      $arrIdProtocolos = InfraArray::converterArrInfraDTO($arrObjRelAcessoExtProtocoloDTO, 'IdProtocolo');

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retDblIdProtocolo();
      $objProtocoloDTO->retStrProtocoloFormatado();
      $objProtocoloDTO->setDblIdProtocolo($arrIdProtocolos, InfraDTO::$OPER_IN);

      $objProtocoloRN = new ProtocoloRN();
      $arrObjProtocoloDTO = InfraArray::indexarArrInfraDTO($objProtocoloRN->listarRN0668($objProtocoloDTO), 'IdProtocolo');

      foreach ($arrObjRelAcessoExtProtocoloDTO as $objRelAcessoExtProtocoloDTO) {
        if (!isset($arrObjProtocoloDTO[$objRelAcessoExtProtocoloDTO->getDblIdProtocolo()])) {
          throw new InfraException('Protocolo ['.$objRelAcessoExtProtocoloDTO->getDblIdProtocolo().'] n?o encontrado.');
        }
        $objRelAcessoExtProtocoloDTO->setStrProtocoloFormatadoProtocolo($arrObjProtocoloDTO[$objRelAcessoExtProtocoloDTO->getDblIdProtocolo()]->getStrProtocoloFormatado());
      }

      $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
      $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($objAcessoExternoDTO->getDblIdProtocoloAtividade());
      $objRelProtocoloProtocoloDTO->setStrStaAssociacao(array(RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO, RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO), InfraDTO::$OPER_IN);

      $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
      foreach ($arrObjRelAcessoExtProtocoloDTO as $objRelAcessoExtProtocoloDTO) {
        $objRelProtocoloProtocoloDTO->setDblIdProtocolo2($objRelAcessoExtProtocoloDTO->getDblIdProtocolo());
        if ($objRelProtocoloProtocoloRN->contarRN0843($objRelProtocoloProtocoloDTO) == 0) {
          throw new InfraException('Protocolo '.$objRelAcessoExtProtocoloDTO->getStrProtocoloFormatadoProtocolo().' n?o pode ser liberado para acesso externo no processo.');
        }
      }
    }
  }

  private function validarDtaValidade(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getDtaValidade())) {
      $objInfraException->adicionarValidacao('Data de Validade n?o informada.');
    } else {
      if (!InfraData::validarData($objAcessoExternoDTO->getDtaValidade())) {
        $objInfraException->adicionarValidacao('Data de Validade inv?lida.');
      }
    }
  }

  private function validarStrEmailUnidade(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getStrEmailUnidade())) {
      $objInfraException->adicionarValidacao('E-mail da Unidade n?o informado.');
    }
  }

  private function validarStrEmailDestinatario(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getStrEmailDestinatario())) {
      $objInfraException->adicionarValidacao('E-mail do Destinat?rio n?o informado.');
    } else {
      $objAcessoExternoDTO->setStrEmailDestinatario(trim($objAcessoExternoDTO->getStrEmailDestinatario()));

      if (strlen($objAcessoExternoDTO->getStrEmailDestinatario()) > 100) {
        $objInfraException->adicionarValidacao('E-mail do Destinat?rio possui tamanho superior a 100 caracteres.');
      }
    }
  }

  private function validarStrHashInterno(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getStrHashInterno())) {
      $objInfraException->adicionarValidacao('HASH Interno n?o informado.');
    } else {
      $objAcessoExternoDTO->setStrHashInterno(trim($objAcessoExternoDTO->getStrHashInterno()));

      if (strlen($objAcessoExternoDTO->getStrHashInterno()) > 32) {
        $objInfraException->adicionarValidacao('HASH Interno possui tamanho superior a 32 caracteres.');
      }
    }
  }

  private function validarStrStaTipo(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getStrStaTipo())) {
      $objInfraException->adicionarValidacao('Tipo n?o informado.');
    } else {
      if (!in_array($objAcessoExternoDTO->getStrStaTipo(), InfraArray::converterArrInfraDTO($this->listarValoresTipoAcessoExterno(), 'StaTipo'))) {
        $objInfraException->adicionarValidacao('Tipo inv?lido.');
      }
    }
  }

  private function validarStrSenha(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getStrSenha())) {
      $objInfraException->adicionarValidacao('Senha n?o informada.');
    }
  }

  private function validarStrMotivo(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {
    if (InfraString::isBolVazia($objAcessoExternoDTO->getStrMotivo())) {
      $objInfraException->adicionarValidacao('Motivo n?o informado.');
    }
  }

  private function validarStrSinProcesso(AcessoExternoDTO $objAcessoExternoDTO, InfraException $objInfraException)
  {

    if (InfraString::isBolVazia($objAcessoExternoDTO->getStrSinProcesso())) {
      $objInfraException->adicionarValidacao('Sinalizador de acesso ao processo n?o informado.');
    } else {
      if (!InfraUtil::isBolSinalizadorValido($objAcessoExternoDTO->getStrSinProcesso())) {
        $objInfraException->adicionarValidacao('Sinalizador de acesso ao processo inv?lido.');
      }
    }
  }

  protected function cadastrarControlado(AcessoExternoDTO $objAcessoExternoDTO)
  {
    try {

      //Valida Permissao
      $objAcessoExternoDTOAuditoria = clone($objAcessoExternoDTO);
      $objAcessoExternoDTOAuditoria->unSetStrSenha();
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_cadastrar', __METHOD__, $objAcessoExternoDTOAuditoria);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarStrStaTipo($objAcessoExternoDTO, $objInfraException);
      //$this->validarNumIdAtividade($objAcessoExternoDTO, $objInfraException);
      //$this->validarStrHashInterno($objAcessoExternoDTO, $objInfraException);

      if ($objAcessoExternoDTO->isSetArrObjRelAcessoExtProtocoloDTO()) {
        $this->validarArrObjRelAcessoExtProtocolo($objAcessoExternoDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      if ($objAcessoExternoDTO->getStrStaTipo() == self::$TA_INTERESSADO ||
          $objAcessoExternoDTO->getStrStaTipo() == self::$TA_USUARIO_EXTERNO ||
          $objAcessoExternoDTO->getStrStaTipo() == self::$TA_DESTINATARIO_ISOLADO
      ) {

        $this->validarStrEmailUnidade($objAcessoExternoDTO, $objInfraException);

        if ($objAcessoExternoDTO->getStrStaTipo() == self::$TA_INTERESSADO) {
          $this->validarNumIdParticipante($objAcessoExternoDTO, $objInfraException);
        } else if ($objAcessoExternoDTO->getStrStaTipo() == self::$TA_USUARIO_EXTERNO) {
          $this->validarNumIdUsuarioExterno($objAcessoExternoDTO, $objInfraException);
          $this->validarDblIdProtocoloAtividade($objAcessoExternoDTO, $objInfraException);
        } else {

          if (InfraString::isBolVazia($objAcessoExternoDTO->getNumIdContatoParticipante())) {
            if (InfraString::isBolVazia($objAcessoExternoDTO->getStrNomeContato())) {
              $objInfraException->adicionarValidacao('Destinat?rio n?o informado.');
            } else {
              $objContatoDTO = new ContatoDTO();
              $objContatoDTO->setStrNome($objAcessoExternoDTO->getStrNomeContato());
              $objContatoRN = new ContatoRN();
              $objContatoDTO = $objContatoRN->cadastrarContextoTemporario($objContatoDTO);
              $objAcessoExternoDTO->setNumIdContatoParticipante($objContatoDTO->getNumIdContato());
            }
          }
        }

        $this->validarStrEmailDestinatario($objAcessoExternoDTO, $objInfraException);
        //$this->validarDtaValidade($objAcessoExternoDTO, $objInfraException);
        $this->validarStrSenha($objAcessoExternoDTO, $objInfraException);
        $this->validarStrMotivo($objAcessoExternoDTO, $objInfraException);
        $this->validarNumDias($objAcessoExternoDTO, $objInfraException);

        $objInfraException->lancarValidacoes();

        $objAcessoExternoDTO->setDblIdDocumento(null);
        $objAcessoExternoDTO->setStrSinProcesso('S');

        $objInfraSip = new InfraSip(SessaoSEI::getInstance());
        $objInfraSip->autenticar(SessaoSEI::getInstance()->getNumIdOrgaoUsuario(),
            SessaoSEI::getInstance()->getNumIdContextoUsuario(),
            SessaoSEI::getInstance()->getStrSiglaUsuario(),
            $objAcessoExternoDTO->getStrSenha());

        $objAcessoExternoDTO->setDtaValidade(InfraData::calcularData($objAcessoExternoDTO->getNumDias(), InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ADIANTE));

        $objParticipanteRN = new ParticipanteRN();

        if ($objAcessoExternoDTO->getStrStaTipo() == self::$TA_USUARIO_EXTERNO) {

          $objUsuarioDTO = new UsuarioDTO();
          $objUsuarioDTO->retNumIdUsuario();
          $objUsuarioDTO->retNumIdContato();
          $objUsuarioDTO->retStrSigla();
          $objUsuarioDTO->retStrNome();
          $objUsuarioDTO->setNumIdUsuario($objAcessoExternoDTO->getNumIdUsuarioExterno());
          $objUsuarioDTO->setStrStaTipo(UsuarioRN::$TU_EXTERNO);

          $objUsuarioRN = new UsuarioRN();
          $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);


          $objParticipanteDTO = new ParticipanteDTO();
          $objParticipanteDTO->retNumIdParticipante();
          $objParticipanteDTO->setDblIdProtocolo($objAcessoExternoDTO->getDblIdProtocoloAtividade());
          $objParticipanteDTO->setNumIdContato($objUsuarioDTO->getNumIdContato());
          $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_ACESSO_EXTERNO);

          $objParticipanteDTO = $objParticipanteRN->consultarRN1008($objParticipanteDTO);

          if ($objParticipanteDTO == null) {
            $objParticipanteDTO = new ParticipanteDTO();
            $objParticipanteDTO->setDblIdProtocolo($objAcessoExternoDTO->getDblIdProtocoloAtividade());
            $objParticipanteDTO->setNumIdContato($objUsuarioDTO->getNumIdContato());
            $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_ACESSO_EXTERNO);
            $objParticipanteDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objParticipanteDTO->setNumSequencia(0);
            $objParticipanteDTO = $objParticipanteRN->cadastrarRN0170($objParticipanteDTO);
          }

          $objAcessoExternoDTO->setNumIdParticipante($objParticipanteDTO->getNumIdParticipante());
          $objAcessoExternoDTO->setStrEmailDestinatario($objUsuarioDTO->getStrSigla());

        } else if ($objAcessoExternoDTO->getStrStaTipo() == self::$TA_DESTINATARIO_ISOLADO) {

          $objContatoDTO = new ContatoDTO();
          $objContatoDTO->retNumIdContato();
          $objContatoDTO->retStrNome();
          $objContatoDTO->setNumIdContato($objAcessoExternoDTO->getNumIdContatoParticipante());

          $objContatoRN = new ContatoRN();
          $objContatoDTO = $objContatoRN->consultarRN0324($objContatoDTO);

          $objParticipanteDTO = new ParticipanteDTO();
          $objParticipanteDTO->retNumIdParticipante();
          $objParticipanteDTO->setDblIdProtocolo($objAcessoExternoDTO->getDblIdProtocoloAtividade());
          $objParticipanteDTO->setNumIdContato($objContatoDTO->getNumIdContato());
          $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_ACESSO_EXTERNO);

          $objParticipanteDTO = $objParticipanteRN->consultarRN1008($objParticipanteDTO);

          if ($objParticipanteDTO == null) {
            $objParticipanteDTO = new ParticipanteDTO();
            $objParticipanteDTO->setDblIdProtocolo($objAcessoExternoDTO->getDblIdProtocoloAtividade());
            $objParticipanteDTO->setNumIdContato($objContatoDTO->getNumIdContato());
            $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_ACESSO_EXTERNO);
            $objParticipanteDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $objParticipanteDTO->setNumSequencia(0);
            $objParticipanteDTO = $objParticipanteRN->cadastrarRN0170($objParticipanteDTO);
          }

          $objAcessoExternoDTO->setNumIdParticipante($objParticipanteDTO->getNumIdParticipante());
        }

        $objParticipanteDTO = new ParticipanteDTO();
        $objParticipanteDTO->retNumIdParticipante();
        $objParticipanteDTO->retDblIdProtocolo();
        $objParticipanteDTO->retStrNomeContato();
        $objParticipanteDTO->setNumIdParticipante($objAcessoExternoDTO->getNumIdParticipante());
        $objParticipanteRN = new ParticipanteRN();
        $objParticipanteDTO = $objParticipanteRN->consultarRN1008($objParticipanteDTO);

        $arrObjAtributoAndamentoDTO = array();
        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('DESTINATARIO_NOME');
        $objAtributoAndamentoDTO->setStrValor($objParticipanteDTO->getStrNomeContato());
        $objAtributoAndamentoDTO->setStrIdOrigem($objParticipanteDTO->getNumIdParticipante());
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('DESTINATARIO_EMAIL');
        $objAtributoAndamentoDTO->setStrValor($objAcessoExternoDTO->getStrEmailDestinatario());
        $objAtributoAndamentoDTO->setStrIdOrigem($objParticipanteDTO->getNumIdParticipante());
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('MOTIVO');
        $objAtributoAndamentoDTO->setStrValor($objAcessoExternoDTO->getStrMotivo());
        $objAtributoAndamentoDTO->setStrIdOrigem($objAcessoExternoDTO->getNumIdParticipante());
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('DATA_VALIDADE');
        $objAtributoAndamentoDTO->setStrValor($objAcessoExternoDTO->getDtaValidade());
        $objAtributoAndamentoDTO->setStrIdOrigem(null);
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('DIAS_VALIDADE');
        $objAtributoAndamentoDTO->setStrValor($objAcessoExternoDTO->getNumDias().' '.($objAcessoExternoDTO->getNumDias() == 1 ? 'dia' : 'dias'));
        $objAtributoAndamentoDTO->setStrIdOrigem(null);
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        $strTipoVisualizacao = self::$TV_INTEGRAL;
        if ($objAcessoExternoDTO->isSetArrObjRelAcessoExtProtocoloDTO() && count($objAcessoExternoDTO->getArrObjRelAcessoExtProtocoloDTO())) {
          $strTipoVisualizacao = self::$TV_PARCIAL;
        }

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('VISUALIZACAO');
        $objAtributoAndamentoDTO->setStrValor(null);
        $objAtributoAndamentoDTO->setStrIdOrigem($strTipoVisualizacao);
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;


        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDblIdProtocolo($objParticipanteDTO->getDblIdProtocolo());
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

        $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_LIBERACAO_ACESSO_EXTERNO);

        $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);

        $objAtividadeRN = new AtividadeRN();
        $objAtividadeDTO = $objAtividadeRN->gerarInternaRN0727($objAtividadeDTO);

        $objAcessoExternoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());


        $objAcessoExternoDTO->setStrHashInterno(md5(time()));


      } else if ($objAcessoExternoDTO->getStrStaTipo() == self::$TA_ASSINATURA_EXTERNA) {

        $this->validarStrEmailUnidade($objAcessoExternoDTO, $objInfraException);
        $this->validarDblIdDocumento($objAcessoExternoDTO, $objInfraException);
        //$this->validarStrEmailDestinatario($objAcessoExternoDTO, $objInfraException);
        //$this->validarDtaValidade($objAcessoExternoDTO, $objInfraException);
        //$this->validarStrSenha($objAcessoExternoDTO, $objInfraException);
        //$this->validarStrMotivo($objAcessoExternoDTO, $objInfraException);
        //$this->validarNumDias($objAcessoExternoDTO, $objInfraException);
        $this->validarStrSinProcesso($objAcessoExternoDTO, $objInfraException);

        $objInfraException->lancarValidacoes();

        $objAcessoExternoDTO->setDtaValidade(null);
        $objAcessoExternoDTO->setStrMotivo(null);

        //busca processo
        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->retDblIdDocumento();
        $objDocumentoDTO->retDblIdProcedimento();
        $objDocumentoDTO->retStrProtocoloProcedimentoFormatado();
        $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
        $objDocumentoDTO->retStrNomeSerie();
        $objDocumentoDTO->retStrStaDocumento();
        $objDocumentoDTO->retStrStaProtocoloProtocolo();
        $objDocumentoDTO->setDblIdDocumento($objAcessoExternoDTO->getDblIdDocumento());

        $objDocumentoRN = new DocumentoRN();
        $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

        if ($objDocumentoDTO->getStrStaDocumento() != DocumentoRN::$TD_EDITOR_INTERNO && $objDocumentoDTO->getStrStaDocumento() != DocumentoRN::$TD_FORMULARIO_GERADO) {
          $objInfraException->lancarValidacao('Somente documentos do editor interno ou formul?rios podem ser liberados para assinatura externa.');
        }

        //busca contato
        $objUsuarioDTO = new UsuarioDTO();
        $objUsuarioDTO->retNumIdUsuario();
        $objUsuarioDTO->retStrSigla();
        $objUsuarioDTO->retStrNome();
        $objUsuarioDTO->retStrStaTipo();
        $objUsuarioDTO->retNumIdContato();
        $objUsuarioDTO->setNumIdUsuario($objAcessoExternoDTO->getNumIdUsuarioExterno());

        $objUsuarioRN = new UsuarioRN();
        $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);

        if ($objUsuarioDTO->getStrStaTipo() == UsuarioRN::$TU_EXTERNO_PENDENTE) {
          $objInfraException->lancarValidacao('Usu?rio externo "'.$objUsuarioDTO->getStrSigla().'" ainda n?o foi liberado.');
        }

        if ($objUsuarioDTO->getStrStaTipo() != UsuarioRN::$TU_EXTERNO) {
          $objInfraException->lancarValidacao('Usu?rio "'.$objUsuarioDTO->getStrSigla().'" n?o ? um usu?rio externo.');
        }

        //verifica se o contato j? ? participante do processo
        $objParticipanteDTO = new ParticipanteDTO();
        $objParticipanteDTO->retNumIdParticipante();
        $objParticipanteDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdProcedimento());
        $objParticipanteDTO->setNumIdContato($objUsuarioDTO->getNumIdContato());
        $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_ACESSO_EXTERNO);

        $objParticipanteRN = new ParticipanteRN();
        $objParticipanteDTO = $objParticipanteRN->consultarRN1008($objParticipanteDTO);

        if ($objParticipanteDTO == null) {

          $objParticipanteDTO = new ParticipanteDTO();
          $objParticipanteDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdProcedimento());
          $objParticipanteDTO->setNumIdContato($objUsuarioDTO->getNumIdContato());
          $objParticipanteDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
          $objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_ACESSO_EXTERNO);
          $objParticipanteDTO->setNumSequencia(0);

          $objParticipanteDTO = $objParticipanteRN->cadastrarRN0170($objParticipanteDTO);
        } else {
          $dto = new AcessoExternoDTO();
          $dto->retStrSiglaContato();
          $dto->retDthAberturaAtividade();
          $dto->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
          $dto->setNumIdParticipante($objParticipanteDTO->getNumIdParticipante());
          $dto->setStrStaTipo(AcessoExternoRN::$TA_ASSINATURA_EXTERNA);
          $dto->setNumMaxRegistrosRetorno(1);

          $dto = $this->consultar($dto);

          if ($dto != null) {
            $objInfraException->lancarValidacao('Usu?rio externo '.$dto->getStrSiglaContato().' j? recebeu libera??o para assinatura externa no documento '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().' em '.substr($dto->getDthAberturaAtividade(), 0, 16).'.');
          }
        }

        $objAcessoExternoDTO->setNumIdParticipante($objParticipanteDTO->getNumIdParticipante());

        $arrObjAtributoAndamentoDTO = array();
        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('USUARIO_EXTERNO_SIGLA');
        $objAtributoAndamentoDTO->setStrValor($objUsuarioDTO->getStrSigla());
        $objAtributoAndamentoDTO->setStrIdOrigem($objUsuarioDTO->getNumIdUsuario());
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('USUARIO_EXTERNO_NOME');
        $objAtributoAndamentoDTO->setStrValor($objUsuarioDTO->getStrNome());
        $objAtributoAndamentoDTO->setStrIdOrigem($objUsuarioDTO->getNumIdUsuario());
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
        $objAtributoAndamentoDTO->setStrValor($objDocumentoDTO->getStrProtocoloDocumentoFormatado());
        $objAtributoAndamentoDTO->setStrIdOrigem($objDocumentoDTO->getDblIdDocumento());
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        if ($objAcessoExternoDTO->getStrSinProcesso() == 'S') {
          $strTipoVisualizacao = self::$TV_INTEGRAL;
        } else {
          $strTipoVisualizacao = self::$TV_NENHUM;
        }

        if ($objAcessoExternoDTO->isSetArrObjRelAcessoExtProtocoloDTO() && count($objAcessoExternoDTO->getArrObjRelAcessoExtProtocoloDTO())) {
          $objAcessoExternoDTO->setStrSinProcesso('S');
          $strTipoVisualizacao = self::$TV_PARCIAL;
        }

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('VISUALIZACAO');
        $objAtributoAndamentoDTO->setStrValor(null);
        $objAtributoAndamentoDTO->setStrIdOrigem($strTipoVisualizacao);
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdProcedimento());
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_LIBERACAO_ASSINATURA_EXTERNA);
        $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);


        $objAtividadeRN = new AtividadeRN();
        $objAtividadeDTO = $objAtividadeRN->gerarInternaRN0727($objAtividadeDTO);

        $objAcessoExternoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());

        $objAcessoExternoDTO->setStrHashInterno(md5(time()));

      } else if ($objAcessoExternoDTO->getStrStaTipo() == self::$TA_SISTEMA) {

        $this->validarNumIdParticipante($objAcessoExternoDTO, $objInfraException);

        $objInfraException->lancarValidacoes();

        $objAcessoExternoDTO->setDblIdDocumento(null);
        $objAcessoExternoDTO->setStrSinProcesso('S');


        $objAcessoExternoDTO->setStrEmailUnidade(null);
        $objAcessoExternoDTO->setStrEmailDestinatario(null);
        $objAcessoExternoDTO->setDtaValidade(null);

        $objParticipanteDTO = new ParticipanteDTO();
        $objParticipanteDTO->retStrSiglaContato();
        $objParticipanteDTO->retStrNomeContato();
        $objParticipanteDTO->retDblIdProtocolo();
        $objParticipanteDTO->setNumIdParticipante($objAcessoExternoDTO->getNumIdParticipante());

        $objParticipanteRN = new ParticipanteRN();
        $objParticipanteDTO = $objParticipanteRN->consultarRN1008($objParticipanteDTO);

        $arrObjAtributoAndamentoDTO = array();
        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('INTERESSADO');
        $objAtributoAndamentoDTO->setStrValor($objParticipanteDTO->getStrSiglaContato().'?'.$objParticipanteDTO->getStrNomeContato());
        $objAtributoAndamentoDTO->setStrIdOrigem($objAcessoExternoDTO->getNumIdParticipante());
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDblIdProtocolo($objParticipanteDTO->getDblIdProtocolo());
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_ACESSO_EXTERNO_SISTEMA);
        $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);

        $objAtividadeRN = new AtividadeRN();
        $objAtividadeDTO = $objAtividadeRN->gerarInternaRN0727($objAtividadeDTO);

        $objAcessoExternoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());

      }

      //gera da mesma forma independente do tipo
      $objAcessoExternoDTO->setStrHashInterno(md5(time()));
      $objAcessoExternoDTO->setStrSinAtivo('S');


      $objAcessoExternoBD = new AcessoExternoBD($this->getObjInfraIBanco());
      $ret = $objAcessoExternoBD->cadastrar($objAcessoExternoDTO);

      if ($objAcessoExternoDTO->isSetArrObjRelAcessoExtProtocoloDTO()) {

        $objRelAcessoExtProtocoloRN = new RelAcessoExtProtocoloRN();

        $arrObjRelAcessoExtProtocoloDTO = $objAcessoExternoDTO->getArrObjRelAcessoExtProtocoloDTO();
        foreach ($arrObjRelAcessoExtProtocoloDTO as $objRelAcessoExtProtocoloDTO) {
          $objRelAcessoExtProtocoloDTO->setNumIdAcessoExterno($ret->getNumIdAcessoExterno());
          $objRelAcessoExtProtocoloRN->cadastrar($objRelAcessoExtProtocoloDTO);
        }
      }

      //ENVIAR EMAIL
      if ($objAcessoExternoDTO->getStrStaTipo() == self::$TA_INTERESSADO || $objAcessoExternoDTO->getStrStaTipo() == self::$TA_DESTINATARIO_ISOLADO) {

        $objEmailSistemaDTO = new EmailSistemaDTO();
        $objEmailSistemaDTO->retStrDe();
        $objEmailSistemaDTO->retStrPara();
        $objEmailSistemaDTO->retStrAssunto();
        $objEmailSistemaDTO->retStrConteudo();
        $objEmailSistemaDTO->setNumIdEmailSistema(EmailSistemaRN::$ES_DISPONIBILIZACAO_ACESSO_EXTERNO);

        $objEmailSistemaRN = new EmailSistemaRN();
        $objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);

        if ($objEmailSistemaDTO != null) {

          $objProtocoloDTO = new ProtocoloDTO();
          $objProtocoloDTO->retStrProtocoloFormatado();
          $objProtocoloDTO->setDblIdProtocolo($objParticipanteDTO->getDblIdProtocolo());

          $objProtocoloRN = new ProtocoloRN();
          $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

          $objUnidadeDTO = new UnidadeDTO();
          $objUnidadeDTO->retStrSigla();
          $objUnidadeDTO->retStrDescricao();
          $objUnidadeDTO->retStrSiglaOrgao();
          $objUnidadeDTO->retStrDescricaoOrgao();
          $objUnidadeDTO->retStrSitioInternetOrgaoContato();
          $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

          $objUnidadeRN = new UnidadeRN();
          $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

          $strDe = $objEmailSistemaDTO->getStrDe();
          $strDe = str_replace('@email_unidade@', $objAcessoExternoDTO->getStrEmailUnidade(), $strDe);

          $strPara = $objEmailSistemaDTO->getStrPara();
          $strPara = str_replace('@email_destinatario@', $objAcessoExternoDTO->getStrEmailDestinatario(), $strPara);

          $strAssunto = $objEmailSistemaDTO->getStrAssunto();
          $strAssunto = str_replace('@processo@', $objProtocoloDTO->getStrProtocoloFormatado(), $strAssunto);

          $strConteudo = $objEmailSistemaDTO->getStrConteudo();
          $strConteudo = str_replace('@processo@', $objProtocoloDTO->getStrProtocoloFormatado(), $strConteudo);
          $strConteudo = str_replace('@nome_destinatario@', $objParticipanteDTO->getStrNomeContato(), $strConteudo);
          $strConteudo = str_replace('@data_validade@', $objAcessoExternoDTO->getDtaValidade(), $strConteudo);
          $strConteudo = str_replace('@link_acesso_externo@', SessaoSEIExterna::getInstance($ret->getNumIdAcessoExterno())->assinarLink(ConfiguracaoSEI::getInstance()->getValor('SEI', 'URL').'/processo_acesso_externo_consulta.php?id_acesso_externo='.$ret->getNumIdAcessoExterno()), $strConteudo);
          $strConteudo = str_replace('@sigla_unidade@', $objUnidadeDTO->getStrSigla(), $strConteudo);
          $strConteudo = str_replace('@descricao_unidade@', $objUnidadeDTO->getStrDescricao(), $strConteudo);
          $strConteudo = str_replace('@sigla_orgao@', $objUnidadeDTO->getStrSiglaOrgao(), $strConteudo);
          $strConteudo = str_replace('@descricao_orgao@', $objUnidadeDTO->getStrDescricaoOrgao(), $strConteudo);
          $strConteudo = str_replace('@sitio_internet_orgao@', $objUnidadeDTO->getStrSitioInternetOrgaoContato(), $strConteudo);

          $objEmailDTO = new EmailDTO();
          $objEmailDTO->setStrDe($strDe);
          $objEmailDTO->setStrPara($strPara);
          $objEmailDTO->setStrAssunto($strAssunto);
          $objEmailDTO->setStrMensagem($strConteudo);

          EmailRN::processar(array($objEmailDTO));
        }
      } else if ($objAcessoExternoDTO->getStrStaTipo() == self::$TA_USUARIO_EXTERNO) {

        $objEmailSistemaDTO = new EmailSistemaDTO();
        $objEmailSistemaDTO->retStrDe();
        $objEmailSistemaDTO->retStrPara();
        $objEmailSistemaDTO->retStrAssunto();
        $objEmailSistemaDTO->retStrConteudo();
        $objEmailSistemaDTO->setNumIdEmailSistema(EmailSistemaRN::$ES_DISPONIBILIZACAO_ACESSO_EXTERNO_USUARIO_EXTERNO);

        $objEmailSistemaRN = new EmailSistemaRN();
        $objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);

        if ($objEmailSistemaDTO != null) {
          $objProtocoloDTO = new ProtocoloDTO();
          $objProtocoloDTO->retStrProtocoloFormatado();
          $objProtocoloDTO->setDblIdProtocolo($objParticipanteDTO->getDblIdProtocolo());

          $objProtocoloRN = new ProtocoloRN();
          $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

          $objUnidadeDTO = new UnidadeDTO();
          $objUnidadeDTO->retNumIdOrgao();
          $objUnidadeDTO->retStrSigla();
          $objUnidadeDTO->retStrDescricao();
          $objUnidadeDTO->retStrSiglaOrgao();
          $objUnidadeDTO->retStrDescricaoOrgao();
          $objUnidadeDTO->retStrSitioInternetOrgaoContato();
          $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

          $objUnidadeRN = new UnidadeRN();
          $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

          $strDe = $objEmailSistemaDTO->getStrDe();
          $strDe = str_replace('@email_unidade@', $objAcessoExternoDTO->getStrEmailUnidade(), $strDe);

          $strPara = $objEmailSistemaDTO->getStrPara();
          $strPara = str_replace('@email_usuario_externo@', $objUsuarioDTO->getStrSigla(), $strPara);

          $strAssunto = $objEmailSistemaDTO->getStrAssunto();
          $strAssunto = str_replace('@processo@', $objProtocoloDTO->getStrProtocoloFormatado(), $strAssunto);

          $strConteudo = $objEmailSistemaDTO->getStrConteudo();
          $strConteudo = str_replace('@processo@', $objProtocoloDTO->getStrProtocoloFormatado(), $strConteudo);
          $strConteudo = str_replace('@nome_usuario_externo@', $objUsuarioDTO->getStrNome(), $strConteudo);
          $strConteudo = str_replace('@email_usuario_externo@', $objUsuarioDTO->getStrSigla(), $strConteudo);
          $strConteudo = str_replace('@link_login_usuario_externo@', ConfiguracaoSEI::getInstance()->getValor('SEI', 'URL').'/controlador_externo.php?acao=usuario_externo_logar&id_orgao_acesso_externo='.$objUnidadeDTO->getNumIdOrgao(), $strConteudo);

          $strConteudo = str_replace('@sigla_unidade@', $objUnidadeDTO->getStrSigla(), $strConteudo);
          $strConteudo = str_replace('@descricao_unidade@', $objUnidadeDTO->getStrDescricao(), $strConteudo);
          $strConteudo = str_replace('@sigla_orgao@', $objUnidadeDTO->getStrSiglaOrgao(), $strConteudo);
          $strConteudo = str_replace('@descricao_orgao@', $objUnidadeDTO->getStrDescricaoOrgao(), $strConteudo);
          $strConteudo = str_replace('@sitio_internet_orgao@', $objUnidadeDTO->getStrSitioInternetOrgaoContato(), $strConteudo);

          $objEmailDTO = new EmailDTO();
          $objEmailDTO->setStrDe($strDe);
          $objEmailDTO->setStrPara($strPara);
          $objEmailDTO->setStrAssunto($strAssunto);
          $objEmailDTO->setStrMensagem($strConteudo);

          EmailRN::processar(array($objEmailDTO));
        }
      } else if ($objAcessoExternoDTO->getStrStaTipo() == self::$TA_ASSINATURA_EXTERNA) {

        $objEmailSistemaDTO = new EmailSistemaDTO();
        $objEmailSistemaDTO->retStrDe();
        $objEmailSistemaDTO->retStrPara();
        $objEmailSistemaDTO->retStrAssunto();
        $objEmailSistemaDTO->retStrConteudo();
        $objEmailSistemaDTO->setNumIdEmailSistema(EmailSistemaRN::$ES_DISPONIBILIZACAO_ASSINATURA_EXTERNA_USUARIO_EXTERNO);

        $objEmailSistemaRN = new EmailSistemaRN();
        $objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);

        if ($objEmailSistemaDTO != null) {

          $objUnidadeDTO = new UnidadeDTO();
          $objUnidadeDTO->retNumIdOrgao();
          $objUnidadeDTO->retStrSigla();
          $objUnidadeDTO->retStrDescricao();
          $objUnidadeDTO->retStrSiglaOrgao();
          $objUnidadeDTO->retStrDescricaoOrgao();
          $objUnidadeDTO->retStrSitioInternetOrgaoContato();
          $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

          $objUnidadeRN = new UnidadeRN();
          $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

          $strDe = $objEmailSistemaDTO->getStrDe();
          $strDe = str_replace('@email_unidade@', $objAcessoExternoDTO->getStrEmailUnidade(), $strDe);

          $strPara = $objEmailSistemaDTO->getStrPara();
          $strPara = str_replace('@email_usuario_externo@', $objUsuarioDTO->getStrSigla(), $strPara);

          $strAssunto = $objEmailSistemaDTO->getStrAssunto();
          $strAssunto = str_replace('@processo@', $objDocumentoDTO->getStrProtocoloProcedimentoFormatado(), $strAssunto);

          $strConteudo = $objEmailSistemaDTO->getStrConteudo();
          $strConteudo = str_replace('@processo@', $objDocumentoDTO->getStrProtocoloProcedimentoFormatado(), $strConteudo);
          $strConteudo = str_replace('@documento@', $objDocumentoDTO->getStrProtocoloDocumentoFormatado(), $strConteudo);
          $strConteudo = str_replace('@tipo_documento@', $objDocumentoDTO->getStrNomeSerie(), $strConteudo);
          $strConteudo = str_replace('@nome_usuario_externo@', $objUsuarioDTO->getStrNome(), $strConteudo);
          $strConteudo = str_replace('@email_usuario_externo@', $objUsuarioDTO->getStrSigla(), $strConteudo);
          $strConteudo = str_replace('@link_login_usuario_externo@', ConfiguracaoSEI::getInstance()->getValor('SEI', 'URL').'/controlador_externo.php?acao=usuario_externo_logar&id_orgao_acesso_externo='.$objUnidadeDTO->getNumIdOrgao(), $strConteudo);
          $strConteudo = str_replace('@sigla_unidade@', $objUnidadeDTO->getStrSigla(), $strConteudo);
          $strConteudo = str_replace('@descricao_unidade@', $objUnidadeDTO->getStrDescricao(), $strConteudo);
          $strConteudo = str_replace('@sigla_orgao@', $objUnidadeDTO->getStrSiglaOrgao(), $strConteudo);
          $strConteudo = str_replace('@descricao_orgao@', $objUnidadeDTO->getStrDescricaoOrgao(), $strConteudo);
          $strConteudo = str_replace('@sitio_internet_orgao@', $objUnidadeDTO->getStrSitioInternetOrgaoContato(), $strConteudo);

          $objEmailDTO = new EmailDTO();
          $objEmailDTO->setStrDe($strDe);
          $objEmailDTO->setStrPara($strPara);
          $objEmailDTO->setStrAssunto($strAssunto);
          $objEmailDTO->setStrMensagem($strConteudo);

          EmailRN::processar(array($objEmailDTO));

        }
      }

      return $ret;

      //Auditoria

    } catch (Exception $e) {
      throw new InfraException('Erro cadastrando Acesso Externo.', $e);
    }
  }

  protected function consultarProcessoAcessoExternoConectado(AcessoExternoDTO $parObjAcessoExternoDTO)
  {
    try {

      global $SEI_MODULOS;

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_listar', __METHOD__, $parObjAcessoExternoDTO);

      //Regras de Negocio

      $objAcessoExternoDTO = new AcessoExternoDTO();
      $objAcessoExternoDTO->setBolExclusaoLogica(false);
      $objAcessoExternoDTO->retNumIdAcessoExterno();
      $objAcessoExternoDTO->retStrSinAtivo();
      $objAcessoExternoDTO->retDblIdProtocoloAtividade();
      $objAcessoExternoDTO->retDblIdDocumento();
      $objAcessoExternoDTO->retStrSinProcesso();
      $objAcessoExternoDTO->retStrStaTipo();
      $objAcessoExternoDTO->setNumIdAcessoExterno($parObjAcessoExternoDTO->getNumIdAcessoExterno());

      $objAcessoExternoDTO = $this->consultar($objAcessoExternoDTO);

      if ($objAcessoExternoDTO == null) {
        throw new InfraException('Disponibiliza??o de Acesso Externo n?o encontrada.');
      }

      if ($objAcessoExternoDTO->getStrSinAtivo() == 'N') {
        throw new InfraException('Disponibiliza??o de Acesso Externo cancelada.');
      }

      $objRelAcessoExtProtocoloDTO = new RelAcessoExtProtocoloDTO();
      $objRelAcessoExtProtocoloDTO->retDblIdProtocolo();
      $objRelAcessoExtProtocoloDTO->setNumIdAcessoExterno($parObjAcessoExternoDTO->getNumIdAcessoExterno());

      $objRelAcessoExtProtocoloRN = new RelAcessoExtProtocoloRN();
      $arrIdLiberados = InfraArray::converterArrInfraDTO($objRelAcessoExtProtocoloRN->listar($objRelAcessoExtProtocoloDTO), 'IdProtocolo');

      if (count($arrIdLiberados)) {
        $objAcessoExternoDTO->setStrSinParcial('S');
        $bolTodosLiberados = false;
      } else {
        $objAcessoExternoDTO->setStrSinParcial('N');
        $bolTodosLiberados = true;
      }

      $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
      $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
      $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($objAcessoExternoDTO->getDblIdProtocoloAtividade());
      $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);

      $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
      $arrIdProcedimentosAnexados = InfraArray::converterArrInfraDTO($objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO), 'IdProtocolo2');

      $objProcedimentoDTO = new ProcedimentoDTO();
      $objProcedimentoDTO->retStrNomeTipoProcedimento();
      $objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
      $objProcedimentoDTO->retDtaGeracaoProtocolo();
      $objProcedimentoDTO->retStrStaNivelAcessoGlobalProtocolo();

      $objProcedimentoDTO->setDblIdProcedimento($objAcessoExternoDTO->getDblIdProtocoloAtividade());

      $bolConsultandoDocumento = false;

      if ($parObjAcessoExternoDTO->isSetDblIdProcedimentoAnexadoConsulta()) {

        $objProcedimentoDTO->setArrDblIdProtocoloAssociado(array($parObjAcessoExternoDTO->getDblIdProcedimentoAnexadoConsulta()));

      } else if ($parObjAcessoExternoDTO->isSetDblIdProtocoloConsulta()) {

        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->retDblIdProtocolo();
        $objProtocoloDTO->retStrStaProtocolo();
        $objProtocoloDTO->setDblIdProtocolo($parObjAcessoExternoDTO->getDblIdProtocoloConsulta());

        $objProtocoloRN = new ProtocoloRN();
        $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

        $dblIdProcessoAnexado = null;

        if ($objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_PROCEDIMENTO) {

          $dblIdProcessoAnexado = $parObjAcessoExternoDTO->getDblIdProtocoloConsulta();

        } else {

          $bolConsultandoDocumento = true;

          $objDocumentoDTO = new DocumentoDTO();
          $objDocumentoDTO->retDblIdProcedimento();
          $objDocumentoDTO->setDblIdDocumento($parObjAcessoExternoDTO->getDblIdProtocoloConsulta());

          $objDocumentoRN = new DocumentoRN();
          $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

          if ($objDocumentoDTO->getDblIdProcedimento() != $objAcessoExternoDTO->getDblIdProtocoloAtividade()) {

            $dblIdProcessoAnexado = $objDocumentoDTO->getDblIdProcedimento();

          }

          $objProcedimentoDTO->setArrDblIdProtocoloAssociado(array($parObjAcessoExternoDTO->getDblIdProtocoloConsulta()));
        }

        if ($dblIdProcessoAnexado != null) {

          $objProcedimentoDTO->setDblIdProcedimento(null);

          if (!in_array($dblIdProcessoAnexado, $arrIdProcedimentosAnexados)) {
            throw new InfraException('Processo solicitado n?o est? anexado ao processo original.');
          }

          $objAcessoExternoDTOAnexado = new AcessoExternoDTO();
          $objAcessoExternoDTOAnexado->setNumIdAcessoExterno($objAcessoExternoDTO->getNumIdAcessoExterno());
          $objAcessoExternoDTOAnexado->setDblIdProcedimentoAnexadoConsulta($dblIdProcessoAnexado);
          $objAcessoExternoDTOAnexado = $this->consultarProcessoAcessoExterno($objAcessoExternoDTOAnexado);

          $objProcedimentoDTOPai = $objAcessoExternoDTOAnexado->getObjProcedimentoDTO();

          foreach ($objProcedimentoDTOPai->getArrObjRelProtocoloProtocoloDTO() as $objRelProtocoloProtocoloDTO) {
            if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO &&
                $objRelProtocoloProtocoloDTO->getStrSinAcessoExterno() == 'S' &&
                $objRelProtocoloProtocoloDTO->getDblIdProtocolo2() == $dblIdProcessoAnexado
            ) {

              $objProcedimentoDTO->setDblIdProcedimento($dblIdProcessoAnexado);
              $bolTodosLiberados = true;
              break;
            }
          }
        }
      }

      $objProcedimentoDTO->setStrSinDocTodos('S');
      $objProcedimentoDTO->setStrSinProcAnexados('S');

      $objProcedimentoRN = new ProcedimentoRN();
      $arrObjProcedimentoDTO = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);


      if (count($arrObjProcedimentoDTO) == 0) {
        throw new InfraException('Processo n?o encontrado.');
      }

      $objProcedimentoDTO = $arrObjProcedimentoDTO[0];

      $arrRet = array();

      $arrObjRelProtocoloProtocoloDTO = $objProcedimentoDTO->getArrObjRelProtocoloProtocoloDTO();

      if (count($arrObjRelProtocoloProtocoloDTO)) {

        $arrAcessoPermitidoModulos = array();
        $arrAcessoNegadoModulos = array();

        if (count($SEI_MODULOS)) {

          $arrObjProcedimentoAPI = array();
          $arrObjDocumentoAPI = array();

          foreach ($arrObjRelProtocoloProtocoloDTO as $objRelProtocoloProtocoloDTO) {

            if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO) {

              $objDocumentoDTO = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();

              $objDocumentoAPI = new DocumentoAPI();
              $objDocumentoAPI->setIdDocumento($objDocumentoDTO->getDblIdDocumento());
              $objDocumentoAPI->setIdProcedimento($objDocumentoDTO->getDblIdProcedimento());
              $objDocumentoAPI->setIdSerie($objDocumentoDTO->getNumIdSerie());
              $objDocumentoAPI->setIdUnidadeGeradora($objDocumentoDTO->getNumIdUnidadeGeradoraProtocolo());
              $objDocumentoAPI->setSinAssinado($objDocumentoDTO->getStrSinAssinado());
              $objDocumentoAPI->setSinPublicado($objDocumentoDTO->getStrSinPublicado());
              $objDocumentoAPI->setTipo($objDocumentoDTO->getStrStaProtocoloProtocolo());
              $objDocumentoAPI->setSubTipo($objDocumentoDTO->getStrStaDocumento());
              $objDocumentoAPI->setNivelAcesso($objDocumentoDTO->getStrStaNivelAcessoGlobalProtocolo());
              $arrObjDocumentoAPI[] = $objDocumentoAPI;

            } else if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO) {

              $objProcedimentoDTOAnexado = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();

              $objProcedimentoAPI = new ProcedimentoAPI();
              $objProcedimentoAPI->setIdProcedimento($objProcedimentoDTOAnexado->getDblIdProcedimento());
              $objProcedimentoAPI->setIdTipoProcedimento($objProcedimentoDTOAnexado->getNumIdTipoProcedimento());
              $objProcedimentoAPI->setIdUnidadeGeradora($objProcedimentoDTOAnexado->getNumIdUnidadeGeradoraProtocolo());
              $objProcedimentoAPI->setNivelAcesso($objProcedimentoDTOAnexado->getStrStaNivelAcessoGlobalProtocolo());
              $arrObjProcedimentoAPI[] = $objProcedimentoAPI;

            }
          }

          foreach ($SEI_MODULOS as $strModulo => $seiModulo) {
            if (($arr = $seiModulo->executar('verificarAcessoProtocoloExterno', $arrObjProcedimentoAPI, $arrObjDocumentoAPI)) != null) {
              foreach ($arr as $dblIdProtocoloModulo => $numTipoAcessoModulo) {

                if ($numTipoAcessoModulo == SeiIntegracao::$TAM_PERMITIDO) {

                  if (!isset($arrAcessoPermitidoModulos[$dblIdProtocoloModulo])) {
                    $arrAcessoPermitidoModulos[$dblIdProtocoloModulo] = array();
                  }

                  $arrAcessoPermitidoModulos[$dblIdProtocoloModulo][] = $strModulo;

                } else if ($numTipoAcessoModulo == SeiIntegracao::$TAM_NEGADO) {

                  if (!isset($arrAcessoNegadoModulos[$dblIdProtocoloModulo])) {
                    $arrAcessoNegadoModulos[$dblIdProtocoloModulo] = array();
                  }

                  $arrAcessoNegadoModulos[$dblIdProtocoloModulo][] = $strModulo;

                } else {
                  throw new InfraException('Tipo de acesso ['.$numTipoAcessoModulo.'] retornado pelo m?dulo ['.$strModulo.'] inv?lido.');
                }
              }
            }
          }
        }

        $objDocumentoRN = new DocumentoRN();

        foreach ($arrObjRelProtocoloProtocoloDTO as $objRelProtocoloProtocoloDTO) {

          $bolMostrarMetadados = true;

          $objRelProtocoloProtocoloDTO->setStrSinAcessoExterno('N');

          $bolAcesso = ($bolTodosLiberados || in_array($objRelProtocoloProtocoloDTO->getDblIdProtocolo2(), $arrIdLiberados));

          if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO) {

            $objDocumentoDTO = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();

            if (($bolAcesso && $objDocumentoRN->verificarSelecaoAcessoExterno($objDocumentoDTO))
                ||
                ($arrAcessoPermitidoModulos[$objRelProtocoloProtocoloDTO->getDblIdProtocolo2()] && $objDocumentoDTO->getStrStaEstadoProtocolo() != ProtocoloRN::$TE_DOCUMENTO_CANCELADO)
                ||
                ($objDocumentoDTO->getDblIdDocumento() == $objAcessoExternoDTO->getDblIdDocumento() && $objDocumentoRN->verificarSelecaoAssinaturaExterna($objDocumentoDTO))
            ) {

              $objRelProtocoloProtocoloDTO->setStrSinAcessoExterno('S');

              //consultando um documento espec?fico se n?o tiver retorna vazio
            } else if ($bolConsultandoDocumento) {

              if ($objDocumentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_DOCUMENTO_CANCELADO) {
                throw new InfraException('Documento foi cancelado.', null, null, false);
              }

              if ($objDocumentoRN->verificarConteudoGerado($objDocumentoDTO) && $objDocumentoDTO->getStrSinAssinado() == 'N') {
                throw new InfraException('Documento sem assinatura.', null, null, false);
              }

              break;
            }

            //se nao tiver acesso n?o mostrar metadados de rascunhos
            if ($objRelProtocoloProtocoloDTO->getStrSinAcessoExterno() == 'N' && $objDocumentoRN->verificarConteudoGerado($objDocumentoDTO) && $objDocumentoDTO->getStrSinAssinado() == 'N' && $objDocumentoDTO->getDblIdDocumento() != $objAcessoExternoDTO->getDblIdDocumento()) {
              $bolMostrarMetadados = false;
            }

          } else if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO) {

            if ($bolAcesso || $arrAcessoPermitidoModulos[$objRelProtocoloProtocoloDTO->getDblIdProtocolo2()]) {
              $objRelProtocoloProtocoloDTO->setStrSinAcessoExterno('S');
            }
          }

          //negacao de modulos tem prioridade
          if ($arrAcessoNegadoModulos[$objRelProtocoloProtocoloDTO->getDblIdProtocolo2()]) {
            $objRelProtocoloProtocoloDTO->setStrSinAcessoExterno('N');
          }

          if ($bolMostrarMetadados) {
            $arrRet[] = $objRelProtocoloProtocoloDTO;
          }
        }
      }

      $objProcedimentoDTO->setArrObjRelProtocoloProtocoloDTO($arrRet);

      $objAcessoExternoDTO->setObjProcedimentoDTO($objProcedimentoDTO);

      return $objAcessoExternoDTO;

    } catch (Exception $e) {
      throw new InfraException('Erro listando protocolos de acesso externo.', $e);
    }
  }

  protected function listarDocumentosControleAcessoConectado(AcessoExternoDTO $parObjAcessoExternoDTO)
  {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_listar', __METHOD__, $parObjAcessoExternoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->retNumIdUsuario();
      $objUsuarioDTO->retStrSigla();
      $objUsuarioDTO->retStrNome();
      $objUsuarioDTO->retStrStaTipo();
      $objUsuarioDTO->retNumIdContato();
      $objUsuarioDTO->setNumIdUsuario($parObjAcessoExternoDTO->getNumIdUsuarioExterno());

      $objUsuarioRN = new UsuarioRN();
      $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);

      if ($objUsuarioDTO == null) {
        throw new InfraException('Usu?rio externo n?o encontrado.', null, $parObjAcessoExternoDTO->__toString());
      }

      if ($objUsuarioDTO->getStrStaTipo() == UsuarioRN::$TU_EXTERNO_PENDENTE) {
        $objInfraException->lancarValidacao('Usu?rio externo "'.$objUsuarioDTO->getStrSigla().'" ainda n?o foi liberado.');
      }

      if ($objUsuarioDTO->getStrStaTipo() != UsuarioRN::$TU_EXTERNO) {
        $objInfraException->lancarValidacao('Usu?rio "'.$objUsuarioDTO->getStrSigla().'" n?o ? um usu?rio externo.');
      }

      $objAcessoExternoDTO = new AcessoExternoDTO();
      $objAcessoExternoDTO->retNumIdAcessoExterno();
      $objAcessoExternoDTO->retDblIdProtocoloAtividade();
      $objAcessoExternoDTO->retDblIdDocumento();
      $objAcessoExternoDTO->retStrSinProcesso();
      $objAcessoExternoDTO->retDthAberturaAtividade();
      $objAcessoExternoDTO->retDtaValidade();
      //$objAcessoExternoDTO->retStrSiglaUnidade();
      //$objAcessoExternoDTO->retStrDescricaoUnidade();
      $objAcessoExternoDTO->setStrStaTipo(array(AcessoExternoRN::$TA_ASSINATURA_EXTERNA, AcessoExternoRN::$TA_USUARIO_EXTERNO), InfraDTO::$OPER_IN);
      $objAcessoExternoDTO->setNumIdContatoParticipante($objUsuarioDTO->getNumIdContato());
      $objAcessoExternoDTO->setOrdDthAberturaAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);

      if ($parObjAcessoExternoDTO->isSetDblIdDocumento()) {
        $objAcessoExternoDTO->setDblIdDocumento($parObjAcessoExternoDTO->getDblIdDocumento());
      }

      //pagina??o
      $objAcessoExternoDTO->setNumMaxRegistrosRetorno($parObjAcessoExternoDTO->getNumMaxRegistrosRetorno());
      $objAcessoExternoDTO->setNumPaginaAtual($parObjAcessoExternoDTO->getNumPaginaAtual());

      $arrObjAcessoExternoDTO = $this->listar($objAcessoExternoDTO);

      //pagina??o
      $parObjAcessoExternoDTO->setNumTotalRegistros($objAcessoExternoDTO->getNumTotalRegistros());
      $parObjAcessoExternoDTO->setNumRegistrosPaginaAtual($objAcessoExternoDTO->getNumRegistrosPaginaAtual());

      if (count($arrObjAcessoExternoDTO)) {

        //Carregar dados do cabe?alho
        $objProcedimentoDTO = new ProcedimentoDTO();
        $objProcedimentoDTO->retStrNomeTipoProcedimento();
        $objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();

        $objProcedimentoDTO->setDblIdProcedimento(InfraArray::converterArrInfraDTO($arrObjAcessoExternoDTO, 'IdProtocoloAtividade'), InfraDTO::$OPER_IN);
        $objProcedimentoDTO->setStrSinDocTodos('S');

        $arrIdDocumentos = array_values(array_filter(InfraArray::converterArrInfraDTO($arrObjAcessoExternoDTO, 'IdDocumento')));
        if (count($arrIdDocumentos)) {
          $objProcedimentoDTO->setArrDblIdProtocoloAssociado($arrIdDocumentos);
        }

        $objProcedimentoRN = new ProcedimentoRN();
        $arrObjProcedimentoDTO = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);

        foreach ($arrObjAcessoExternoDTO as $objAcessoExternoDTO) {
          foreach ($arrObjProcedimentoDTO as $objProcedimentoDTO) {
            if ($objAcessoExternoDTO->getDblIdProtocoloAtividade() == $objProcedimentoDTO->getDblIdProcedimento()) {

              $objAcessoExternoDTO->setObjProcedimentoDTO($objProcedimentoDTO);

              $arrObjDocumentoDTO = $objProcedimentoDTO->getArrObjDocumentoDTO();
              foreach ($arrObjDocumentoDTO as $objDocumentoDTO) {
                if ($objAcessoExternoDTO->getDblIdDocumento() == $objDocumentoDTO->getDblIdDocumento()) {
                  $objAcessoExternoDTO->setObjDocumentoDTO($objDocumentoDTO);
                }
              }
              break;
            }
          }
        }
      }

      //Auditoria

      return $arrObjAcessoExternoDTO;

    } catch (Exception $e) {
      throw new InfraException('Erro listando documentos para assinatura externa.', $e);
    }
  }

  protected function listarDisponibilizacoesConectado(AcessoExternoDTO $parObjAcessoExternoDTO)
  {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_listar', __METHOD__, $parObjAcessoExternoDTO);

      $objAcessoExternoDTO = new AcessoExternoDTO();
      $objAcessoExternoDTO->setBolExclusaoLogica(false);
      $objAcessoExternoDTO->retNumIdAcessoExterno();
      $objAcessoExternoDTO->retStrSiglaContato();
      $objAcessoExternoDTO->retStrNomeContato();
      $objAcessoExternoDTO->retStrSiglaUnidade();
      $objAcessoExternoDTO->retStrDescricaoUnidade();
      $objAcessoExternoDTO->retNumIdAtividade();
      $objAcessoExternoDTO->retDthAberturaAtividade();
      $objAcessoExternoDTO->retNumIdTarefaAtividade();
      $objAcessoExternoDTO->retStrEmailDestinatario();
      $objAcessoExternoDTO->retDtaValidade();
      $objAcessoExternoDTO->retDblIdProtocoloAtividade();
      $objAcessoExternoDTO->retStrSinAtivo();

      $objAcessoExternoDTO->setStrStaTipo(array(AcessoExternoRN::$TA_INTERESSADO,
          AcessoExternoRN::$TA_DESTINATARIO_ISOLADO,
          AcessoExternoRN::$TA_USUARIO_EXTERNO), InfraDTO::$OPER_IN);

      $objAcessoExternoDTO->setDblIdProtocoloAtividade($parObjAcessoExternoDTO->getDblIdProtocoloAtividade());

      $objAcessoExternoDTO->setOrdDthAberturaAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);

      $objAcessoExternoRN = new AcessoExternoRN();
      $arrObjAcessoExternoDTO = $objAcessoExternoRN->listar($objAcessoExternoDTO);

      if (count($arrObjAcessoExternoDTO)) {

        $objAtributoAndamentoRN = new AtributoAndamentoRN();

        foreach ($arrObjAcessoExternoDTO as $objAcessoExternoDTO) {

          if ($objAcessoExternoDTO->getNumIdTarefaAtividade() == TarefaRN::$TI_LIBERACAO_ACESSO_EXTERNO) {
            $objAcessoExternoDTO->setDthCancelamento(null);
          } else {
            $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
            $objAtributoAndamentoDTO->retStrValor();
            $objAtributoAndamentoDTO->setStrNome('DATA_HORA');
            $objAtributoAndamentoDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());

            $objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);
            $objAcessoExternoDTO->setDthCancelamento($objAtributoAndamentoDTO->getStrValor());
          }
        }

        $objRelAcessoExtProtocoloDTO = new RelAcessoExtProtocoloDTO();
        $objRelAcessoExtProtocoloDTO->retNumIdAcessoExterno();
        $objRelAcessoExtProtocoloDTO->retStrProtocoloFormatadoProtocolo();
        $objRelAcessoExtProtocoloDTO->setNumIdAcessoExterno(InfraArray::converterArrInfraDTO($arrObjAcessoExternoDTO, 'IdAcessoExterno'), InfraDTO::$OPER_IN);

        $objRelAcessoExtProtocoloRN = new RelAcessoExtProtocoloRN();
        $arrObjRelAcessoExtProtocoloDTO = InfraArray::indexarArrInfraDTO($objRelAcessoExtProtocoloRN->listar($objRelAcessoExtProtocoloDTO), 'IdAcessoExterno', true);

        foreach ($arrObjAcessoExternoDTO as $objAcessoExternoDTO) {
          if (isset($arrObjRelAcessoExtProtocoloDTO[$objAcessoExternoDTO->getNumIdAcessoExterno()])) {
            $objAcessoExternoDTO->setArrObjRelAcessoExtProtocoloDTO($arrObjRelAcessoExtProtocoloDTO[$objAcessoExternoDTO->getNumIdAcessoExterno()]);
          } else {
            $objAcessoExternoDTO->setArrObjRelAcessoExtProtocoloDTO(array());
          }
        }
      }


      return $arrObjAcessoExternoDTO;

    } catch (Exception $e) {
      throw new InfraException('Erro listando disponibiliza??es de acesso externo.', $e);
    }
  }

  protected function cancelarDisponibilizacaoControlado($parArrObjAcessoExternoDTO)
  {
    try {

      global $SEI_MODULOS;

      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_cancelar', __METHOD__, $parArrObjAcessoExternoDTO);

      $objInfraException = new InfraException();

      $objAcessoExternoDTO = new AcessoExternoDTO();
      $objAcessoExternoDTO->setBolExclusaoLogica(false);
      $objAcessoExternoDTO->retNumIdAcessoExterno();
      $objAcessoExternoDTO->retNumIdAtividade();
      $objAcessoExternoDTO->retDblIdProtocoloAtividade();
      $objAcessoExternoDTO->retNumIdTarefaAtividade();
      $objAcessoExternoDTO->retNumIdUnidadeAtividade();
      $objAcessoExternoDTO->retNumIdContatoParticipante();
      $objAcessoExternoDTO->retStrNomeContato();
      $objAcessoExternoDTO->retStrStaTipo();
      $objAcessoExternoDTO->retDblIdDocumento();
      $objAcessoExternoDTO->retStrProtocoloDocumentoFormatado();

      $objAcessoExternoDTO->setNumIdAcessoExterno(InfraArray::converterArrInfraDTO($parArrObjAcessoExternoDTO, 'IdAcessoExterno'), InfraDTO::$OPER_IN);

      $arrObjAcessoExternoDTO = InfraArray::indexarArrInfraDTO($this->listar($objAcessoExternoDTO), 'IdAcessoExterno');


      foreach ($parArrObjAcessoExternoDTO as $parObjAcessoExternoDTO) {

        $objAcessoExternoDTO = $arrObjAcessoExternoDTO[$parObjAcessoExternoDTO->getNumIdAcessoExterno()];

        if ($objAcessoExternoDTO == null) {
          throw new InfraException('Registro de acesso externo ['.$parObjAcessoExternoDTO->getNumIdAcessoExterno().'] n?o encontrado.');
        }

        $objAcessoExternoDTO->setStrMotivo($parObjAcessoExternoDTO->getStrMotivo());

        if ($objAcessoExternoDTO->getStrStaTipo() != AcessoExternoRN::$TA_INTERESSADO &&
            $objAcessoExternoDTO->getStrStaTipo() != AcessoExternoRN::$TA_DESTINATARIO_ISOLADO &&
            $objAcessoExternoDTO->getStrStaTipo() != AcessoExternoRN::$TA_USUARIO_EXTERNO
        ) {
          $objInfraException->adicionarValidacao('Registro ['.$objAcessoExternoDTO->getNumIdAcessoExterno().'] n?o ? uma Disponibiliza??o de Acesso Externo.');
        }

        if ($objAcessoExternoDTO->getNumIdTarefaAtividade() == TarefaRN::$TI_LIBERACAO_ACESSO_EXTERNO_CANCELADA) {
          $objInfraException->adicionarValidacao('Disponibiliza??o de acesso externo para "'.$objAcessoExternoDTO->getStrNomeContato().'" j? consta como cancelada.');
        } else if ($objAcessoExternoDTO->getNumIdTarefaAtividade() != TarefaRN::$TI_LIBERACAO_ACESSO_EXTERNO) {
          $objInfraException->adicionarValidacao('Andamento do processo ['.$objAcessoExternoDTO->getNumIdTarefaAtividade().'] n?o ? uma Disponibiliza??o de Acesso Externo.');
        }

        if ($objAcessoExternoDTO->getNumIdUnidadeAtividade() != SessaoSEI::getInstance()->getNumIdUnidadeAtual()) {
          $objInfraException->adicionarValidacao('Disponibiliza??o de acesso externo para o interessado "'.$objAcessoExternoDTO->getStrNomeContato().'" n?o foi concedida pela unidade atual.');
        }
      }
      $objInfraException->lancarValidacoes();


      $strDataHoraAtual = InfraData::getStrDataHoraAtual();

      $objAtividadeRN = new AtividadeRN();
      $objAtributoAndamentoRN = new AtributoAndamentoRN();
      $objAcessoExternoBD = new AcessoExternoBD($this->getObjInfraIBanco());
      $arrObjAcessoExternoAPI = array();
      foreach ($arrObjAcessoExternoDTO as $objAcessoExternoDTO) {

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->retStrNome();
        $objAtributoAndamentoDTO->retStrValor();
        $objAtributoAndamentoDTO->retStrIdOrigem();
        $objAtributoAndamentoDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());

        $arrObjAtributoAndamentoDTO = $objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO);

        foreach ($arrObjAtributoAndamentoDTO as $objAtributoAndamentoDTO) {
          if ($objAtributoAndamentoDTO->getStrNome() == 'MOTIVO') {
            $objAtributoAndamentoDTO->setStrValor($objAcessoExternoDTO->getStrMotivo());
            break;
          }
        }

        //lan?a andamento para o usu?rio atual registrando o cancelamento da libera??o
        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDblIdProtocolo($objAcessoExternoDTO->getDblIdProtocoloAtividade());
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdUsuario(null);
        $objAtividadeDTO->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAtividadeDTO->setDtaPrazo(null);

        $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);

        $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_CANCELAMENTO_LIBERACAO_ACESSO_EXTERNO);

        $ret = $objAtividadeRN->gerarInternaRN0727($objAtividadeDTO);

        //altera andamento original de concess?o ou transfer?ncia
        $objAtividadeDTO = new AtividadeDTO();

        $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_LIBERACAO_ACESSO_EXTERNO_CANCELADA);

        $objAtividadeDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());
        $objAtividadeRN->mudarTarefa($objAtividadeDTO);

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('USUARIO');
        $objAtributoAndamentoDTO->setStrValor(SessaoSEI::getInstance()->getStrSiglaUsuario().'?'.SessaoSEI::getInstance()->getStrNomeUsuario());
        $objAtributoAndamentoDTO->setStrIdOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAtributoAndamentoDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());
        $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('DATA_HORA');
        $objAtributoAndamentoDTO->setStrValor($strDataHoraAtual);
        $objAtributoAndamentoDTO->setStrIdOrigem($ret->getNumIdAtividade()); //relaciona com o andamento de cassa??o
        $objAtributoAndamentoDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());
        $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);

        $objAcessoExternoBD->desativar($objAcessoExternoDTO);

        $objAcessoExternoAPI = new AcessoExternoAPI();
        $objAcessoExternoAPI->setIdAcessoExterno($objAcessoExternoDTO->getNumIdAcessoExterno());

        $objProcedimentoAPI = new ProcedimentoAPI();
        $objProcedimentoAPI->setIdProcedimento($objAcessoExternoDTO->getDblIdProtocoloAtividade());
        $objAcessoExternoAPI->setProcedimento($objProcedimentoAPI);

        $arrObjAcessoExternoAPI[] = $objAcessoExternoAPI;
      }

      foreach ($SEI_MODULOS as $seiModulo) {
        $seiModulo->executar('cancelarDisponibilizacaoAcessoExterno', $arrObjAcessoExternoAPI);
      }

    } catch (Exception $e) {
      throw new InfraException('Erro cancelando disponibiliza??o de acesso externo.', $e);
    }
  }

  protected function listarLiberacoesAssinaturaExternaConectado(AcessoExternoDTO $parObjAcessoExternoDTO)
  {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_listar', __METHOD__, $parObjAcessoExternoDTO);

      $objAcessoExternoDTO = new AcessoExternoDTO();
      $objAcessoExternoDTO->setBolExclusaoLogica(false);
      $objAcessoExternoDTO->retNumIdAcessoExterno();
      $objAcessoExternoDTO->retStrSiglaContato();
      $objAcessoExternoDTO->retStrNomeContato();
      $objAcessoExternoDTO->retStrSiglaUnidade();
      $objAcessoExternoDTO->retStrDescricaoUnidade();
      $objAcessoExternoDTO->retNumIdAtividade();
      $objAcessoExternoDTO->retDthAberturaAtividade();
      $objAcessoExternoDTO->retNumIdTarefaAtividade();
      $objAcessoExternoDTO->retStrSinProcesso();
      $objAcessoExternoDTO->retNumIdContatoParticipante();
      $objAcessoExternoDTO->retDblIdProtocoloAtividade();
      $objAcessoExternoDTO->retStrSinAtivo();

      $objAcessoExternoDTO->setStrStaTipo(AcessoExternoRN::$TA_ASSINATURA_EXTERNA);
      $objAcessoExternoDTO->setDblIdDocumento($parObjAcessoExternoDTO->getDblIdDocumento());

      $objAcessoExternoRN = new AcessoExternoRN();
      $arrObjAcessoExternoDTO = $objAcessoExternoRN->listar($objAcessoExternoDTO);

      if (count($arrObjAcessoExternoDTO)) {

        $objAssinaturaRN = new AssinaturaRN();
        $objAtributoAndamentoRN = new AtributoAndamentoRN();

        foreach ($arrObjAcessoExternoDTO as $objAcessoExternoDTO) {

          $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
          $objAtributoAndamentoDTO->retStrIdOrigem();
          $objAtributoAndamentoDTO->setStrNome('DOCUMENTO');
          $objAtributoAndamentoDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());
          $objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);

          $objAssinaturaDTO = new AssinaturaDTO();
          $objAssinaturaDTO->retDthAberturaAtividade();
          $objAssinaturaDTO->setDblIdDocumento($objAtributoAndamentoDTO->getStrIdOrigem());
          $objAssinaturaDTO->setNumIdContatoUsuario($objAcessoExternoDTO->getNumIdContatoParticipante());

          $objAssinaturaDTO = $objAssinaturaRN->consultarRN1322($objAssinaturaDTO);

          if ($objAssinaturaDTO != null) {
            $objAcessoExternoDTO->setDthUtilizacao($objAssinaturaDTO->getDthAberturaAtividade());
          } else {
            $objAcessoExternoDTO->setDthUtilizacao(null);
          }

          if ($objAcessoExternoDTO->getNumIdTarefaAtividade() == TarefaRN::$TI_LIBERACAO_ASSINATURA_EXTERNA_CANCELADA) {
            $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
            $objAtributoAndamentoDTO->retStrValor();
            $objAtributoAndamentoDTO->setStrNome('DATA_HORA');
            $objAtributoAndamentoDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());

            $objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);
            $objAcessoExternoDTO->setDthCancelamento($objAtributoAndamentoDTO->getStrValor());
          } else {
            $objAcessoExternoDTO->setDthCancelamento(null);
          }
        }

        $objRelAcessoExtProtocoloDTO = new RelAcessoExtProtocoloDTO();
        $objRelAcessoExtProtocoloDTO->retNumIdAcessoExterno();
        $objRelAcessoExtProtocoloDTO->retStrProtocoloFormatadoProtocolo();
        $objRelAcessoExtProtocoloDTO->setNumIdAcessoExterno(InfraArray::converterArrInfraDTO($arrObjAcessoExternoDTO, 'IdAcessoExterno'), InfraDTO::$OPER_IN);

        $objRelAcessoExtProtocoloRN = new RelAcessoExtProtocoloRN();
        $arrObjRelAcessoExtProtocoloDTO = InfraArray::indexarArrInfraDTO($objRelAcessoExtProtocoloRN->listar($objRelAcessoExtProtocoloDTO), 'IdAcessoExterno', true);

        foreach ($arrObjAcessoExternoDTO as $objAcessoExternoDTO) {
          if (isset($arrObjRelAcessoExtProtocoloDTO[$objAcessoExternoDTO->getNumIdAcessoExterno()])) {
            $objAcessoExternoDTO->setArrObjRelAcessoExtProtocoloDTO($arrObjRelAcessoExtProtocoloDTO[$objAcessoExternoDTO->getNumIdAcessoExterno()]);
          } else {
            $objAcessoExternoDTO->setArrObjRelAcessoExtProtocoloDTO(array());
          }
        }

      }

      return $arrObjAcessoExternoDTO;

    } catch (Exception $e) {
      throw new InfraException('Erro listando libera??es de assinatura externa.', $e);
    }
  }

  protected function cancelarLiberacaoAssinaturaExternaControlado($parArrObjAcessoExternoDTO)
  {
    try {

      global $SEI_MODULOS;

      SessaoSEI::getInstance()->validarAuditarPermissao('assinatura_externa_cancelar', __METHOD__, $parArrObjAcessoExternoDTO);

      $objInfraException = new InfraException();

      $objAcessoExternoDTO = new AcessoExternoDTO();
      $objAcessoExternoDTO->setBolExclusaoLogica(false);
      $objAcessoExternoDTO->retNumIdAcessoExterno();
      $objAcessoExternoDTO->retNumIdAtividade();
      $objAcessoExternoDTO->retDblIdProtocoloAtividade();
      $objAcessoExternoDTO->retNumIdTarefaAtividade();
      $objAcessoExternoDTO->retNumIdUnidadeAtividade();
      $objAcessoExternoDTO->retNumIdContatoParticipante();
      $objAcessoExternoDTO->retStrStaTipo();
      $objAcessoExternoDTO->retDblIdDocumento();
      $objAcessoExternoDTO->retStrProtocoloDocumentoFormatado();
      $objAcessoExternoDTO->retStrSinProcesso();

      $objAcessoExternoDTO->setNumIdAcessoExterno(InfraArray::converterArrInfraDTO($parArrObjAcessoExternoDTO, 'IdAcessoExterno'), InfraDTO::$OPER_IN);

      $arrObjAcessoExternoDTO = InfraArray::indexarArrInfraDTO($this->listar($objAcessoExternoDTO), 'IdAcessoExterno');


      $objUsuarioDTO = new UsuarioDTO();
      $objUsuarioDTO->setBolExclusaoLogica(false);
      $objUsuarioDTO->retNumIdUsuario();
      $objUsuarioDTO->retNumIdContato();
      $objUsuarioDTO->retStrSigla();
      $objUsuarioDTO->retStrNome();
      $objUsuarioDTO->setNumIdContato(InfraArray::converterArrInfraDTO($arrObjAcessoExternoDTO, 'IdContatoParticipante'), InfraDTO::$OPER_IN);

      $objUsuarioRN = new UsuarioRN();
      $arrObjUsuarioDTO = InfraArray::indexarArrInfraDTO($objUsuarioRN->listarRN0490($objUsuarioDTO), 'IdContato');


      foreach ($parArrObjAcessoExternoDTO as $parObjAcessoExternoDTO) {

        $objAcessoExternoDTO = $arrObjAcessoExternoDTO[$parObjAcessoExternoDTO->getNumIdAcessoExterno()];
        $objUsuarioDTO = $arrObjUsuarioDTO[$objAcessoExternoDTO->getNumIdContatoParticipante()];

        if ($objAcessoExternoDTO == null) {
          throw new InfraException('Registro de acesso externo ['.$parObjAcessoExternoDTO->getNumIdAcessoExterno().'] n?o encontrado.');
        }

        $objAcessoExternoDTO->setStrMotivo($parObjAcessoExternoDTO->getStrMotivo());

        if ($objAcessoExternoDTO->getStrStaTipo() != AcessoExternoRN::$TA_ASSINATURA_EXTERNA) {
          $objInfraException->adicionarValidacao('Registro ['.$objAcessoExternoDTO->getNumIdAcessoExterno().'] n?o ? uma Libera??o de Assinatura Externa.');
        }

        if ($objAcessoExternoDTO->getNumIdTarefaAtividade() == TarefaRN::$TI_LIBERACAO_ASSINATURA_EXTERNA_CANCELADA) {
          $objInfraException->adicionarValidacao('Libera??o de Assinatura Externa para o usu?rio "'.$objUsuarioDTO->getStrSigla().'" no documento '.$objAcessoExternoDTO->getStrProtocoloDocumentoFormatado().' j? consta como cancelada.');
        } else if ($objAcessoExternoDTO->getNumIdTarefaAtividade() != TarefaRN::$TI_LIBERACAO_ASSINATURA_EXTERNA) {
          $objInfraException->adicionarValidacao('Andamento do processo ['.$objAcessoExternoDTO->getNumIdTarefaAtividade().'] n?o ? uma Libera??o de Assinatura Externa.');
        }

        if ($objAcessoExternoDTO->getNumIdUnidadeAtividade() != SessaoSEI::getInstance()->getNumIdUnidadeAtual()) {
          $objInfraException->adicionarValidacao('Libera??o de Assinatura Externa para o usu?rio "'.$objUsuarioDTO->getStrSigla().'" no documento '.$objAcessoExternoDTO->getStrProtocoloDocumentoFormatado().' n?o foi concedida pela unidade atual.');
        }

        if ($objAcessoExternoDTO->getStrSinProcesso() == 'N') {
          $objAssinaturaDTO = new AssinaturaDTO();
          $objAssinaturaDTO->retStrSiglaUsuario();
          $objAssinaturaDTO->setNumIdUsuario($objUsuarioDTO->getNumIdUsuario());
          $objAssinaturaDTO->setDblIdDocumento($objAcessoExternoDTO->getDblIdDocumento());

          $objAssinaturaRN = new AssinaturaRN();
          $objAssinaturaDTO = $objAssinaturaRN->consultarRN1322($objAssinaturaDTO);

          if ($objAssinaturaDTO != null) {
            $objInfraException->adicionarValidacao('Usu?rio "'.$objAssinaturaDTO->getStrSiglaUsuario().'" j? assinou o documento '.$objAcessoExternoDTO->getStrProtocoloDocumentoFormatado().'.');
          }
        }
      }
      $objInfraException->lancarValidacoes();

      $strDataHoraAtual = InfraData::getStrDataHoraAtual();

      $objAtividadeRN = new AtividadeRN();
      $objAtributoAndamentoRN = new AtributoAndamentoRN();
      $objAcessoExternoBD = new AcessoExternoBD($this->getObjInfraIBanco());
      $arrObjAcessoExternoAPI = array();

      foreach ($arrObjAcessoExternoDTO as $objAcessoExternoDTO) {

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->retStrNome();
        $objAtributoAndamentoDTO->retStrValor();
        $objAtributoAndamentoDTO->retStrIdOrigem();
        $objAtributoAndamentoDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());

        $arrObjAtributoAndamentoDTO = $objAtributoAndamentoRN->listarRN1367($objAtributoAndamentoDTO);

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('MOTIVO');
        $objAtributoAndamentoDTO->setStrValor($objAcessoExternoDTO->getStrMotivo());
        $objAtributoAndamentoDTO->setStrIdOrigem(null); //relaciona com o andamento de cassa??o
        $arrObjAtributoAndamentoDTO[] = $objAtributoAndamentoDTO;

        //lan?a andamento para o usu?rio atual registrando o cancelamento da libera??o
        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setDblIdProtocolo($objAcessoExternoDTO->getDblIdProtocoloAtividade());
        $objAtividadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdUnidadeOrigem(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objAtividadeDTO->setNumIdUsuario(null);
        $objAtividadeDTO->setNumIdUsuarioOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAtividadeDTO->setDtaPrazo(null);

        $objAtividadeDTO->setArrObjAtributoAndamentoDTO($arrObjAtributoAndamentoDTO);
        $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_CANCELAMENTO_LIBERACAO_ASSINATURA_EXTERNA);

        $ret = $objAtividadeRN->gerarInternaRN0727($objAtividadeDTO);

        //altera andamento original de concess?o ou transfer?ncia
        $objAtividadeDTO = new AtividadeDTO();
        $objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_LIBERACAO_ASSINATURA_EXTERNA_CANCELADA);
        $objAtividadeDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());
        $objAtividadeRN->mudarTarefa($objAtividadeDTO);

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('USUARIO');
        $objAtributoAndamentoDTO->setStrValor(SessaoSEI::getInstance()->getStrSiglaUsuario().'?'.SessaoSEI::getInstance()->getStrNomeUsuario());
        $objAtributoAndamentoDTO->setStrIdOrigem(SessaoSEI::getInstance()->getNumIdUsuario());
        $objAtributoAndamentoDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());
        $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);

        $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
        $objAtributoAndamentoDTO->setStrNome('DATA_HORA');
        $objAtributoAndamentoDTO->setStrValor($strDataHoraAtual);
        $objAtributoAndamentoDTO->setStrIdOrigem($ret->getNumIdAtividade()); //relaciona com o andamento de cassa??o
        $objAtributoAndamentoDTO->setNumIdAtividade($objAcessoExternoDTO->getNumIdAtividade());
        $objAtributoAndamentoRN->cadastrarRN1363($objAtributoAndamentoDTO);


        $objAcessoExternoBD->desativar($objAcessoExternoDTO);

        $objAcessoExternoAPI = new AcessoExternoAPI();
        $objAcessoExternoAPI->setIdAcessoExterno($objAcessoExternoDTO->getNumIdAcessoExterno());

        $objProcedimentoAPI = new ProcedimentoAPI();
        $objProcedimentoAPI->setIdProcedimento($objAcessoExternoDTO->getDblIdProtocoloAtividade());
        $objAcessoExternoAPI->setProcedimento($objProcedimentoAPI);

        $objDocumentoAPI = new DocumentoAPI();
        $objDocumentoAPI->setIdDocumento($objAcessoExternoDTO->getDblIdDocumento());
        $objAcessoExternoAPI->setDocumento($objDocumentoAPI);

        $arrObjAcessoExternoAPI[] = $objAcessoExternoAPI;
      }

      foreach ($SEI_MODULOS as $seiModulo) {
        $seiModulo->executar('cancelarLiberacaoAssinaturaExterna', $arrObjAcessoExternoAPI);
      }


    } catch (Exception $e) {
      throw new InfraException('Erro cancelando libera??o de assinatura externa.', $e);
    }
  }


  /*
  protected function alterarControlado(AcessoExternoDTO $objAcessoExternoDTO){
    try {

      //Valida Permissao
         SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_alterar',__METHOD__,$objAcessoExternoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objAcessoExternoDTO->isSetNumIdAtividade()){
        $this->validarNumIdAtividade($objAcessoExternoDTO, $objInfraException);
      }
      if ($objAcessoExternoDTO->isSetNumIdParticipante()){
        $this->validarNumIdParticipante($objAcessoExternoDTO, $objInfraException);
      }
      if ($objAcessoExternoDTO->isSetDtaValidade()){
        $this->validarDtaValidade($objAcessoExternoDTO, $objInfraException);
      }
      if ($objAcessoExternoDTO->isSetStrEmailUnidade()){
        $this->validarStrEmailUnidade($objAcessoExternoDTO, $objInfraException);
      }
      if ($objAcessoExternoDTO->isSetStrEmailDestinatario()){
        $this->validarStrEmailDestinatario($objAcessoExternoDTO, $objInfraException);
      }
      if ($objAcessoExternoDTO->isSetStrHashInterno()){
        $this->validarStrHashInterno($objAcessoExternoDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      $objAcessoExternoBD = new AcessoExternoBD($this->getObjInfraIBanco());
      $objAcessoExternoBD->alterar($objAcessoExternoDTO);

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro alterando Acesso Externo.',$e);
    }
  }

 */

  protected function excluirControlado($arrObjAcessoExternoDTO)
  {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_excluir', __METHOD__, $arrObjAcessoExternoDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      for ($i = 0; $i < count($arrObjAcessoExternoDTO); $i++) {

        $objAcessoExternoDTO = new AcessoExternoDTO();
        $objAcessoExternoDTO->setBolExclusaoLogica(false);
        $objAcessoExternoDTO->retStrStaTipo();
        $objAcessoExternoDTO->retNumIdTarefaAtividade();
        $objAcessoExternoDTO->setNumIdAcessoExterno($arrObjAcessoExternoDTO[$i]->getNumIdAcessoExterno());

        $objAcessoExternoDTO = $this->consultar($objAcessoExternoDTO);

        if ($objAcessoExternoDTO->getStrStaTipo() != AcessoExternoRN::$TA_SISTEMA &&
            !($objAcessoExternoDTO->getStrStaTipo() == AcessoExternoRN::$TA_ASSINATURA_EXTERNA && $objAcessoExternoDTO->getNumIdTarefaAtividade() == TarefaRN::$TI_LIBERACAO_ASSINATURA_EXTERNA_CANCELADA)
        ) {
          throw new InfraException('Acesso Externo n?o pode ser exclu?do.');
        }
      }

      $objInfraException->lancarValidacoes();

      $objRelAcessoExtProtocoloRN = new RelAcessoExtProtocoloRN();

      for ($i = 0; $i < count($arrObjAcessoExternoDTO); $i++) {
        $objRelAcessoExtProtocoloDTO = new RelAcessoExtProtocoloDTO();
        $objRelAcessoExtProtocoloDTO->retNumIdAcessoExterno();
        $objRelAcessoExtProtocoloDTO->retDblIdProtocolo();
        $objRelAcessoExtProtocoloDTO->setNumIdAcessoExterno($arrObjAcessoExternoDTO[$i]->getNumIdAcessoExterno());
        $objRelAcessoExtProtocoloRN->excluir($objRelAcessoExtProtocoloRN->listar($objRelAcessoExtProtocoloDTO));
      }

      $objAcessoExternoBD = new AcessoExternoBD($this->getObjInfraIBanco());
      for ($i = 0; $i < count($arrObjAcessoExternoDTO); $i++) {
        $objAcessoExternoBD->excluir($arrObjAcessoExternoDTO[$i]);
      }

      //Auditoria

    } catch (Exception $e) {
      throw new InfraException('Erro excluindo Acesso Externo.', $e);
    }
  }

  protected function consultarConectado(AcessoExternoDTO $objAcessoExternoDTO)
  {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_consultar', __METHOD__, $objAcessoExternoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcessoExternoBD = new AcessoExternoBD($this->getObjInfraIBanco());
      $ret = $objAcessoExternoBD->consultar($objAcessoExternoDTO);

      //Auditoria

      return $ret;
    } catch (Exception $e) {
      throw new InfraException('Erro consultando Acesso Externo.', $e);
    }
  }

  protected function listarConectado(AcessoExternoDTO $objAcessoExternoDTO)
  {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_listar', __METHOD__, $objAcessoExternoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcessoExternoBD = new AcessoExternoBD($this->getObjInfraIBanco());
      $ret = $objAcessoExternoBD->listar($objAcessoExternoDTO);

      //Auditoria

      return $ret;

    } catch (Exception $e) {
      throw new InfraException('Erro listando Acessos Externos.', $e);
    }
  }

  protected function contarConectado(AcessoExternoDTO $objAcessoExternoDTO)
  {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_listar', __METHOD__, $objAcessoExternoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcessoExternoBD = new AcessoExternoBD($this->getObjInfraIBanco());
      $ret = $objAcessoExternoBD->contar($objAcessoExternoDTO);

      //Auditoria

      return $ret;
    } catch (Exception $e) {
      throw new InfraException('Erro contando Acessos Externos.', $e);
    }
  }

  public function listarValoresTipoAcessoExterno()
  {
    try {

      $arrObjTipoDTO = array();

      $objTipoDTO = new TipoDTO();
      $objTipoDTO->setStrStaTipo(self::$TA_INTERESSADO);
      $objTipoDTO->setStrDescricao('Interessado do Processo');
      $arrObjTipoDTO[] = $objTipoDTO;

      $objTipoDTO = new TipoDTO();
      $objTipoDTO->setStrStaTipo(self::$TA_USUARIO_EXTERNO);
      $objTipoDTO->setStrDescricao('Usu?rio Externo');
      $arrObjTipoDTO[] = $objTipoDTO;

      $objTipoDTO = new TipoDTO();
      $objTipoDTO->setStrStaTipo(self::$TA_DESTINATARIO_ISOLADO);
      $objTipoDTO->setStrDescricao('Destinat?rio Isolado');
      $arrObjTipoDTO[] = $objTipoDTO;

      $objTipoDTO = new TipoDTO();
      $objTipoDTO->setStrStaTipo(self::$TA_SISTEMA);
      $objTipoDTO->setStrDescricao('Sistema');
      $arrObjTipoDTO[] = $objTipoDTO;

      $objTipoDTO = new TipoDTO();
      $objTipoDTO->setStrStaTipo(self::$TA_ASSINATURA_EXTERNA);
      $objTipoDTO->setStrDescricao('Assinatura Externa de Documento');
      $arrObjTipoDTO[] = $objTipoDTO;

      return $arrObjTipoDTO;

    } catch (Exception $e) {
      throw new InfraException('Erro listando valores de Tipo de Acesso Externo.', $e);
    }
  }

  /*
  protected function desativarControlado($arrObjAcessoExternoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_desativar',__METHOD__,$arrObjAcessoExternoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcessoExternoBD = new AcessoExternoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAcessoExternoDTO);$i++){
        $objAcessoExternoBD->desativar($arrObjAcessoExternoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro desativando Acesso Externo.',$e);
    }
  }

  protected function reativarControlado($arrObjAcessoExternoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_reativar',__METHOD__,$arrObjAcessoExternoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcessoExternoBD = new AcessoExternoBD($this->getObjInfraIBanco());
      for($i=0;$i<count($arrObjAcessoExternoDTO);$i++){
        $objAcessoExternoBD->reativar($arrObjAcessoExternoDTO[$i]);
      }

      //Auditoria

    }catch(Exception $e){
      throw new InfraException('Erro reativando Acesso Externo.',$e);
    }
  }

  protected function bloquearControlado(AcessoExternoDTO $objAcessoExternoDTO){
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('acesso_externo_consultar',__METHOD__,$objAcessoExternoDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objAcessoExternoBD = new AcessoExternoBD($this->getObjInfraIBanco());
      $ret = $objAcessoExternoBD->bloquear($objAcessoExternoDTO);

      //Auditoria

      return $ret;
    }catch(Exception $e){
      throw new InfraException('Erro bloqueando Acesso Externo.',$e);
    }
  }

 */
}
?>