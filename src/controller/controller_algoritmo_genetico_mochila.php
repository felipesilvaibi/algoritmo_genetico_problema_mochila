<?php
/* 
 * Controlador único de processamento do algoritmo genético
 *
 * @package AlgoritmoGenetico
 * @subpackage Controller
 * @user felipesilvaibi
 * @since 14/03/2021
 */
class ControllerAlgoritmoGeneticoMochila {
    
    /**
     * Tamanho da população analisada
     */
    const TAMANHO_POPULACAO = 4;
    
    /**
     * Probabilidade de mutação de um filho a cada geração
     */
    const PROBABILIDADE_MUTACAO = 10;
    
    /**
     * Valor cem para o cálculo de porcentagem
     */
    const VALOR_CEM_PORCENTAGEM = 100;
    
    /**
     * Capacidade máxima da mochila (peso)
     * 
     * @tutorial
     * @var int 
     */
    private $capacidadeMaximaMochila;
    
    /**
     * Quantidade de gerações que o algoritmo deverá processar
     * 
     * @var int 
     */
    private $sequencialGeracaoCriterioParada;
    
    /**
     * Tipo do gene (cada tipo de gene gera um novo elemento no cromossomo)
     * 
     * @var ModelObjetoMochilaBase
     */
    private $tiposGene;
    
    /**
     * A geração é composta por (população, pais geração e filhos geração)
     * Obs: Um indivíduo da população corresponde a um cromossomo, esse por sua ver é um agrupador de tipos de gene
     * 
     * @var Array
     */
    private $geracao;
    
    /**
     * Melhor cromossomo de todas as gerações
     * 
     * @var Array
     */
    private $bestOf;
    
    /**
     * Sequencial de geração das populações (utilizado para o critério de parada)
     * 
     * @var int
     */
    private $sequencialGeracao;
    
    public function __construct() {
        ini_set('xdebug.max_nesting_level', 1000); 
        
        $this->capacidadeMaximaMochila         = 8;
        $this->sequencialGeracaoCriterioParada = 200;
        $this->geracao                         = [];
        $this->sequencialGeracao               = 1;
        
        $this->criaListaTipoGeneCromossomo();
    }
    
    /**
     * Cria a lista de tipos de gene do cromossomo (equivalente aos possíveis objetos da mochila)
     * 
     * @tutorial 1. Faz o require de todos os arquivos da pasta Model (que extendem ModelObjetoMochilaBase)
     *           2. Atribui ao array de tipos de gene uma instância de cada objeto (exceto ModelObjetoMochilaBase.php)
     */
    private function criaListaTipoGeneCromossomo() {
        /* Informar o caminho até a pasta Model (modelos de objeto da mochila) */
        $sDiretorioPadraoObjetosMochila = 'src/model/';
        $aDirPhpFiles                   = glob($sDiretorioPadraoObjetosMochila . '*.php');

        $this->tiposGene = [];
        foreach ($aDirPhpFiles as $sDirPhpFile) {
            $sNomeArquivo = str_replace($sDiretorioPadraoObjetosMochila, '', $sDirPhpFile);
            require $sDiretorioPadraoObjetosMochila . $sNomeArquivo;

            $sNomeClasse = preg_replace('/\s+/', '', (ucwords(str_replace('_', ' ', str_replace('.php', '', $sNomeArquivo)))));
            if ($this->isArquivoObjetoInstanciavel($sNomeClasse)) {
                $this->tiposGene[] = new $sNomeClasse();
            }
        }
    }
    
    /**
     * Verifica se o arquivo de objeto é instanciável
     * 
     * @param String $sNomeArquivo
     * @return boolean
     */
    private function isArquivoObjetoInstanciavel($sNomeArquivo) {
        $oReflectionClass = new ReflectionClass($sNomeArquivo);
        return !$oReflectionClass->isAbstract();
    }
    
    /**
     * MÉTODO INICIAL
     * 
     * @tutorial Cada cromossomo tem que ser factível, ou seja, a soma dos genes multiplicados
     * pelo peso do tipo do gene não pode passar a capacidade máxima da mochila
     */
    public function processaDados() {
        $this->criaPopulacaoInicial();
        $this->criaGeracao();
    }
    
    /**
     * Cria a população inicial (geração dos cromossomos iniciais)
     */
    private function criaPopulacaoInicial() {
        $aListaCromossomos = [];
        for ($i = 0; $i < self::TAMANHO_POPULACAO;) {
            $aCromossomo = $this->getCromossomo();
            if ($this->isCromossomoFactivel($aCromossomo)) {
                $this->trataBestOf($aCromossomo);
                $aListaCromossomos[] = $aCromossomo;
                $i++;
            }
        }
        $this->geracao['populacao'] = $aListaCromossomos;
    }
    
    /**
     * @tutorial $aCromossomo[$iKey] = quantidade de itens do gene 1 * peso de cada item
     */
    private function getCromossomo() {
        $aCromossomo = [];
        foreach ($this->tiposGene as $iKey => $oTipoGene) {
            $iQuantidadeGene = $this->getQuantidadeInformacaoGene($oTipoGene);
            $aCromossomo[$iKey]['tipoGene'] = $oTipoGene;
            $aCromossomo[$iKey]['peso']     = $iQuantidadeGene * $oTipoGene->getPeso();
            $aCromossomo[$iKey]['valor']    = $iQuantidadeGene * $oTipoGene->getValor();
        }
        return $aCromossomo;
    }
    
    /**
     * Cria de forma aleatória a quantidade de informação do gene
     * 
     * @tutorial Utiliza um número aleatório para fazer o sorteio através do método da roleta viciada
     * @param ModelObjetoMochilaBase $oGene
     * @return int
     */
    private function getQuantidadeInformacaoGene(ModelObjetoMochilaBase $oGene) {
        $iPossibilidadeEscolha = $this->getPossibilidadeEscolhaGene($oGene);
        $iProbabilidadeEscolha = (self::VALOR_CEM_PORCENTAGEM / $iPossibilidadeEscolha);
        $iPorcentagemAleatoria = $this->getPorcentagemAleatoria();
        
        $iQuantidadeGene                = 0;
        $iSomatorioProbabilidadeEscolha = $iProbabilidadeEscolha;
        for ($i = 0; $i < $iPossibilidadeEscolha; $i++) {
            if ($iPorcentagemAleatoria <= $iSomatorioProbabilidadeEscolha) {
                break;
            }
            $iSomatorioProbabilidadeEscolha += $iProbabilidadeEscolha;
            $iQuantidadeGene++;
        }
        return $iQuantidadeGene;
    }
    
    /**
     * Retorna a possibilidade de escolha do gene (quantidade total somada a possibilidade de não escolher nenhum)
     * 
     * @param ModelObjetoMochilaBase $oGene
     * @return int
     */
    private function getPossibilidadeEscolhaGene(ModelObjetoMochilaBase $oGene) {
        return $oGene->getQuantidadeDisponivel() + 1;
    }
    
    /**
     * Cria uma nova geração apartir da população definida inicialmente (ou através da última iteração
     * realizada)
     */
    private function criaGeracao() {
        $this->processaSelecaoPais();
        $this->processaCrossOver();
        $this->processaMutacao();
        $this->processaConstrucaoNovaPopulacao();
        $this->processaFinalGeracao();
    }
    
    /**
     * Processa as informações geradas pela geração
     */
    private function processaFinalGeracao() {
        $this->processaCriacaoInformacaoGraficaGeracao();
        $this->processaVerificacaoCriterioParada();
    }
    
    /**
     * Cria a informação gráfica das informações de todos os cromossomos da geração
     */
    private function processaCriacaoInformacaoGraficaGeracao() {
        $sMensagem = '---------------------------</br>Geração: '. $this->sequencialGeracao . '</br></br>';
        foreach ($this->geracao['populacao'] as $iKey => $aCromossomo) {
            $iIndiceCromossomo = $iKey + 1;
            $sMensagem .= '- Cromossomo: ' . $iIndiceCromossomo . '</br>'; 
            foreach ($aCromossomo as $aGene) {
                if ($aGene['peso']) {
                    $sMensagem .= '  * ' . ($aGene['peso'] / $aGene['tipoGene']->getPeso()) . ' do tipo ' . $aGene['tipoGene']->getNome() . '</br>';
                }
            }
            $sMensagem .= '  * Peso Total na Mochila: ' . $this->getPesoCromossomo($aCromossomo) . '</br>  * Valor Total na Mochila: ' . $this->getValorCromossomo($aCromossomo) . '</br></br>';
        }
        echo $sMensagem;
    }
    
    /**
     * Processa a verificação do critério de parada
     * 
     * @tutorial Caso o critério de parada seja atingido, o algorítmo interrompe o procedimento,
     * caso não, deverá ser realizado o processamento de uma nova geração 
     */
    private function processaVerificacaoCriterioParada() {
        if (!($this->sequencialGeracao == $this->sequencialGeracaoCriterioParada)) {
            $this->sequencialGeracao++;
            $this->criaGeracao();
        } else {
            $this->processaCriacaoInformacaoGraficaFinal();
        }
    }
    
    /**
     * Cria a informação gráfica do melhor cromossomo até então (o melhor cromossomo diz respeito à maximização de valor na mochila sem que a capacidade máxima da mesma seja atingida)
     */
    private function processaCriacaoInformacaoGraficaFinal() {
        $sMensagem = 'O maior valor: R$' . $this->getValorCromossomo($this->bestOf['cromossomo']) . '. Gerado pela primeira vez na geração ' . $this->bestOf['sequencialGeracao'] . '. Sendo estes, valores dos seguintes objetos: </br>';
        foreach ($this->bestOf['cromossomo'] as $aGene) {
            if ($aGene['peso']) {
                $sMensagem .= ($aGene['peso'] / $aGene['tipoGene']->getPeso()) . ' do tipo ' . $aGene['tipoGene']->getNome() . '</br>';
            }
        }
        echo $sMensagem;
    }
    
    /**
     * Cria a nova população de acordo com os filhos gerados na geração atual
     * 
     * @tutorial 1. Conforme o algorítmo propõe:
     *           1.1 Remove os dois primeiros cromossomos da população, adiciona os dois novos filhos (criando uma nova população)
     * Observação: Reseta também as informações sobre pais e filhos já existentes para que a nova geração crie de forma única as novas informações
     */
    private function processaConstrucaoNovaPopulacao() {
        array_splice($this->geracao['populacao'], 0, 2);
        $this->geracao['populacao'] = array_merge($this->geracao['populacao'], $this->geracao['filhosPopulacao']);
        unset($this->geracao['paisPopulacao']);
        unset($this->geracao['filhosPopulacao']);
    }
    
    /**
     * Seleciona os cromossomos pai da população
     */
    private function processaSelecaoPais() {
        list($aListaCromossomosMelhores, $aListaCromossomosPiores) = $this->getListasCromossomosMelhoresPiores();
        
        $aCromossomoPaiMelhor           = $this->getCromossomoPai($aListaCromossomosMelhores);
        $aCromossomoPaiPior             = $this->getCromossomoPai($aListaCromossomosPiores);
        $this->geracao['paisPopulacao'] = [$aCromossomoPaiMelhor, $aCromossomoPaiPior];
    }
    
    /**
     * Cria as listas dos cromossomos melhores e piores
     * 
     * @return Array
     */
    private function getListasCromossomosMelhoresPiores() {
        $aPopulacaoOrdenada         = $this->ordenaPopulacaoValorDecrescente($this->geracao['populacao']);
        $iChaveInicioListaPiores    = self::TAMANHO_POPULACAO / 2;
        $aListaCromossomosMelhores  = [];
        $aListaCromossomosPiores    = [];
        
        for ($i = 0; $i < self::TAMANHO_POPULACAO; $i++) {
            if ($i < $iChaveInicioListaPiores) {
                $aListaCromossomosMelhores[] = $aPopulacaoOrdenada[$i];
            } else {
                $aListaCromossomosPiores[] = $aPopulacaoOrdenada[$i];
            }
        }
        return [$aListaCromossomosMelhores, $aListaCromossomosPiores];
    }
    
    /**
     * Ordena a população pelo valor dos cromossomos de forma decrescente
     * 
     * @tutorial A ordenação não pode interferir na ordenação da geração global (pois a ordenação original é necessária para
     * a definição dos cromossomos que deverão ser substituídos na geração)
     * @param Array $aPopulacao
     * @return Array
     */
    private function ordenaPopulacaoValorDecrescente($aPopulacao) {
        usort($aPopulacao, function($aCromossomoA, $aCromossomoB) {
                                return $this->getValorCromossomo($aCromossomoB) - $this->getValorCromossomo($aCromossomoA);
                           });
        return $aPopulacao;
    }
    
    /**
     * Cria o cromossomo pai de acordo com a lista de cromossomos informadas
     * 
     * @param Array $aListaCromossomos
     * @return Array
     */
    private function getCromossomoPai($aListaCromossomos) {
        $aListaCromossomosAtualizados = $this->getListaCromossomoComPossibilidadePai($aListaCromossomos);
        return $this->defineCromossomoPai($aListaCromossomosAtualizados);
    }
    
    /**
     * Retorna a lista de cromossomo com as suas possibilidades de serem definidos como o pai
     * 
     * @param Array $aListaCromossomos
     * @return Array
     */
    private function getListaCromossomoComPossibilidadePai($aListaCromossomos) {
        $iSomaValoresCromossomos = $this->getSomaValoresCromossomos($aListaCromossomos);
        foreach ($aListaCromossomos as $iKey => $aCromossomo) {
            $aListaCromossomos[$iKey]['probabilidadeEscolhaPai'] = $this->getPorcentagemCromossomoPai($aCromossomo, $iSomaValoresCromossomos);
        }
        return $aListaCromossomos;
    }
    
    /**
     * Cria a soma dos valores dos cromossomos
     * 
     * @param Array $aListaCromossomos
     * @return int
     */
    private function getSomaValoresCromossomos($aListaCromossomos) {
        $iSomaValoresCromossomos = 0;
        foreach ($aListaCromossomos as $aCromossomo) {
            $iSomaValoresCromossomos += $this->getValorCromossomo($aCromossomo);
        }
        return $iSomaValoresCromossomos;
    }
    
    /**
     * Cria através da regra de 3 a porcentagem do cromossomo ser definido como pai
     * 
     * @param Array $aCromossomo
     * @param int $iSomaValoresCromossomos
     * @return int
     */
    private function getPorcentagemCromossomoPai($aCromossomo, $iSomaValoresCromossomos) {
        return (($this->getValorCromossomo($aCromossomo) * self::VALOR_CEM_PORCENTAGEM) / $iSomaValoresCromossomos);
    }
    
    /**
     * Define o cromossomo pai através da possibilidade do mesmo ser pai
     * 
     * @tutorial Utiliza um número aleatório para fazer o sorteio através do método da roleta viciada
     * Observação: A lógica do for é diferente pois as probabilidades de cada registro da iteração
     * são diferentes
     * @param Array $aListaCromossomos
     * @return Array
     */
    private function defineCromossomoPai($aListaCromossomos) {
        $iPorcentagemAleatoria = $this->getPorcentagemAleatoria();
        $iProbabilidadeEscolha = $aListaCromossomos[0]['probabilidadeEscolhaPai'];
        
        for ($i = 0; $i < count($aListaCromossomos); $i++) {
            if ($iPorcentagemAleatoria <= $iProbabilidadeEscolha) {
                unset($aListaCromossomos[$i]['probabilidadeEscolhaPai']);
                $aCromossomoPai = $aListaCromossomos[$i];
                break;
            }
            $iProbabilidadeEscolha = ($aListaCromossomos[$i]['probabilidadeEscolhaPai'] + $iProbabilidadeEscolha);
        }
        return $aCromossomoPai;
    }
    
    /**
     * Faz o cross over dos cromossomos pai da população
     * 
     * @tutorial 1. No cross over serão definidos os 50% melhores e os 50% piores da população
     *           2. O cross over deverá acontecer em no mínimo um gene, e poderá acontecer em até 2 genes
     */
    private function processaCrossOver() {
        $aPaisPopulacao                  = $this->geracao['paisPopulacao'];
        $aListaTipoGeneCrossOver         = $this->getListaTipoGeneCrossOver();
        $aListaInformacoesGenesCrossOver = $this->getListaInformacoesGenesCrossOver($aPaisPopulacao, $aListaTipoGeneCrossOver);
        $aListaCromossomoFilho           = $this->getListaCromossomoFilho($aPaisPopulacao, $aListaTipoGeneCrossOver, $aListaInformacoesGenesCrossOver);
        
        $this->finalizaCrossOver($aListaCromossomoFilho);
    }
    
    /**
     * Retorna uma lista de tipos de gene utilizados no cross over
     * 
     * @return array
     */
    private function getListaTipoGeneCrossOver() {
        $oTipoGeneCrossOverA  = $this->getTipoGeneCrossOver();
        $oTipoGeneCrossOverB  = $this->getTipoGeneCrossOver();
        
        $aListaTipoGeneCrossOver[] = $oTipoGeneCrossOverA;
        if (!($oTipoGeneCrossOverA == $oTipoGeneCrossOverB)) {
            $aListaTipoGeneCrossOver[] = $oTipoGeneCrossOverB;
        }
        return $aListaTipoGeneCrossOver;
    }
    
    /**
     * Retorna o tipo do gene que será utilizado no cross over
     * 
     * @return ModelObjetoMochilaBase
     */
    private function getTipoGeneCrossOver() {
        $iQuantidadeTipoGene   = count($this->tiposGene);
        $iProbabilidadeEscolha = (self::VALOR_CEM_PORCENTAGEM / $iQuantidadeTipoGene);
        $iPorcentagemAleatoria = $this->getPorcentagemAleatoria(); 
        
        $iSomatorioProbabilidadeEscolha = $iProbabilidadeEscolha;
        for ($i = 0; $i < $iQuantidadeTipoGene; $i++) {
            if ($iPorcentagemAleatoria <= $iSomatorioProbabilidadeEscolha) {
                $oTipoGeneCrossOver = $this->tiposGene[$i];
                break;
            }
            $iSomatorioProbabilidadeEscolha += $iProbabilidadeEscolha;
        }
        return $oTipoGeneCrossOver;
    }
    
    /**
     * Retorna uma lista de informações dos genes para o cross over
     * 
     * @param Array $aPaisPopulacao
     * @param Array $aListaTipoGeneCrossOver
     * @return Array
     */
    private function getListaInformacoesGenesCrossOver($aPaisPopulacao, $aListaTipoGeneCrossOver) {
        $aListaInformacoesGenesCrossOver = [];
        foreach ($aPaisPopulacao as $iKeyCromossomo => $aCromossomo) {
            foreach ($aCromossomo as $iKeyGene => $aGene) {
                if (in_array($aGene['tipoGene'], $aListaTipoGeneCrossOver)) {
                    $aListaInformacoesGenesCrossOver[$iKeyCromossomo][$iKeyGene] = $aGene;
                }
            }
        }
        return $aListaInformacoesGenesCrossOver;
    }
    
    /**
     * Retorna a lista de cromossos filhos
     * 
     * @tutorial Crusa os pais gerando os filhos A e B. O filho A herdará os genes do pai A sendo que os genes do filho contidos na lista de tipos de gene para o cross over
     * serão herdados do pai B. Já o filho B herda do pai B e os genes da lista de tipos de genes serão herdados do pai A 
     * @param Array $aPaisPopulacao
     * @param Array $aListaTipoGeneCrossOver
     * @param Array $aListaInformacoesGenesCrossOver
     * @return Array
     */
    private function getListaCromossomoFilho($aPaisPopulacao, $aListaTipoGeneCrossOver, $aListaInformacoesGenesCrossOver) {
        $aListaCromossomoFilho = [];
        foreach ($aPaisPopulacao as $iKeyCromossomo => $aCromossomoPai) {
            foreach ($aCromossomoPai as $iKeyGene => $aGene) {
                if (in_array($aGene['tipoGene'], $aListaTipoGeneCrossOver)) {
                    $aListaCromossomoFilho[$iKeyCromossomo] = $aCromossomoPai;

                    $iKeyCromossomoOutroPai = ($iKeyCromossomo == 0) ? 1 : 0;
                    $aGeneOutroPai          = $aListaInformacoesGenesCrossOver[$iKeyCromossomoOutroPai][$iKeyGene];
                    $aListaCromossomoFilho[$iKeyCromossomo][$iKeyGene] = $aGeneOutroPai;
                }
            }
        }
        return $aListaCromossomoFilho;
    }
    
    /**
     * Finaliza o cross over
     * 
     * @param Array $aListaCromossomoFilho
     * @throws Exception
     */
    private function finalizaCrossOver($aListaCromossomoFilho) {
        if ($this->isAllFilhosFactivel($aListaCromossomoFilho)) {
            $this->geracao['filhosPopulacao'] = $aListaCromossomoFilho;
        } else {
            $this->processaCrossOver();
        }
    }
    
    /**
     * Verifica se todos os filhos são factíveis
     * 
     * @param Array $aListaCromossomoFilho
     * @return boolean
     */
    private function isAllFilhosFactivel($aListaCromossomoFilho) {
        $bIsFactivel = true;
        foreach ($aListaCromossomoFilho as $aCromossomoFilho) {
            if (!$this->isCromossomoFactivel($aCromossomoFilho)) {
                $bIsFactivel = false;
                break;
            }
        }
        return $bIsFactivel;
    }
    
    /**
     * Processa a mutação dos filhos da geração
     * 
     * @tutorial A mutação somente poderá acontecer em um gene
     */
    private function processaMutacao() {
        if ($this->ocorreMutacao()) {
            list($aCromossomoFilhoMutacao, $iIndiceCromossomoMutacao) = $this->getCromossomoFilhoMutacao();
            $this->processaMutacaoGeneCromossomo($aCromossomoFilhoMutacao, $iIndiceCromossomoMutacao);
        }
        $this->trataBestOfGeracoes($this->geracao['filhosPopulacao']);
    }

    /**
     * Trata o melhor valor das gerações com base nos filhos gerados no cross over
     * 
     * @param Array $aListaCromossomoFilho
     */
    private function trataBestOfGeracoes($aListaCromossomoFilho) {
        foreach ($aListaCromossomoFilho as $aCromossomoFilho) {
            $this->trataBestOf($aCromossomoFilho);
        }
    }
    
    /**
     * Verifica se ocorre mutação
     * 
     * @tutorial Probabilidade aleatória dividida pela probabilidade de mutação
     * @return boolean
     */
    private function ocorreMutacao() {
        return ($this->getPorcentagemAleatoria() <= self::PROBABILIDADE_MUTACAO);
    }
    
    /**
     * Retorna o cromossomo filho que será mutado
     * 
     * @return Array
     */
    private function getCromossomoFilhoMutacao() {
        $iPossibilidadeEscolha          = count($this->geracao['filhosPopulacao']);
        $iProbabilidadeEscolha          = (self::VALOR_CEM_PORCENTAGEM / $iPossibilidadeEscolha);
        $iPorcentagemAleatoria          = $this->getPorcentagemAleatoria();
        $iSomatorioProbabilidadeEscolha = $iProbabilidadeEscolha;
        for ($i = 0; $i < $iPossibilidadeEscolha; $i++) {
            if ($iPorcentagemAleatoria <= $iSomatorioProbabilidadeEscolha) {
                $aCromossomoFilhoMutacao  = $this->geracao['filhosPopulacao'][$i];
                $iIndiceCromossomoMutacao = $i;
                break;
            }
            $iSomatorioProbabilidadeEscolha += $iProbabilidadeEscolha;
        }
        return [$aCromossomoFilhoMutacao, $iIndiceCromossomoMutacao];
    }
    
    /**
     * Processa a etapa de mutação do cromossomo
     * 
     * @param Array $aCromossomoFilhoMutacao
     * @param int $iIndiceCromossomoMutacao
     */
    private function processaMutacaoGeneCromossomo($aCromossomoFilhoMutacao, $iIndiceCromossomoMutacao) {
        $oTipoGeneAlterado = $this->defineTipoGeneAlterado();
        foreach ($aCromossomoFilhoMutacao as $iIndiceGeneMutacao => $aGene) {
            if ($aGene['tipoGene'] == $oTipoGeneAlterado) {
                $this->finalizaMutacaoGeneCromossomo($aCromossomoFilhoMutacao, $iIndiceCromossomoMutacao, $aGene, $iIndiceGeneMutacao);
                break;
            }
        }
    }
    
    /**
     * Processa a mutação do cromossomo específico
     * 
     * @param Array $aCromossomoFilhoMutacao
     * @param integer $iIndiceCromossomoMutacao
     */
    private function defineTipoGeneAlterado() {
        $iQuantidadeTipoGene   = count($this->tiposGene);
        $iProbabilidadeEscolha = (self::VALOR_CEM_PORCENTAGEM / $iQuantidadeTipoGene);
        $iPorcentagemAleatoria = $this->getPorcentagemAleatoria(); 

        $iSomatorioProbabilidadeEscolha = $iProbabilidadeEscolha;
        for ($i = 0; $i < $iQuantidadeTipoGene; $i++) {
            if ($iPorcentagemAleatoria <= $iSomatorioProbabilidadeEscolha) {
                $oTipoGeneCrossOver = $this->tiposGene[$i];
                break;
            }
            $iSomatorioProbabilidadeEscolha += $iProbabilidadeEscolha;
        }
        return $oTipoGeneCrossOver;
    }
    
    /**
     * Finaliza a mutação do gene no cromossomo
     * 
     * @tutorial Caso o peso gerado na mutação do cromossomo não seja factível, o procedimento da mutação desse cromossomo deve ser realizado novamente 
     * @param Array $aCromossomoFilhoMutacao
     * @param int $iIndiceCromossomoMutacao
     * @param Array $aGene
     * @param int $iIndiceGeneMutacao
     */
    private function finalizaMutacaoGeneCromossomo($aCromossomoFilhoMutacao, $iIndiceCromossomoMutacao, $aGene, $iIndiceGeneMutacao) {
        $aDadosMutacaoGeneMutacao['quantidadeGene'] = $this->getQuantidadeInformacaoGene($aGene['tipoGene']);
        $aDadosMutacaoGeneMutacao['peso']           = $aDadosMutacaoGeneMutacao['quantidadeGene'] * $aGene['tipoGene']->getPeso();
        $aDadosMutacaoGeneMutacao['valor']          = $aDadosMutacaoGeneMutacao['quantidadeGene'] * $aGene['tipoGene']->getValor();
        
        if ($this->isPesoFactivel($aDadosMutacaoGeneMutacao['peso'])) {
            $this->geracao['filhosPopulacao'][$iIndiceCromossomoMutacao][$iIndiceGeneMutacao]['peso']  = $aDadosMutacaoGeneMutacao['peso'];
            $this->geracao['filhosPopulacao'][$iIndiceCromossomoMutacao][$iIndiceGeneMutacao]['valor'] = $aDadosMutacaoGeneMutacao['valor'];
        } else {
            $this->processaMutacaoGeneCromossomo($aCromossomoFilhoMutacao, $iIndiceCromossomoMutacao);
        }
    }
    
    /**
     * retorna uma porcentagem aleatória (utilizado para as operações de roleta viciada)
     * 
     * @return int
     */
    private function getPorcentagemAleatoria() {
        return random_int(0, 100);
    }
    
    /**
     * Verifica se o cromossomo é factível, ou seja, a soma dos genes multiplicados
     * pelo peso do tipo do gene não ultrapassa a capacidade máxima da mochila
     * 
     * @param Array $aCromossomoAtual
     * @return boolean
     */
    private function isCromossomoFactivel($aCromossomoAtual) {
        $iPesoCromossomo = $this->getPesoCromossomo($aCromossomoAtual);
        return $this->isPesoFactivel($iPesoCromossomo);
    }
    
    /**
     * Verifica se o peso informado é factível
     * 
     * @param int $iPesoCromossomo
     * @return boolean
     */
    private function isPesoFactivel($iPesoCromossomo) {
        return ($iPesoCromossomo <= $this->capacidadeMaximaMochila);
    }
    
    /**
     * Retorna o peso do cromossomo
     * 
     * @param Array $aCromossomo
     * @return integer
     */
    private function getPesoCromossomo($aCromossomo) {
        $iPesoCromossomo = 0;
        foreach ($aCromossomo as $aGene) {
            $iPesoCromossomo += $aGene['peso'];
        }
        return $iPesoCromossomo;
    }
    
    /**
     * Trata o melhor cromossomo
     * 
     * @param Array $aCromossomo
     */
    private function trataBestOf($aCromossomo) {
        $iValorCromossomo = $this->getValorCromossomo($aCromossomo);
        if ($this->isBestOfGeracoes($iValorCromossomo)) {
            $this->bestOf['cromossomo']        = $aCromossomo;
            $this->bestOf['sequencialGeracao'] = $this->sequencialGeracao;
        }
    }
    
    /**
     * Verifica se o cromossomo atual é o melhor da geração
     * 
     * @param int$iValorCromossomo
     * @return boolean
     */
    private function isBestOfGeracoes($iValorCromossomo) {
        return (!isset($this->bestOf) || $iValorCromossomo > $this->getValorCromossomo($this->bestOf['cromossomo']));
    }
    
    /**
     * Retorna o valor do cromossomo
     * 
     * @param Array $aCromossomo
     * @return int
     */
    private function getValorCromossomo($aCromossomo) {
        $iValorCromossomo = 0;
        foreach ($aCromossomo as $aGene) {
            $iValorCromossomo += $aGene['valor'];
        }
        return $iValorCromossomo;
    }
    
}