<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
 *
 * 14/04/2008 - criado por mga
 *
 * Vers�o do Gerador de C�digo: 1.14.0
 *
 * Vers�o no CVS: $Id$
 */

require_once dirname(__FILE__).'/../SEI.php';

class UnidadeDTO extends InfraDTO {

	public function getStrNomeTabela() {
		return 'unidade';
	}

	public function montar() {

		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUnidade',
                                   'id_unidade');

		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
																	'IdOrigem',
																	'id_origem');

		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdOrgao',
                                   'id_orgao');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdContato',
                                   'id_contato');

		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Sigla',
                                   'sigla');

		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Descricao',
                                   'descricao');
			
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinMailPendencia',
                                   'sin_mail_pendencia');

		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinAtivo',
                                   'sin_ativo');

		$this->adicionarAtributo(InfraDTO::$PREFIXO_ARR,
      												'ObjEmailUnidadeDTO');

		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinArquivamento',
                                   'sin_arquivamento');

		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinOuvidoria',
                                   'sin_ouvidoria');		

		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                           		     'SinProtocolo',
                            		   'sin_protocolo');
		
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinEnvioProcesso',
                                   'sin_envio_processo');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                  'IdxUnidade',
                                  'idx_unidade');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SiglaOrgao',
                                              'sigla',
                                              'orgao');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'DescricaoOrgao',
                                              'descricao',
                                              'orgao');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                              'SinEnvioProcessoOrgao',
                                              'sin_envio_processo',
                                              'orgao');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
        'IdContatoOrgao',
        'id_contato',
        'orgao');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
				'TimbreOrgao',
				'timbre',
				'orgao');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
        'SitioInternetOrgaoContato',
        'b.sitio_internet',
        'contato b');

    $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
        'NomeContato',
        'a.nome',
        'contato a');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
				'TelefoneFixoContato',
				'a.telefone_fixo',
				'contato a');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
				'TelefoneCelularContato',
				'a.telefone_celular',
				'contato a');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
				'SitioInternetContato',
				'a.sitio_internet',
				'contato a');

		$this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
				'IdCidadeContato',
				'a.id_cidade',
				'contato a');

		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,'CodigoSei','codigo_sei');
		$this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'SinProcessoAberto');
    $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'PalavrasPesquisa');

		$this->configurarPK('IdUnidade',InfraDTO::$TIPO_PK_INFORMADO);
    $this->configurarFK('IdContato','contato a','a.id_contato');
		$this->configurarFK('IdOrgao','orgao','id_orgao');
    $this->configurarFK('IdContatoOrgao','contato b','b.id_contato');

		$this->configurarExclusaoLogica('SinAtivo', 'N');

	}
}
?>