<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
 *
 * 14/05/2015 - criado por mga
 *
 */

require_once dirname(__FILE__).'/../SEI.php';

class AnotacaoINT extends InfraINT {

  public static function montarIconeAnotacao($objAnotacaoDTO, $bolAcaoRegistrarAnotacao, $dblIdProtocolo, $strParametros = ''){

    $ret  = '';

    if ($objAnotacaoDTO!=null) {

      if ($bolAcaoRegistrarAnotacao) {
        $strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=anotacao_registrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_protocolo=' . $dblIdProtocolo . $strParametros);
      }else{
        $strLink = 'javascript:void(0);';
      }

      $ret = '<a href="'.$strLink.'" '.PaginaSEI::montarTitleTooltip($objAnotacaoDTO->getStrDescricao(),$objAnotacaoDTO->getStrSiglaUsuario()) . '><img src="imagens/' . ($objAnotacaoDTO->getStrSinPrioridade() == 'N' ? 'sei_anotacao_pequeno.gif' : 'sei_anotacao_prioridade_pequeno.gif') . '" class="imagemStatus" /></a>';

    }

    return $ret;
  }

}
?>