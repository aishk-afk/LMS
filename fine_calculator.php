<?php
// fine_calculator.php
require_once 'db_config.php';

function getTieredRepairFee($bookPrice)
{
    if ($bookPrice < 150) return 25.00;
    if ($bookPrice < 300) return 50.00;
    if ($bookPrice < 600) return 125.00;
    if ($bookPrice < 800) return 250.00;
    if ($bookPrice < 2500) return 400.00;
    if ($bookPrice < 3000) return 550.00;
    if ($bookPrice < 5000) return 650.00;
    if ($bookPrice < 8000) return 725.00;
    if ($bookPrice < 10000) return 1000.00;
    return $bookPrice * 0.10;
}

function getTieredFineRate($bookPrice)
{
    if ($bookPrice < 150) return 5.00;
    if ($bookPrice < 500) return 10.00;
    if ($bookPrice < 1500) return 15.00;
    if ($bookPrice < 5000) return 30.00;
    if ($bookPrice < 10000) return 50.00;
    return round(($bookPrice * 0.01) / 7, 2);
}

function getCalibratedFine($bookPrice, $daysLate, $condition, $severityMultiplier = 1.0) {
    // 1. Get Base Rates
    $fineRate = getTieredFineRate($bookPrice);
    
    // 2. Apply 25% Overdue Cap
    $rawOverdue = $daysLate * $fineRate;
    $overdueCap = $bookPrice * 0.25;
    $overduePart = min($rawOverdue, $overdueCap);
    
    $totalAmount = 0;

    if ($condition === 'LOST') {
        $surcharge = $bookPrice * 0.10;
        $adminFee = 200.00; // You can also pull this from system_settings table
        $totalAmount = $bookPrice + $surcharge + $adminFee + $overduePart;
    } 
    elseif ($condition === 'DAMAGED') {
        $baseRepair = getTieredRepairFee($bookPrice);
        $totalAmount = ($baseRepair * $severityMultiplier) + $overduePart;
    } 
    else {
        $totalAmount = $overduePart;
    }

    return [
        'total' => round($totalAmount, 2),
        'fine_rate' => $fineRate,
        'overdue_part' => $overduePart
    ];
}

