<?php
require_once 'db_config.php';
require_once 'process_return.php';

function getTieredRepairFee($bookPrice) {
    if ($bookPrice < 300) return 50.00;
    if ($bookPrice < 800) return 150.00;
    if ($bookPrice < 2500) return 400.00;
    if ($bookPrice < 3000) return 550.00;
    if ($bookPrice < 10000) return 750.00;
    return $bookPrice * 0.10; // Fallback 10%
}

/**
 * Automated Daily Fine Rate 
 */
function getTieredFineRate($bookPrice) {
    if ($bookPrice < 500) return 5.00;
    if ($bookPrice < 1500) return 15.00;
    if ($bookPrice < 5000) return 30.00;
    if ($bookPrice < 10000) return 50.00;
    return round(($bookPrice * 0.01) / 7, 2); // ~1% per week
}

function getCalibratedFine($bookPrice, $daysLate, $condition, $severity = 1.0) {
    // Determine the daily rate automatically
    $finePerDay = getTieredFineRate($bookPrice);

    // Calculate Overdue Component with 25% cap 🧢
    $overduePart = min($daysLate * $finePerDay, $bookPrice * 0.25);
    $baseFine = 0;

    // 3. APPLY DAMAGE/LOSS LOGIC
    switch ($condition) {
        case 'OVERDUE':
            $baseFine = $overduePart;
            break;
            
        case 'DAMAGED':
            $repairFee = getTieredRepairFee($bookPrice);
            $baseFine = $repairFee + $overduePart;
            break;
            
        case 'LOST':
            // 100% Price + 10% Surcharge + 200 Processing + Overdue
            $surcharge = $bookPrice * 0.10;
            $processing = 200.00;
            $baseFine = $bookPrice + $surcharge + $processing + $overduePart;
            break;
    }

    return [
        'success' => true,
        'suggested_amount' => round($baseFine, 2),
        'overdue_component' => round($overduePart, 2),
        'is_strict' => true
    ];
}
?>