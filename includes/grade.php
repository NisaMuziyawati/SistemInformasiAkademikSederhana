<?php
/**
 * Konversi nilai angka (0-100) menjadi nilai huruf & bobot mutu.
 * Skala umum yang dipakai banyak kampus di Indonesia.
 */
function convertNilai(float $angka): array
{
    if ($angka >= 80)      return ['A',  4.00];
    elseif ($angka >= 75)  return ['AB', 3.50];
    elseif ($angka >= 70)  return ['B',  3.00];
    elseif ($angka >= 65)  return ['BC', 2.50];
    elseif ($angka >= 60)  return ['C',  2.00];
    elseif ($angka >= 55)  return ['CD', 1.50];
    elseif ($angka >= 50)  return ['D',  1.00];
    else                   return ['E',  0.00];
}
