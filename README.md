# Algorítmo genético aplicado ao problema da mochila binária
Exemplo que apresenta uma possível implementação de "Algoritmo Genético" para resolver o "Problema da Mochila"

## O que é o Problema da Mochila?
O problema da mochila (em inglês Knapsack problem) é um problema de otimização combinatória. Metaforicamente podemos entendê-lo como o desafio de encher uma mochila sem ultrapassar um determinado limite de peso, otimizando o somatório do valor dos produtos carregados

## O que é um Algoritmo Genético?
O algoritmo genético é uma das técnicas da computação evolucionária de busca e otimização inspirada na evolução natural das espécies de Darwin. A idéia é criar uma população de indivíduos que vão se reproduzir e competir pela sobrevivência, onde os melhores indivíduos sobrevivem e transferem suas características para novas gerações, até que se alcance uma solução próxima ao resultado ótimo (Pozo et al. 2003)

## Definições do algoritmo:

- Tamanho da População: 4 Indivíduos

- Método de Seleção: Roleta Viciada

- Regras do crossover:
   * 50% dos cromossos da população atual serão originados pelos 50% melhores da população anterior, assim como os outros 50% serão originados pelos 50% piores da população anterior
   * Ocorre em até 2 genes

- Regras da mutação:
   * 10% de chance do filho sofrer mutação
   * Ocorre somente em 1 gene

- Critério de parada:
   * 200 gerações
   * 1000 recursões aninhadas (configuração do PHP Xdebug para impossibilitar recursão infinita (alterar conforme necessário))

Observação: Algumas considerações foram feitas para deixar o problema mais didático. Por exemplo: População de apenas 4 indivíduos e 10% de chance de mutação do filho

## Referências
- https://vitorebatista.medium.com/algoritmo-gen%C3%A9tico-para-o-problema-da-mochila-5910f90f9488
- https://www.youtube.com/watch?v=sXzFIrSt11o
