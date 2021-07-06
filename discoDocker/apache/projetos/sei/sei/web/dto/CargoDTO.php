<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 10/12/2007 - criado por fbv
*
* Vers�o do Gerador de C�digo: 1.10.1
*
* Vers�o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class CargoDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'cargo';
  }

  public function montar() {

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdCargo',
                                   'id_cargo');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                    'IdTratamento',
                                    'id_tratamento');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                  'IdVocativo',
                                  'id_vocativo');

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Expressao',
                                   'expressao');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                  'StaGenero',
                                  'sta_genero');

  	 $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinAtivo',
                                   'sin_ativo');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'ExpressaoTratamento',
                                              'expressao',
                                              'tratamento');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'ExpressaoVocativo',
                                              'expressao',
                                              'vocativo');

    $this->configurarPK('IdCargo', InfraDTO::$TIPO_PK_NATIVA );
    

    $this->configurarExclusaoLogica('SinAtivo', 'N');

    $this->configurarFK('IdTratamento', 'tratamento', 'id_tratamento', InfraDTO::$TIPO_FK_OPCIONAL);
    $this->configurarFK('IdVocativo', 'vocativo', 'id_vocativo', InfraDTO::$TIPO_FK_OPCIONAL);


  }
}
?>