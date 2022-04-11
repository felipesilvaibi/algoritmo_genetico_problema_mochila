<?php
/* 
 * Modelo base para a criação dos objetos da mochila
 *
 * @package AlgoritmoGenetico
 * @subpackage Model
 * @user felipesilvaibi
 * @since 14/03/2021
 */
abstract class ModelObjetoMochilaBase {
    
    /**
     * Nome do objeto
     * 
     * @var String
     */
    protected $nome;
    
    /**
     * Peso do objeto (float pois o peso do objeto pode ser fracionado)
     * 
     * @var float
     */
    protected $peso;
    
    /**
     * Valor do Objeto
     * 
     * @var float
     */
    protected $valor;
    
    /**
     * Quantidade do objeto disponível para colocar na mochila (caso o algoritmo for utilizado para a resolução do problema da mochila binária
     * a quantidade deverá ser 1)
     * 
     * @var int
     */
    protected $quantidadeDisponivel;
    
    public function getNome() {
        return $this->nome;
    }
    
    public function getPeso() {
        return $this->peso;
    }

    public function getValor() {
        return $this->valor;
    }

    public function getQuantidadeDisponivel() {
        return $this->quantidadeDisponivel;
    }
    
}