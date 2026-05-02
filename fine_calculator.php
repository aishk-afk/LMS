<?php
require_once 'db_config.php';

function getTieredRepairFee($bookPrice) {
    if ($bookPrice < 300) return 50.00;
    if ($bookPrice < 800) return 150.00;
    if ($bookPrice < 2500) return 400.00;
    if ($bookPrice < 3000) return 550.00;
    if ($bookPrice < 10000) return 750.00;
    return $bookPrice * 0.10;
}

function getTieredFineRate($bookPrice) {
    if ($bookPrice < 500) return 5.00;
    if ($bookPrice < 1500) return 15.00;
    if ($bookPrice < 5000) return 30.00;
    if ($bookPrice < 10000) return 50.00;
    return round(($bookPrice * 0.01) / 7, 2);
}

// Fixed: Now accepts all 6 arguments in the correct order 🤝
function getCalibratedFine($bookPrice, $dbFineRate, $daysLate, $condition, $isGraduating, $userType) {
    
    // We use the automated rate for our "Strict" logic 🤖
    $finePerDay = getTieredFineRate($bookPrice);

    // Overdue Component (Capped at 25%)
    $overduePart = min($daysLate * $finePerDay, $bookPrice * 0.25);
    
    // Graduating students get a 1.5x penalty to ensure they clear records! 🎓
    if ($isGraduating) {
        $overduePart *= 1.5;
    }

    $totalAmount = 0;

    switch ($condition) {
        case 'OVERDUE':
            $totalAmount = $overduePart;
            break;
            
        case 'DAMAGED':
            $repairFee = getTieredRepairFee($bookPrice);
            $totalAmount = $repairFee + $overduePart;
            break;
            
        case 'LOST':
            $surcharge = $bookPrice * 0.10;
            $processing = 200.00;
            $totalAmount = $bookPrice + $surcharge + $processing + $overduePart;
            break;
    }

    return [
        'success' => true,
        'suggested_amount' => round($totalAmount, 2),
        'overdue_component' => round($overduePart, 2)
    ];
}
