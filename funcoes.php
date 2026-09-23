<?php
// FUNÇÕES DE UTILIDADE PEQUENAS PARA VÁRIAS TELAS
// ESCAPAMENTO DE HTML 
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// FORMATAÇÃO DE MOEDA PARA A BRASILEIRA (REAL)
function moeda($valor)
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

// aceita "12,50", "1.250,90", "1.250", "R$ 1 250,00" ou "12.50" e devolve float (ou null se for inválido)
function lerPreco($texto)
{
    $texto = str_replace(['R$', ' '], '', trim($texto));
    if ($texto === '') {
        return null;
    }

    if (strpos($texto, ',') !== false) {
        // formato brasileiro: 1.250,90
        $texto = str_replace('.', '', $texto);   // tira o ponto de milhar
        $texto = str_replace(',', '.', $texto);  // vírgula vira ponto decimal
    } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $texto)) {
        // 1.250 ou 1.250.000 -> aqui o ponto é milhar, não decimal
        $texto = str_replace('.', '', $texto);
    }

    return is_numeric($texto) && $texto >= 0 ? (float) $texto : null;
}